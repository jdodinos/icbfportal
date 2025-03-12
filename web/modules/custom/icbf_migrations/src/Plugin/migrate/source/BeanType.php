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
 *   id = "d7_block_bean_type",
 *   source_module = "bean"
 * )
 */
class BeanType extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('bean_type', 'b')->fields('b');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'type_id' => $this->t('The numeric identifier of the block/box'),
      'name' => $this->t('The block/box content'),
      'label' => $this->t('Admin title of the block/box.'),
      'format' => $this->t('Input format of the content block/box content.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['type_id']['type'] = 'string';
    return $ids;
  }

}
