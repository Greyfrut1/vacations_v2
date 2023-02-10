<?php

namespace Drupal\field_validation\Plugin\FieldValidationRule;

use Drupal\Core\Form\FormStateInterface;
use Drupal\field_validation\ConfigurableFieldValidationRuleBase;
use Drupal\field_validation\FieldValidationRuleSetInterface;

/**
 * IntegerFieldValidationRule.
 *
 * @FieldValidationRule(
 *   id = "integer_field_validation_rule",
 *   label = @Translation("Integer"),
 *   description = @Translation("Integer values.")
 * )
 */
class IntegerFieldValidationRule extends ConfigurableFieldValidationRuleBase {

  /**
   * {@inheritdoc}
   */
   
  public function addFieldValidationRule(FieldValidationRuleSetInterface $field_validation_rule_set) {

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $summary = parent::getSummary();

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'min' => NULL,
	  'max' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['min'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Minimum value'),
      '#default_value' => $this->configuration['min'],
      '#required' => TRUE,
    ];
    $form['max'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum value'),
      '#default_value' => $this->configuration['max'],
      '#required' => TRUE,
    ];	
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['min'] = $form_state->getValue('min');
	$this->configuration['max'] = $form_state->getValue('max');
  }
  
  public function validate($params) {
    $value = isset($params['value']) ? $params['value'] : '';
	$rule = isset($params['rule']) ? $params['rule'] : null;
	$context = isset($params['context']) ? $params['context'] : null;
	$settings = array();
	
	if(!empty($rule) && !empty($rule->configuration)){
	  $settings = $rule->configuration;
	}
	//$settings = $this->rule->settings;
    if ($value !== '' && !is_null($value)) {
      $options = array();
      
        $value = (int)$value;
      
      $node = \Drupal::routeMatch()->getParameter('node');
      $token_service = \Drupal::token();
      
      if (!empty($settings['min'])){
      //$settings['min'] = $settings['min'];
      $settings['min'] = $token_service->replace($settings['min'], ['node' => $node]);
      $settings['min'] = (int)$settings['min'];
      }
      
      if (!empty($settings['max'])){
      //$settings['max'] = strtr($settings['max'], $tokens);
      $settings['max'] = $token_service->replace($settings['max'], ['node' => $node]);
      $settings['max'] = (int)$settings['max'];
      }
      
      if (isset($settings['min']) && $settings['min'] != '') {
	    $min = $settings['min'];
        $options['options']['min_range'] = $min;
      }
      if (isset($settings['max']) && $settings['max'] != '') {
	    $max = $settings['max'];
        $options['options']['max_range'] = $max;
      }  
  
      if (FALSE === filter_var($value, FILTER_VALIDATE_INT, $options)) {
        $context->addViolation($rule->getErrorMessage());
      }      

    }	
    //return true;
  }
}
