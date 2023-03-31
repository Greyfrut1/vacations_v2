<?php

namespace Drupal\Tests\sweetalert\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\sweetalert\Ajax\SweetAlertCommand;

/**
 * @coversDefaultClass \Drupal\sweetalert\Ajax\SweetAlertCommand
 * @group sweetalert
 */
class SweetAlertCommandTest extends UnitTestCase {

  /**
   * Test that the render method returns an array with our option set.
   */
  public function testSweetAlertCommandRenderArray() {
    $options = [];
    $options['title'] = 'Test Alert Title';
    $options['text'] = 'Test alert message.';
    $command = new SweetAlertCommand($options);

    $expected = [
      'command' => 'sweetalert',
      'settings' => ['options' => $options],
    ];

    $this->assertEquals($expected, $command->render());
  }

}
