<?php

namespace Drupal\icbf_migrations\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a fallback action for Revisioning publish latest.
 *
 * @Action(
 *   id = "revisioning_publish_latest_revision_action",
 *   label = @Translation("Fix for missing Revisioning Publish Latest action"),
 *   type = "node"
 * )
 */
class RevisioningPublishLatestFix extends ActionBase {

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
