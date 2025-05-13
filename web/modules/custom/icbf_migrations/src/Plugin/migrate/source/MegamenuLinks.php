<?php

namespace Drupal\icbf_migrations\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal 7 MegaMenu tabs source from database.
 *
 * For available configuration keys, refer to the parent classes.
 *
 * @see \Drupal\migrate\Plugin\migrate\source\SqlBase
 * @see \Drupal\migrate\Plugin\migrate\source\SourcePluginBase
 *
 * @MigrateSource(
 *   id = "d7_megamenu_links",
 *   source_module = "md_megamenu"
 * )
 */
class MegamenuLinks extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('md_megamenu_tabs', 'b');
    $query->join('md_megamenus', 'c', 'b.mid = c.mid');
    $query->fields('c', ['machine_name'])
      ->fields('b', ['tid', 'mid', 'settings', 'position']);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'tid' => $this->t('The numeric identifier of the tab'),
      'mid' => $this->t('The numeric identifier of the menu'),
      'settings' => $this->t('The menu item data.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['tid']['type'] = 'integer';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $settings = $row->getSourceProperty('settings');
    $data = unserialize($settings);
    $link_title = $data['general']['title'];
    $link_path = $data['general']['path'];
    $options = [
      'attributes' => ['title' => $link_title],
    ];

    $row->setSourceProperty('link_title', $link_title);
    $row->setSourceProperty('link_path', $link_path);
    $row->setSourceProperty('options', $options);
    return parent::prepareRow($row);
  }

}
