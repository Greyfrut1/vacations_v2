<?php

namespace Drupal\twig_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the Twig field formatter.
 *
 * @FieldFormatter(
 *   id = "twig",
 *   label = @Translation("Rendered Twig template"),
 *   field_types = {
 *     "twig"
 *   }
 * )
 */
class TwigFormatter extends FormatterBase {

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ModuleHandlerInterface $moduleHandler) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('module_handler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $entity = $items->getEntity();

    // Global context.
    $context = twig_field_default_context();

    // Field context.
    if ($display_mode_id = $this->getFieldSetting('display_mode')) {
      $display_mode = EntityViewDisplay::load($display_mode_id);

      $twig_field_name = $this->fieldDefinition->getName();

      // Remove current component to avoid recursion.
      $display_mode->removeComponent($twig_field_name);

      $build = $display_mode->build($entity);
      $entity_cache = [
        'tags' => $entity->getCacheTags(),
        'contexts' => $entity->getCacheContexts(),
        'max-age' => $entity->getCacheMaxAge(),
      ];
      foreach ($build as $field_name => $field) {
        // Skip itself to avoid recursion.
        if ($twig_field_name != $field_name) {
          $context[$field_name] = $field;

          // As the fields are rendered individually the cache should be
          // configured for each one.
          if (isset($context[$field_name]['#cache'])) {
            $field_cache = $context[$field_name]['#cache'];
            $context[$field_name]['#cache'] = [
              'tags' => Cache::mergeTags($field_cache['tags'], $entity_cache['tags']),
              'contexts' => Cache::mergeContexts($field_cache['contexts'], $entity_cache['contexts']),
              'max-age' => Cache::mergeMaxAges($field_cache['max-age'], $entity_cache['max-age']),
            ];
          }
        }
      }
    }

    // Other context.
    $entity_type = $this->fieldDefinition->getTargetEntityTypeId();
    $context[$entity_type] = $entity;

    // Invoke hook_twig_field_formatter_variable_alter function.
    $alter_context = [
      'entity_type' => $entity_type,
      'field_definition' => clone $this->fieldDefinition,
    ];
    $this->moduleHandler->alter('twig_field_formatter_variable', $context, $alter_context);

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'inline_template',
        '#template' => $item->value,
        '#context' => $context,
      ];
    }

    return $elements;
  }

}
