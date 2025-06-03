<?php

namespace Drupal\icbf_migrations\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a fallback action for Revisioning delete.
 *
 * @Action(
 *   id = "revisioning_delete_archived_action",
 *   label = @Translation("Fix for missing Revisioning Delete action"),
 *   type = "node"
 * )
 */
class RevisioningDeleteFix extends ActionBase {

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
