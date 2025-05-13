<?php

namespace Drupal\icbf_migrations\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a fallback action for Pathauto node.
 *
 * @Action(
 *   id = "pathauto_node_update_action",
 *   label = @Translation("Fix for missing Pathauto Node action"),
 *   type = "node"
 * )
 */
class PathautoNodeFix extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($node = NULL) {
    // No operation, just a placeholder to avoid migration errors.
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return TRUE;
  }

}
