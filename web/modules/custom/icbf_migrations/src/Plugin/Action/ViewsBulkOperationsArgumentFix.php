<?php

namespace Drupal\icbf_migrations\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a fallback action for Views Bulk Operations Argument.
 *
 * @Action(
 *   id = "views_bulk_operations_argument_selector_action",
 *   label = @Translation("Fix for missing Views Bulk Operations Argument action"),
 *   type = "entity"
 * )
 */
class ViewsBulkOperationsArgumentFix extends ActionBase {

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
