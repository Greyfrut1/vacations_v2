<?php

namespace Drupal\sweetalert\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\Core\Ajax\CommandWithAttachedAssetsInterface;
use Drupal\Core\Asset\AttachedAssets;

/**
 * AJAX command for spawning a SweetAlert object.
 *
 * @ingroup ajax
 */
class SweetAlertCommand implements CommandInterface, CommandWithAttachedAssetsInterface {

  /**
   * Constructs a SweetAlertCommand object.
   *
   * @param array $options
   *   The options for the SweetAlert popup.
   */
  public function __construct(protected array $options) {}

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'sweetalert',
      'settings' => ['options' => $this->options],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getAttachedAssets() {
    $assets = new AttachedAssets();
    $assets->setLibraries(['sweetalert/command']);
    return $assets;
  }

}
