<?php

namespace Drupal\icbf_migrations\Plugin\migrate\source;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;
use Drupal\migrate\Row;

/**
 * Drupal 7 Youtube Settings source from database.
 *
 * For available configuration keys, refer to the parent classes.
 *
 * @see \Drupal\migrate\Plugin\migrate\source\SqlBase
 * @see \Drupal\migrate\Plugin\migrate\source\SourcePluginBase
 *
 * @MigrateSource(
 *   id = "youtube_settings",
 *   source_module = "youtube"
 * )
 */
class YoutubeSettings extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('variable', 'v')
      ->fields('v', ['name', 'value'])
      ->condition('name', '%youtube%', 'LIKE');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => $this->t('The configuration name'),
      'value' => $this->t('The configuration value.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['name']['type'] = 'string';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $property_name = $row->getSourceProperty('name');
    $property_value = unserialize($row->getSourceProperty('value'));

    $row->setSourceProperty($property_name, $property_value);
    return parent::prepareRow($row);
  }

}
