<?php

namespace Drupal\icbf_migrations\Plugin\migrate\source;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal 7 Bean block source from database.
 *
 * For available configuration keys, refer to the parent classes.
 *
 * @see \Drupal\migrate\Plugin\migrate\source\SqlBase
 * @see \Drupal\migrate\Plugin\migrate\source\SourcePluginBase
 *
 * @MigrateSource(
 *   id = "bean_block",
 *   source_module = "bean"
 * )
 */
class BeanBlock extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $bean_data = $this->select('bean', 'b');
    $bean_data->join('field_data_field_builder', 'fb', 'fb.entity_id = b.bid');
    $bean_data->fields('b');
    $bean_data->fields('fb', ['bundle', 'language', 'field_builder_value', 'field_builder_format']);

    return $bean_data;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'bid' => $this->t('The numeric identifier of the block/box'),
      'label' => $this->t('Admin title of the block/box.'),
      'field_builder_value' => $this->t('The block/box content'),
      'field_builder_format' => $this->t('Input format of the content block/box content.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['bid']['type'] = 'integer';
    return $ids;
  }

}
