<?php

namespace Drupal\custom_solr_search\Controller;

use Drupal\Core\Controller\ControllerBase;

class SearchPageController extends ControllerBase {

  public function renderSearchPage() {
    $block_manager = \Drupal::service('plugin.manager.block');
    $block = $block_manager->createInstance('custom_solr_search_block')->build();

    return [
      '#markup' => '',
      'content' => $block,
    ];
  }

}
