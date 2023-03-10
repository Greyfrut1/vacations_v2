<?php

namespace Drupal\scheduler_rules_integration\Plugin\Condition;

use Drupal\Core\Entity\EntityInterface;
use Drupal\rules\Core\RulesConditionBase;

/**
 * Provides 'Node is scheduled for unpublishing' condition.
 *
 * @Condition(
 *   id = "scheduler_condition_node_scheduled_for_unpublishing",
 *   label = @Translation("Node is scheduled for unpublishing"),
 *   category = @Translation("Scheduler"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node",
 *       label = @Translation("Scheduled Node"),
 *       description = @Translation("The node to test for having a scheduled unpublishing date. Enter 'node' or use data selection.")
 *     )
 *   }
 * )
 */
class NodeIsScheduledForUnpublishing extends RulesConditionBase {

  /**
   * Determines whether a node is scheduled for unpublishing.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The node to be checked.
   *
   * @return bool
   *   TRUE if the node is scheduled for unpublishing, FALSE if not.
   */
  protected function doEvaluate(EntityInterface $node) {
    return !empty($node->unpublish_on->value);
  }

}
