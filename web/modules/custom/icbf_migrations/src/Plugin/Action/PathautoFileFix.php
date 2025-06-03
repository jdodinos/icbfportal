<?php

namespace Drupal\icbf_migrations\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a fallback action for Pathauto file.
 *
 * @Action(
 *   id = "pathauto_file_update_action",
 *   label = @Translation("Fix for missing Pathauto File action"),
 *   type = "file"
 * )
 */
class PathautoFileFix extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($file = NULL) {
    // No operation, just a placeholder to avoid migration errors.
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return TRUE;
  }

}
