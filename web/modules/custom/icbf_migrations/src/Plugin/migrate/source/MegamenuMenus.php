<?php

namespace Drupal\icbf_migrations\Plugin\migrate\source;

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
 *   id = "d7_megamenu_menus",
 *   source_module = "md_megamenu"
 * )
 */
class MegamenuMenus extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('md_megamenus', 'b')
      ->fields('b', ['mid', 'title', 'description', 'machine_name']);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'mid' => $this->t('The numeric identifier of the menu'),
      'title' => $this->t('The menu name'),
      'description' => $this->t('The menu description'),
      'machine_name' => $this->t('The machine name for the menu.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['mid']['type'] = 'integer';
    return $ids;
  }

}
