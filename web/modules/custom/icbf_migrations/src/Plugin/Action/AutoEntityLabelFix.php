<?php

namespace Drupal\icbf_migrations\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a fallback action for auto entity label.
 *
 * @Action(
 *   id = "auto_entitylabel_entity_update_action",
 *   label = @Translation("Fix for missing Auto Entity Label action"),
 *   type = "entity"
 * )
 */
class AutoEntityLabelFix extends ActionBase {

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
