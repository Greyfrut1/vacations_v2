<?php

declare(strict_types=1);

namespace Drupal\sweetalert\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\sweetalert\Ajax\SweetAlertCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;

/**
 * Sandbox demo form for SweetAlert.
 */
class SandboxForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sweetalert_sandbox';
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['help'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('Use the below inputs to generate some example alerts. You can view more advanced examples on <a href=":link">the homepage</a>.', [':link' => 'https://sweetalert2.github.io/#examples']) . '</p>',
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('The alert title.'),
      '#default_value' => '',
    ];

    $form['message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Message'),
      '#description' => $this->t('The message for the alert.'),
      '#default_value' => '',
    ];

    $form['backdrop'] = [
      '#type' => 'select',
      '#title' => $this->t('Backdrop'),
      '#description' => $this->t('Whether to show a transparent backdrop behind the alert.'),
      '#default_value' => 0,
      '#options' => [
        0 => $this->t("No"),
        1 => $this->t("Yes"),
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate alert'),
      '#ajax' => [
        'callback' => '::spawn',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * AJAX callback for the form. Spawns a SweetAlert.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX event response.
   */
  public function spawn(array &$form, FormStateInterface $form_state) {
    $title = $form_state->getValue('title');
    $message = $form_state->getValue('message');
    $backdrop = (bool) $form_state->getValue('backdrop');

    $response = new AjaxResponse();
    $response->addCommand(new SweetAlertCommand(
        [
          'title' => $title,
          'text' => $message,
          'backdrop' => $backdrop,
        ]
      )
    );
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
