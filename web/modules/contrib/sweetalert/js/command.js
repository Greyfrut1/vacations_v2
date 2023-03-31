/**
 * @file
 * Extends the AjaxCommands and adds sweetalert as a method.
 */

(function (Drupal) {
  'use strict';

  if (typeof Drupal.AjaxCommands !== 'undefined' && typeof Swal !== 'undefined') {
    Drupal.AjaxCommands.prototype.sweetalert = function (ajax, response, status) {
      Swal.fire(response.settings.options);
    };
  }

})(Drupal);
