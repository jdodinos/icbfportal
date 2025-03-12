<?php

namespace Drupal\icbf_migrations\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a fallback action for Pathauto taxonomy term.
 *
 * @Action(
 *   id = "pathauto_taxonomy_term_update_action",
 *   label = @Translation("Fix for missing Pathauto Taxonomy Term action"),
 *   type = "taxonomy_term"
 * )
 */
class PathautoTaxonomyTermFix extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($taxonomy_term = NULL) {
    // No operation, just a placeholder to avoid migration errors.
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return TRUE;
  }

}
