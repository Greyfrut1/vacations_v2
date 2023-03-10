<?php

namespace Drupal\scheduler_rules_integration\Plugin\RulesAction;

use Drupal\Core\Entity\EntityInterface;
use Drupal\rules\Core\RulesActionBase;

/**
 * Provides an 'Unpublish the node immediately' action.
 *
 * @RulesAction(
 *   id = "scheduler_unpublish_now_action",
 *   label = @Translation("Unpublish the content immediately"),
 *   category = @Translation("Scheduler"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node",
 *       label = @Translation("Node"),
 *       description = @Translation("The node to be unpublished now"),
 *     ),
 *   }
 * )
 */
class UnpublishNow extends RulesActionBase {

  /**
   * Set the node status to Unpublished.
   *
   * This action should really be provided by Rules or by Core, but it is not
   * yet done (as of Aug 2016). Scheduler users need this action so we provide
   * it here. It could be removed later when Rules or Core includes it.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The node to be unpublished.
   */
  public function doExecute(EntityInterface $node) {
    $node->setUnpublished();
  }

}
