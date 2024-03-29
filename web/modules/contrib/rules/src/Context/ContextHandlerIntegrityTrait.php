<?php

namespace Drupal\rules\Context;

use Drupal\Core\Plugin\Context\ContextDefinitionInterface as CoreContextDefinitionInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface as CoreContextAwarePluginInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\rules\Context\ContextDefinitionInterface as RulesContextDefinitionInterface;
//@codingStandardsIgnoreStart
use Drupal\rules\Context\ContextProviderInterface;
//@codingStandardsIgnoreEnd
use Drupal\rules\Exception\IntegrityException;
use Drupal\rules\Engine\IntegrityViolation;
use Drupal\rules\Engine\IntegrityViolationList;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Entity\Plugin\DataType\EntityReference;

/**
 * Extends the context handler trait with support for checking integrity.
 */
trait ContextHandlerIntegrityTrait {

  use ContextHandlerTrait;

  /**
   * Performs the integrity check.
   *
   * @param \Drupal\Core\Plugin\ContextAwarePluginInterface $plugin
   *   The plugin with its defined context.
   * @param \Drupal\rules\Context\ExecutionMetadataStateInterface $metadata_state
   *   The current configuration state with all defined variables that are
   *   available.
   *
   * @return \Drupal\rules\Engine\IntegrityViolationList
   *   The list of integrity violations.
   */
  protected function checkContextConfigIntegrity(CoreContextAwarePluginInterface $plugin, ExecutionMetadataStateInterface $metadata_state) {
    $violation_list = new IntegrityViolationList();
    $context_definitions = $plugin->getContextDefinitions();

    // Make sure that all provided variables by this plugin are added to the
    // execution metadata state.
    $this->addProvidedContextDefinitions($plugin, $metadata_state);

    foreach ($context_definitions as $name => $context_definition) {
      // Check if a data selector is configured that maps to the state.
      if (isset($this->configuration['context_mapping'][$name])) {
        try {
          $data_definition = $this->getMappedDefinition($name, $metadata_state);
          $this->checkDataTypeCompatible($context_definition, $data_definition, $name, $violation_list);
        }
        catch (IntegrityException $e) {
          $violation = new IntegrityViolation();
          $violation->setMessage($this->t('Data selector %selector for context %context_name is invalid. @message', [
            '%selector' => $this->configuration['context_mapping'][$name],
            '%context_name' => $context_definition->getLabel(),
            '@message' => $e->getMessage(),
          ]));
          $violation->setContextName($name);
          $violation->setUuid($this->getUuid());
          $violation_list->add($violation);
        }

        if ($context_definition instanceof RulesContextDefinitionInterface
          && $context_definition->getAssignmentRestriction() === RulesContextDefinitionInterface::ASSIGNMENT_RESTRICTION_INPUT
        ) {
          $violation = new IntegrityViolation();
          $violation->setMessage($this->t('The context %context_name may not be configured using a selector.', [
            '%context_name' => $context_definition->getLabel(),
          ]));
          $violation->setContextName($name);
          $violation->setUuid($this->getUuid());
          $violation_list->add($violation);
        }
      }
      elseif (isset($this->configuration['context_values'][$name])) {
        if ($context_definition instanceof RulesContextDefinitionInterface
          && $context_definition->getAssignmentRestriction() === RulesContextDefinitionInterface::ASSIGNMENT_RESTRICTION_SELECTOR
        ) {
          $violation = new IntegrityViolation();
          $violation->setMessage($this->t('The context %context_name may only be configured using a selector.', [
            '%context_name' => $context_definition->getLabel(),
          ]));
          $violation->setContextName($name);
          $violation->setUuid($this->getUuid());
          $violation_list->add($violation);
        }
      }
      elseif ($context_definition->isRequired() && $context_definition->getDefaultValue() === NULL) {
        $violation = new IntegrityViolation();
        $violation->setMessage($this->t('The required context %context_name is missing.', [
          '%context_name' => $context_definition->getLabel(),
        ]));
        $violation->setContextName($name);
        $violation->setUuid($this->getUuid());
        $violation_list->add($violation);
      }
    }

    if ($plugin instanceof ContextProviderInterface) {
      $provided_context_definitions = $plugin->getProvidedContextDefinitions();

      foreach ($provided_context_definitions as $name => $context_definition) {
        if (isset($this->configuration['provides_mapping'][$name])
          && !preg_match('/^[0-9a-zA-Z_]*$/', $this->configuration['provides_mapping'][$name])
        ) {
          $violation = new IntegrityViolation();
          $violation->setMessage($this->t('Provided variable name %name contains not allowed characters.', [
            '%name' => $this->configuration['provides_mapping'][$name],
          ]));
          $violation->setContextName($name);
          $violation->setUuid($this->getUuid());
          $violation_list->add($violation);
        }
      }
    }

    return $violation_list;
  }

  /**
   * Checks that the data type of a mapped variable matches the expectation.
   *
   * @param \Drupal\Core\Plugin\Context\ContextDefinitionInterface $context_definition
   *   The context definition of the context on the plugin.
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $provided
   *   The data definition of the mapped variable to the context.
   * @param string $context_name
   *   The name of the context on the plugin.
   * @param \Drupal\rules\Engine\IntegrityViolationList $violation_list
   *   The list of violations where new ones will be added.
   */
  protected function checkDataTypeCompatible(CoreContextDefinitionInterface $context_definition, DataDefinitionInterface $provided, $context_name, IntegrityViolationList $violation_list) {
    // Compare data types. For now, fail if they are not equal.
    // @todo Add support for matching based upon type-inheritance.
    $target_type = $context_definition->getDataDefinition()->getDataType();
    $provided_type = $provided->getDataType();

    // Always allow a target type of 'any'.
    if ($target_type == 'any') {
      return;
    }

    // Valid if the target type matches the provided type exactly.
    if ($target_type == $provided_type) {
      return;
    }

    // When context is EntityAdapter and the provided type is an EntityAdapter
    // or EntityReference then check the provided entity type constraint.
    $provided_type_text = $provided_type;
    if ($context_definition->getDataDefinition()->getClass() == EntityAdapter::class
        && ($provided->getClass() == EntityAdapter::class || $provided->getClass() == EntityReference::class)) {

      // If the target type is an unqualified entity then there is no need to do
      // a constraint check.
      if ($target_type == 'entity') {
        return;
      }

      // getConstraints()['EntityType'] will be 'node', 'user', 'user_role' etc.
      $provided_type_constraint = $provided->getConstraints()['EntityType'];
      // Explode $target_type to get the second part of the entity qualifier.
      $target_type_constraint = explode(':', $target_type)[1];
      // If the two constraint entity types match then this reference is valid.
      if ($provided_type_constraint == $target_type_constraint) {
        return;
      }
      $provided_type_text = "$provided_type_constraint $provided_type_text";
    }

    // None of the above cases pass, so fail the validation.
    $violation = new IntegrityViolation();
    $violation->setMessage($this->t('Expected a @target_type data type for context %context_name but got a @provided_type data type instead.', [
      '@target_type' => $target_type,
      '%context_name' => $context_definition->getLabel(),
      '@provided_type' => $provided_type_text,
    ]));
    $violation->setContextName($context_name);
    $violation->setUuid($this->getUuid());
    $violation_list->add($violation);
  }

}
