<?php

namespace Drupal\custom_solr_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides the custom search block.
 *
 * @Block(
 *   id = "custom_solr_search_block",
 *   admin_label = @Translation("Custom Solr Search Block")
 * )
 */
class CustomSearchBlock extends BlockBase {
  public function build() {
    return \Drupal::formBuilder()->getForm('\Drupal\custom_solr_search\Form\AdvancedSearchForm');
  }
}
