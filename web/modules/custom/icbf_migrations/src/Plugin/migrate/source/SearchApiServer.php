<?php

namespace Drupal\icbf_migrations\Plugin\migrate\source;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal 7 Search API Server from database.
 *
 * For available configuration keys, refer to the parent classes.
 *
 * @see \Drupal\migrate\Plugin\migrate\source\SqlBase
 * @see \Drupal\migrate\Plugin\migrate\source\SourcePluginBase
 *
 * @MigrateSource(
 *   id = "d7_search_api_server",
 *   source_module = "search_api"
 * )
 */
class SearchApiServer extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('search_api_server', 't')
      ->fields('t', ['id', 'name', 'machine_name', 'class', 'options', 'enabled', 'status'])
      ->condition('t.class', 'search_api_db_service');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'id' => $this->t('The numeric identifier of the block/box'),
      'name' => $this->t('The block/box content'),
      'machine_name' => $this->t('Admin title of the block/box.'),
      'class' => $this->t('Input format of the content block/box content.'),
      'options' => $this->t('Input format of the content block/box content.'),
      'enabled' => $this->t('Input format of the content block/box content.'),
      'status' => $this->t('Input format of the content block/box content.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['machine_name']['type'] = 'string';
    return $ids;
  }

}
