<?php

namespace Drupal\icbf_migrations\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a fallback action for Pathauto user.
 *
 * @Action(
 *   id = "pathauto_user_update_action",
 *   label = @Translation("Fix for missing Pathauto User action"),
 *   type = "user"
 * )
 */
class PathautoUserFix extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    // No operation, just a placeholder to avoid migration errors.
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return TRUE;
  }

}
