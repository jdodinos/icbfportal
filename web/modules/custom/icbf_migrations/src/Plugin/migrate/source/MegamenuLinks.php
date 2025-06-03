<?php

namespace Drupal\icbf_migrations\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;
use Drupal\block_content\Entity\BlockContent;

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
      ->fields('b', ['tid', 'mid', 'settings', 'position', 'items']);
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
    $mid = $row->getSourceProperty('mid');
    $settings = $row->getSourceProperty('settings');
    $data = unserialize($settings);
    $link_title = $data['general']['title'];
    $link_path = $data['general']['path'];
    $options = [
      'attributes' => ['title' => $link_title],
    ];

    $items = $row->getSourceProperty('items');
    $items = unserialize($items);
    $block = [];
    foreach ($items as $key => $item) {
      foreach ($item as $k => $v) {
        foreach ($v as $kk => $vv) {
          $html_data = (array) $vv;
          if (isset($html_data['type']) && $html_data['type'] == 'html') {
            $block_title = 'Block megamenu - ' . $mid . ' '. $link_title . ' ' . $key . ' ' . $k . ' ' . $kk;

            $entity_type_manager = \Drupal::entityTypeManager();
            $query = $entity_type_manager->getStorage('block_content')->getQuery();
            $query->accessCheck(FALSE);
            $query->condition('info', $block_title);
            $ids = $query->execute();
            if (empty($ids)) {
              $block = BlockContent::create([
                'info' => $block_title,
                'type' => 'basic',
                'langcode' => 'en',
                'title' => $block_title,
                'body' => [
                  'value' => urldecode($html_data['html']),
                  'format' => 'full_html',
                ],
                'settings' => [
                  'block_content_type' => 'basic',
                  'block_content' => [
                    'type' => 'basic',
                    'langcode' => 'en',
                    'title' => $block_title,
                    'body' => [
                      'value' => urldecode($html_data['html']),
                      'format' => 'full_html',
                    ],
                  ],
                ],
                'plugin' => 'block_content:basic',
              ]);
              $block->save();
            }
          }
        }
      }
    }

    $row->setSourceProperty('link_title', $link_title);
    $row->setSourceProperty('link_path', $link_path);
    $row->setSourceProperty('options', $options);
    return parent::prepareRow($row);
  }

}
