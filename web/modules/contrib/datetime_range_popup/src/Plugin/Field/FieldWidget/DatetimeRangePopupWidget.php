<?php

namespace Drupal\datetime_range_popup\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeWidgetBase;

/**
 * Plugin implementation of the MaterializeDateTimeWidget widget.
 *
 * @FieldWidget(
 *   id = "datetime_range_popup_widget",
 *   label = @Translation("DateTime Range Popup"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class DatetimeRangePopupWidget extends DateRangeWidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The date format storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dateStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityStorageInterface $date_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->dateStorage = $date_storage;
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
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')->getStorage('date_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Field type.
    $element['value'] = [
      '#title' => $this->t('Start date'),
      '#type' => 'date_time_range_start',
      '#date_timezone' => date_default_timezone_get(),
      '#default_value' => NULL,
      '#date_type' => NULL,
      '#required' => $element['#required'],
    ];
    // Field type.
    $element['end_value'] = [
      '#title' => $this->t('End date'),
      '#type' => 'date_time_range_end',
      '#date_timezone' => date_default_timezone_get(),
      '#default_value' => NULL,
      '#date_type' => NULL,
      '#required' => $element['#required'],
    ];
    $element['#element_validate'][] = [$this, 'validateStartEnd'];

    // Identify the type of date and time elements to use.
    switch ($this->getFieldSetting('datetime_type')) {
      case DateRangeItem::DATETIME_TYPE_DATE:
      case DateRangeItem::DATETIME_TYPE_ALLDAY:

        // A date-only field should have no timezone conversion performed, so
        // use the same timezone as for storage.
        $element['value']['#date_timezone'] = DateTimeItemInterface::STORAGE_TIMEZONE;

        // If field is date only, use default time format.
        $format = DateTimeItemInterface::DATE_STORAGE_FORMAT;

        // Type of the field.
        $element['value']['#date_type'] = $this->getFieldSetting('datetime_type');

        // A date-only field should have no timezone conversion performed, so
        // use the same timezone as for storage.
        $element['end_value']['#date_timezone'] = DateTimeItemInterface::STORAGE_TIMEZONE;

        // If field is date only, use default time format.
        $end_value_format = DateTimeItemInterface::DATE_STORAGE_FORMAT;

        // Type of the field.
        $element['end_value']['#date_type'] = $this->getFieldSetting('datetime_type');
        break;

      default:
        // Type of the field.
        $element['value']['#date_timezone'] = DateTimeItemInterface::STORAGE_TIMEZONE;

        // Assign the time format, because time will be saved in 24hrs format
        // in database.
        $format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;

        $element['value']['#date_type'] = $this->getFieldSetting('datetime_type');

        // Type of the field.
        $element['end_value']['#date_timezone'] = DateTimeItemInterface::STORAGE_TIMEZONE;

        // Assign the time format, because time will be saved in 24hrs format
        // in database.
        $end_value_format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;

        // Echo $end_value_format;exit;.
        $element['end_value']['#date_type'] = $this->getFieldSetting('datetime_type');
        break;
    }

    if ($items[$delta]->start_date) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $start_date */
      $start_date = $items[$delta]->start_date;
      // The date was created and verified during field_load(), so it is safe to
      // use without further inspection.
      if ($this->getFieldSetting('datetime_type') == DateRangeItem::DATETIME_TYPE_DATE) {
        // A date without time will pick up the current time, use the default
        // time.
        $start_date->setDefaultDateTime();
      }
      $start_date->setTimezone(new \DateTimeZone($element['value']['#date_timezone']));

      // Manual define form for input field.
      $element['value']['#default_value'] = $start_date->format($format);
    }
    if ($items[$delta]->end_date) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $end_date */
      $end_date = $items[$delta]->end_date;
      if ($this->getFieldSetting('datetime_type') == DateRangeItem::DATETIME_TYPE_DATE) {
        // A date without time will pick up the current time, use the default
        // time.
        $end_date->setDefaultDateTime();
      }
      $end_date->setTimezone(new \DateTimeZone($element['end_value']['#date_timezone']));

      // Manual define form for input field.
      $element['end_value']['#default_value'] = $end_date->format($end_value_format);
    }

    $element['value']['#hour_format'] = $this->getSetting('hour_format');
    $element['value']['#allow_times'] = $this->getSetting('allow_times');
    $element['value']['#disable_days'] = $this->getSetting('disable_days');
    $element['value']['#week_start'] = $this->getSetting('week_start');
    $element['value']['#exclude_date'] = $this->getSetting('exclude_date');

    $element['end_value']['#hour_format'] = $this->getSetting('hour_format');
    $element['end_value']['#allow_times'] = $this->getSetting('allow_times');
    $element['end_value']['#disable_days'] = $this->getSetting('disable_days');
    $element['end_value']['#week_start'] = $this->getSetting('week_start');
    $element['end_value']['#exclude_date'] = $this->getSetting('exclude_date');

    return $element;
  }

  /**
   * Element_validate callback to ensure that the start date <= the end date.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   */
  public function validateStartEnd(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $start_date = $element['value']['#value'];
    $end_date = $element['end_value']['#value'];
    $tz = date_default_timezone_get();
    $start = new DrupalDateTime($start_date, $tz);
    $end = new DrupalDateTime($end_date, $tz);
    if ($start > $end) {
      $form_state->setError($element, $this->t('The @title end date cannot be before the start date', ['@title' => $element['#title']]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'hour_format' => '24h',
      'allow_times' => '15',
      'disable_days' => [],
      'exclude_date' => '',
      'week_start' => '7',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $elements = [];
    $elements['hour_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Hours Format'),
      '#description' => $this->t('Select the hours format'),
      '#options' => [
        '12h' => $this->t('12 Hours'),
        '24h' => $this->t('24 Hours'),
      ],
      '#default_value' => $this->getSetting('hour_format'),
      '#required' => TRUE,
    ];
    $elements['allow_times'] = [
      '#type' => 'select',
      '#title' => $this->t('Minutes granularity'),
      '#description' => $this->t('Select granularity for minutes in calendar'),
      '#options' => [
        '5' => $this->t('5 minutes'),
        '10' => $this->t('10 minutes'),
        '15' => $this->t('15 minutes'),
        '30' => $this->t('30 minutes'),
        '60' => $this->t('60 minutes'),
      ],
      '#default_value' => $this->getSetting('allow_times'),
      '#required' => TRUE,
    ];
    $elements['disable_days'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Disable specific days in week'),
      '#description' => $this->t('Select days which are disabled in calendar, etc. weekends or just Friday'),
      '#options' => [
        '1' => $this->t('Monday'),
        '2' => $this->t('Tuesday'),
        '3' => $this->t('Wednesday'),
        '4' => $this->t('Thursday'),
        '5' => $this->t('Friday'),
        '6' => $this->t('Saturday'),
        '7' => $this->t('Sunday'),
      ],
      '#default_value' => $this->getSetting('disable_days'),
      '#required' => FALSE,
    ];
    $elements['week_start'] = [
      '#type' => 'radios',
      '#title' => $this->t('Select start week'),
      '#description' => $this->t('Select start week which you want to start in calendar'),
      '#options' => [
        '1' => $this->t('Monday'),
        '2' => $this->t('Tuesday'),
        '3' => $this->t('Wednesday'),
        '4' => $this->t('Thursday'),
        '5' => $this->t('Friday'),
        '6' => $this->t('Saturday'),
        '7' => $this->t('Sunday'),
      ],
      '#default_value' => $this->getSetting('week_start'),
      '#required' => FALSE,
      '#attributes' => ['class' => ['week_start_from']],
    ];
    $elements['exclude_date'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Disable specific dates from calendar'),
      '#description' => $this->t('Enter days in following format YYYY-MM-DD e.g. 2020-03-15. Separate multiple dates with comma. This is used for specific dates, if you want to disable all weekends use settings above, not this field.'),
      '#default_value' => $this->getSetting('exclude_date'),
      '#required' => FALSE,
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Hours Format: @hour_format', ['@hour_format' => $this->getSetting('hour_format')]);
    $summary[] = $this->t('Minutes Granularity: @allow_times', ['@allow_times' => $this->getSetting('allow_times')]);

    $options = [
      '1' => $this->t('Monday'),
      '2' => $this->t('Tuesday'),
      '3' => $this->t('Wednesday'),
      '4' => $this->t('Thursday'),
      '5' => $this->t('Friday'),
      '6' => $this->t('Saturday'),
      '7' => $this->t('Sunday'),
    ];

    $disabled_days = [];
    foreach ($this->getSetting('disable_days') as $value) {
      if (!empty($value)) {
        $disabled_days[] = $options[$value];
      }
    }

    $disabled_days = implode(',', $disabled_days);

    $summary[] = $this->t('Disabled days: @disabled_days', ['@disabled_days' => !empty($disabled_days) ? $disabled_days : $this->t('None')]);

    $summary[] = $this->t('Start Week: @week_start', ['@week_start' => $this->getSetting('week_start')]);

    $summary[] = $this->t('Disabled dates: @disabled_dates', ['@disabled_dates' => !empty($this->getSetting('exclude_date')) ? $this->getSetting('exclude_date') : $this->t('None')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    // The widget form element type has transformed the value to a
    // DrupalDateTime object at this point. We need to convert it back to the
    // storage timezone and format.
    foreach ($values as &$item) {
      if (!empty($item['value'])) {

        // Date value is now string not instance of DrupalDateTime (without T).
        $date = new DrupalDateTime($item['value']);
        switch ($this->getFieldSetting('datetime_type')) {
          case DateRangeItem::DATETIME_TYPE_DATE:
            // If this is a date-only field, set it to the default time so the
            // timezone conversion can be reversed.
            $date->setDefaultDateTime();
            $format = DateTimeItemInterface::DATE_STORAGE_FORMAT;
            break;

          default:
            $format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
            break;
        }

        // Adjust the date for storage.
        $date->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE));
        $item['value'] = $date->format($format);
      }
      if (!empty($item['end_value'])) {

        // Date value is now string not instance of DrupalDateTime (without T).
        $date = new DrupalDateTime($item['end_value']);
        switch ($this->getFieldSetting('datetime_type')) {
          case DateRangeItem::DATETIME_TYPE_DATE:
            // If this is a date-only field, set it to the default time so the
            // timezone conversion can be reversed.
            $date->setDefaultDateTime();
            $format = DateTimeItemInterface::DATE_STORAGE_FORMAT;
            break;

          default:
            $format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
            break;
        }

        // Adjust the date for storage.
        $date->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE));
        $item['end_value'] = $date->format($format);
      }

    }

    return $values;
  }

}
