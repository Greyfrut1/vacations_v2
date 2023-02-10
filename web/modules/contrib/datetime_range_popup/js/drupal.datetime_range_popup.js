/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.datetime_range_popup = {
    attach: function (context, settings) {

      // Setting the current language for the calendar.
      var language = drupalSettings.path.currentLanguage;
      var last_field;

      $(context).find('input[data-date-time-range-end]').once('datePicker').each(function () {
        var input = $(this);

        // Get widget Type.
        var widgetType = input.data('dateTimeRangeEnd');

        // Get hour format - 12 or 24.
        var hourFormat = input.data('hourFormat');
        var timeFormat = (hourFormat === '12h') ? 'YYYY-MM-DD hh:mm' : 'YYYY-MM-DD  HH:mm';

        // Get excluded dates.
        var excludeDates = '';
        if (typeof input.data('excludeDate') !== 'undefined') {
          excludeDates = input.data('excludeDate').split(',');
        }

        // Get disabled days.
        var disabledDays = input.data('disableDays');

        // Get minute granularity.
        var allowedTimes = input.data('allowTimes');

        // Get week start.
        var weekStart = input.data('weekStart');

        // Set last field id to use in start date.
        last_field = $('#' + input.attr('id'));

        // If field widget is Date Time.
        if (widgetType === 'datetime') {
          $('#' + input.attr('id')).bootstrapMaterialDatePicker({
            format: timeFormat,
            disabledDays: disabledDays,
            disabledDates: excludeDates,
            minuteStep: allowedTimes,
            lang: language,
            weekStart: weekStart,
            clearButton: true
          });
        }

        // If field widget is Date only.
        else {
          $('#' + input.attr('id')).bootstrapMaterialDatePicker({
            format: 'YYYY-MM-DD',
            disabledDays: disabledDays,
            disabledDates: excludeDates,
            minuteStep: allowedTimes,
            lang: language,
            time: false,
            weekStart: weekStart,
            clearButton: true
          });
        }
      });

      $(context).find('input[data-date-time-range-start]').once('datePicker').each(function () {
        var input = $(this);

        // Get widget Type.
        var widgetType = input.data('dateTimeRangeStart');

        // Get hour format - 12 or 24.
        var hourFormat = input.data('hourFormat');
        var timeFormat = (hourFormat === '12h') ? 'YYYY-MM-DD hh:mm:' : 'YYYY-MM-DD  HH:mm';

        // Get excluded dates.
        var excludeDates = '';
        if (typeof input.data('excludeDate') !== 'undefined') {
          excludeDates = input.data('excludeDate').split(',');
        }

        // Get disabled days.
        var disabledDays = input.data('disableDays');

        // Get minute granularity.
        var allowedTimes = input.data('allowTimes');

        // Get week start.
        var weekStart = input.data('weekStart');
        // If field widget is Date Time.
        if (widgetType === 'datetime') {
          $('#' + input.attr('id')).bootstrapMaterialDatePicker({
            format: timeFormat,
            disabledDays: disabledDays,
            disabledDates: excludeDates,
            minuteStep: allowedTimes,
            lang: language,
            weekStart: weekStart,
            clearButton: true
          }).on('change', function (e, date)
          {
            last_field.bootstrapMaterialDatePicker('setMinDate', date);
          });
        }

        // If field widget is Date only.
        else {
          $('#' + input.attr('id')).bootstrapMaterialDatePicker({
            format: 'YYYY-MM-DD',
            disabledDays: disabledDays,
            disabledDates: excludeDates,
            minuteStep: allowedTimes,
            lang: language,
            time: false,
            weekStart: weekStart,
            clearButton: true
          }).on('change', function (e, date)
          {
            last_field.bootstrapMaterialDatePicker('setMinDate', date);
          });
        }
      });


    }
  };

})(jQuery, Drupal, drupalSettings);
