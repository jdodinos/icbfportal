<?php

namespace Drupal\icbf_migrations;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\layout_builder\Section;
use Drupal\views\Entity\View;
use Drupal\block_content\Entity\BlockContent;

/**
 * The Panelizer Migration Service class.
 */
class PanelizerMigrationService {
  private $secondDatabase = 'migrate';
  public $block_config;
  public $panelizer = FALSE;

  /**
   *
   */
  protected $database;

  /**
   *
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Get the entity types.
   */
  public function getEntityTypes() {
    $connection = Database::getConnection('default', $this->secondDatabase);
    $query = $connection->select('panelizer_entity', 'pe')
      ->fields('pe', ['entity_type'])
      ->distinct('entity_type');
    $result = $query->execute()->fetchAll();

    $entities = [];
    foreach ($result as $value) {
      switch ($value->entity_type) {
        case 'taxonomy_term':
          $entity_type_name = 'Taxonomias';
          break;

        default:
          $entity_type_name = 'Tipos de contenidos';
          break;
      }
      $entities[$value->entity_type] = $entity_type_name;
    }

    return $entities;
  }

  /**
   * The getPanelizerNodes function.
   */
  public function getPanelizerNodes($table_to_return = ['node'], $nids = []) {
    $connection = Database::getConnection('default', 'migrate');
    $query = $connection->select('node', 'n');
    $query->innerJoin('panelizer_entity', 'pe', 'n.vid = pe.revision_id');
    $query->innerJoin('panels_display', 'pd', 'pe.did = pd.did');
    $query->condition('pe.did', 0, '<>');
    // $query->condition('pd.layout', 'onecol_page', '<>');

    if (!empty($nids)) {
      $query->condition('n.nid', $nids, 'IN');
    }

    if (in_array('node', $table_to_return)) {
      $query->fields('n', ['nid', 'vid', 'title']);
      $query->addField('n', 'type', 'entity_type');
      $query->fields('pe', ['did']);
      $query->orderBy('n.nid', 'ASC');
    }
    if (in_array('panelizer_entity', $table_to_return)) {
      $query->fields('pe');
    }
    if (in_array('panels_display', $table_to_return)) {
      $query->fields('pd');
    }
    if (in_array('panels_pane', $table_to_return)) {
      $query->innerJoin('panels_pane', 'pp', 'pe.did = pp.did');
      $query->fields('pp');
      $query->orderBy('pp.position', 'ASC');
      $query->orderBy('pp.panel', 'ASC');
    }

    return $query->execute()->fetchAll();
  }

  /**
   * Fetches Panelizer vocabularies from the database.
   *
   * @param array $nids
   *   An optional array of Ids to filter the results.
   *
   * @return \Drupal\Core\Database\StatementInterface
   *   The database query result.
   */
  public function getPanelizerTerms($table_to_return = ['panelizer_entity'], $tids = []) {
    $connection = Database::getConnection('default', 'migrate');
    $query = $connection->select('panelizer_entity', 'pe');
    $query->innerJoin('panels_display', 'pd', 'pe.did = pd.did');
    $query->innerjoin('taxonomy_term_data', 'ttd', 'pe.entity_id = ttd.tid');
    $query->innerJoin('taxonomy_vocabulary', 'tv', 'tv.vid = ttd.vid');
    $query->condition('pe.did', 0, '<>');
    $query->condition('pe.entity_type', 'taxonomy_term', '=');
    $query->condition('pd.layout', 'onecol_page', '<>');
    if (!empty($tids)) {
      $query->condition('pe.entity_id', $tids, 'IN');
    }

    if (in_array('panelizer_entity', $table_to_return)) {
      $query->fields('pe');
      $query->fields('ttd', ['name', 'vid']);
      $query->addField('tv', 'name', 'vocabulary');
      $query->addField('tv', 'machine_name', ' voc_machine_name');
      $query->orderBy('ttd.vid', 'ASC');
    }
    if (in_array('panels_display', $table_to_return)) {
      $query->fields('pd');
    }
    if (in_array('panels_pane', $table_to_return)) {
      $query->innerJoin('panels_pane', 'pp', 'pe.did = pp.did');
      $query->fields('pp');
      $query->orderBy('pp.position', 'ASC');
      $query->orderBy('pp.panel', 'ASC');
    }
    // $query->range(0, 10);
    return $query->execute()->fetchAll();
  }

  /**
   * Function to get Minipanels data.
   */
  public function getPanelsMiniData($panel_name) {
    $connection = Database::getConnection('default', 'migrate');
    $query = $connection->select('panels_mini', 'pm');
    $query->innerJoin('panels_pane', 'pp', 'pm.did = pp.did');
    $query->fields('pp');
    $query->condition('pm.name', $panel_name);
    $result = $query->execute()->fetchAll();

    return $result;
  }

  public function createLayoutSections($panels_display) {
    $sections = [];
    foreach ($panels_display as $item) {
      $sections[$item->did] = [];
      $layout_settings = unserialize($item->layout_settings);

      if (empty($layout_settings) || !isset($layout_settings['items'])) {
        $layout_settings = [
          "items" => [
            "main" => [
              "type" => "row",
              "contains" => "column",
              "children" => ['main-row'],
              "parent" => NULL,
            ],
            "main-row" => [
              "type" => "row",
              "contains" => "region",
              "children" => ["center"],
              "parent" => 1,
            ],
            "center" => [
              "type" => "region",
              "title" => "Centrado",
              "width" => 100,
              "width_type" => "%",
              "parent" => "main-row",
            ],
          ],
        ];
      }

      $main = $layout_settings['items']['main'];
      foreach ($main['children'] as $region_name) {
        foreach ($layout_settings['items'] as $item_name => $value) {
          if ($item_name === $region_name) {
            $num_children = count($value['children']);
            switch ($num_children) {
              case 4:
                $section_type = 'layout_fourcol_section';
                break;

              case 3:
                $section_type = 'layout_threecol_section';
                break;

              case 2:
                $section_type = 'layout_twocol_section';
                break;

              default:
                $section_type = 'layout_onecol';
            }

            $children_info = [];
            foreach ($value['children'] as $child) {
              $children_info[$child] = $layout_settings['items'][$child] ?? [];
            }

            // Crear una sección (fila) con un layout de una columna.
            $sections[$item->did][] = [
              'children' => $value['children'],
              'children_info' => $children_info,
              'section_name' => $region_name,
              'section' => new Section($section_type),
            ];
          }
        }
      }
    }

    return $sections;
  }

  public function searchSectionInLayout($panel_name, $panels_display) {
    $position = [];
    foreach ($panels_display as $item) {
      $layout_settings = unserialize($item->layout_settings);

      if ($layout_settings && isset($layout_settings['items'][$panel_name])) {
        if ($layout_settings['items'][$panel_name]['parent'] == 'main') {
          $position = [
            'panel_name' => $panel_name,
            'section_name' => $panel_name,
          ];
          break;
        }
        else {
          $parent_name = $layout_settings['items'][$panel_name]['parent'];
          if ($layout_settings['items'][$parent_name]['parent'] == 'main') {
            $position = $parent_name;
            $position = [
              'panel_name' => $panel_name,
              'section_name' => $parent_name,
            ];
            break;
          }
          else {
            $position = ['panel_name' => $parent_name];
            $parent_name = $layout_settings['items'][$parent_name]['parent'];
            if ($layout_settings['items'][$parent_name]['parent'] == 'main') {
              $position['section_name'] = $parent_name;
            }
            else {
              $position = ['panel_name' => $parent_name];
              $parent_name = $layout_settings['items'][$parent_name]['parent'];
              if ($layout_settings['items'][$parent_name]['parent'] == 'main') {
                $position['section_name'] = $parent_name;
              }
            }
          }
        }
      }
      else {
        // If the layout settings are not available, we assume the panel is in
        // the main section.
        $position = [
          'panel_name' => $panel_name,
          'section_name' => 'main-row',
        ];
      }
    }

    return $position;
  }

  public function deleteLayoutSectionsForEntity($entity) {
    // Ensure that Layout Builder is enabled and has the field.
    if ($entity->hasField('layout_builder__layout')) {
      /** @var \Drupal\layout_builder\Field\LayoutSectionItemList $layout_field */
      $layout_field = $entity->get('layout_builder__layout');
      $count = count($layout_field);

      // Delete all items in the layout field.
      for ($i = $count - 1; $i >= 0; $i--) {
        $layout_field->removeItem($i);
      }
      // Save the entity.
      $entity->save();
    }
  }

  public function getRegionInSection($section_id, $position) {
    $section_regions = $this->getRegionsAvailableForSection($section_id);

    return $section_regions[$position];
  }

  private function getRegionsAvailableForSection($section_id) {
    $sections = [
      'layout_onecol' => ['content'],
      'layout_twocol_section' => ['first', 'second'],
      'layout_threecol_section' => ['first', 'second', 'third'],
      'layout_fourcol_section' => ['first', 'second', 'third', 'fourth'],
    ];
    return $sections[$section_id] ?? [];
  }

  public function createDefaultConfiguration($plugin_id, $label, $provider, $label_display) {
    $this->block_config = [
      'id' => $plugin_id,
      'label' => $label,
      'provider' => $provider,
      'label_display' => $label_display,
    ];

    return $this->block_config;
  }

  public function addContextMappingConfig(array $context) {
    $this->block_config['context_mapping'] = $context;
  }

  public function addFormatterConfig(array $formatter) {
    $this->block_config['formatter'] = $formatter;
  }

  public function addViewMode($view_mode) {
    $this->block_config['view_mode'] = $view_mode;
  }

  public function addViewInBlock($view_id, $configuration) {
    $this->block_config = [];
    $display_id = $configuration['display'];
    $view_id = $this->checkingViewId($view_id, $display_id);
    $view = View::load($view_id);
    $messages = [];
    if ($view) {
      if (strpos($display_id, 'page_') === 0) {
        $display_id = 'block_' . $display_id;
      }

      $displays = $view->get('display');
      if (isset($displays[$display_id])) {
        $layout_field = 'views_block';
        $label = "Views {$view_id} - Display {$displays[$display_id]['display_title']} - Display ID {$display_id}";
        $block_plugin_id = "{$layout_field}:{$view_id}-{$display_id}";
        $provider = 'views';
        $label = $configuration['override_title_text'] ?? $label;
        $label_display = $this->validateLabelDisplay($configuration);

        $this->createDefaultConfiguration(
          $block_plugin_id,
          $label,
          $provider,
          $label_display,
        );
        $this->addFormatterConfig($configuration);

        if (isset($configuration['args']) && !empty($configuration['args'])) {
          $this->block_config['arguments'] = explode('/', $configuration['args']);
        }
      }
      else {
        $messages[] = "Display {$display_id} not found in view {$view_id}";
      }
    }
    else {
      $messages[] = "View {$view_id} not found";
    }

    return [
      'block' => $this->block_config,
      'messages' => $messages,
    ];
  }

  public function addCustomBlock($subtype, $configuration, $name = '') {
    $this->block_config = [];
    $label_display = $this->validateLabelDisplay($configuration);
    $block = BlockContent::create([
      'type' => 'basic',
      'info' => 'Bloque custom in Layout Builder ' . $name,
      'body' => [
        'value' => $configuration['body'],
        'format' => $configuration['format'],
      ],
    ]);
    $block->save();

    $block_plugin_id = 'inline_block:basic';
    $label = $configuration['title'];
    $provider = 'block_content';
    $this->createDefaultConfiguration(
      $block_plugin_id,
      $label,
      $provider,
      $label_display,
    );
    $this->block_config['block_revision_id'] = $block->getRevisionId();
    $this->block_config['block_serialized'] = NULL;

    return [
      'block' => $this->block_config,
      'messages' => [],
    ];
  }

  public function addBlockContentInBlock($subtype, $configuration) {
    $this->block_config = [];
    $label_display = $this->validateLabelDisplay($configuration);
    $messages = [];

    if (strpos($subtype, 'md_megamenu') === 0) {
      $md_megamenus = [
        'md_megamenu-2' => 'menu-prueba1',
        'md_megamenu-6' => 'men-compras-locales',
        'md_megamenu-7' => 'menu-transparencia',
        'md_megamenu-8' => 'mgm-5c775d3a74ba0',
        'md_megamenu-10' => 'mgm-5ce41f0ce4a05',
        'md_megamenu-11' => 'mgm-5d3f4fe8cd42b',
        'md_megamenu-12' => 'mgm-5da0e630d2a93',
        'md_megamenu-15' => 'mgm-5e7a6aab9aad3',
        'md_megamenu-16' => 'mgm-5edaba119f9a0',
        'md_megamenu-17' => 'mgm-5edfcfafa91eb',
        'md_megamenu-18' => 'mgm-5ee27f3730710',
        'md_megamenu-19' => 'mgm-6038204b97dda',
        'md_megamenu-20' => 'mgm-6049302480ef2',
        'md_megamenu-21' => 'mfu',
      ];
      $provider = 'tb_megamenu';
      $block_plugin_id = 'tb_megamenu_menu_block:' . $md_megamenus[$subtype] ?? $subtype;
      $this->createDefaultConfiguration(
        $block_plugin_id,
        '',
        $provider,
        $label_display,
      );
      $this->addFormatterConfig($configuration);
    }
    if (strpos($subtype, 'quicktabs') === 0) {
      $provider = 'quicktabs';
      $quicktab = explode('-', $subtype);
      $block_plugin_id = 'quicktabs_block:' . $quicktab[1];
      $this->createDefaultConfiguration(
        $block_plugin_id,
        '',
        $provider,
        $label_display,
      );
      $this->addFormatterConfig($configuration);
    }
    else {
      // For all blocks.
      $position_explode = strpos($subtype, '-');
      $block_type = substr($subtype, 0, $position_explode);
      $block_id = substr($subtype, $position_explode + 1);
      if ($block_type == 'block') {
        $block_uuid = $this->database->select('block_content', 'b')
          ->fields('b', ['uuid'])
          ->condition('id', $block_id)
          ->execute()->fetchField();
      }
      elseif ($block_type == 'bean') {
        $block_title = str_replace('---', '|+|', $block_id);
        if (strpos($block_id, '--') !== FALSE) {
          $block_title = str_replace('--', '+|', $block_id);
        }

        $block_title = str_replace('-', ' ', $block_title);
        $block_title = str_replace('|+|', ' - ', $block_title);
        $block_title = str_replace('+|', '- ', $block_title);
        // Caso muy particular de un bloque de páginas.
        if ($block_id == 'paginas-internas-primera-infanci') {
          $block_title = 'Páginas internas Primera Infancia';
        }
        elseif ($block_id == '1519---directos-servicios-ciudad') {
          $block_title = 'Servicios Ciudadania - 1519-1';
        }
        elseif ($block_id == 'servicios-pqr---1519-3') {
          $block_title = 'Servicios PQR - 1519-3';
        }
        elseif ($block_id == 'titulo-micrositio') {
          $block_title = 'Observatorio de Bienestar - top';
        }
        $block_query = $this->database->select('block_content', 'b');
        $block_query->join('block_content_field_data', 'bcfd', 'b.id = bcfd.id');
        $block_query->fields('b', ['uuid'])
          ->condition('bcfd.info', $block_title);
        $block_uuid = $block_query->execute()->fetchField();
      }

      if (isset($block_uuid)) {
        $block_plugin_id = "block_content:$block_uuid";
        $provider = 'block_content';

        $this->createDefaultConfiguration(
          $block_plugin_id,
          '',
          $provider,
          $label_display,
        );
        $this->addFormatterConfig($configuration);
        $this->addViewMode('full');
        $field_configuration = $this->block_config;
      }
    }

    if (!isset($block_plugin_id)) {
      $block_plugin_id = $subtype;
    }

    // Verificar existencia del plugin.
    $plugin_manager = \Drupal::service('plugin.manager.block');
    if (!$plugin_manager->hasDefinition($block_plugin_id)) {
      $messages[] = "Block plugin ID {$block_plugin_id} not found.";
    }

    return [
      'block' => $this->block_config,
      'messages' => $messages,
    ];
  }

  private function validateLabelDisplay($configuration) {
    $label_display = FALSE;
    if (isset($configuration['override_title']) && $configuration['override_title'] && isset($configuration['override_title_text']) && $configuration['override_title_text']) {
      $label_display = TRUE;
    }

    return $label_display;
  }

  public function insertNewLog($entity_id, $entity_type, $messages = NULL) {
    // Save the record to have information about the migration.
    $record_id = $this->database->select('panelizer_migration', 'pm')
      ->fields('pm', ['id'])
      ->condition('entity_id', $entity_id)
      ->condition('entity_type', $entity_type)
      ->execute()
      ->fetchField();

    $fields = [
      'entity_id' => $entity_id,
      'entity_type' => $entity_type,
      'status' => 1,
    ];
    if (!empty($messages)) {
      // Status 2 indicates that there were messages.
      $fields['status'] = 2;
      $fields['data'] = json_encode($messages);
    }
    if ($record_id) {
      // Update the existing record.
      $this->database->update('panelizer_migration')
        ->fields($fields)
        ->condition('id', $record_id)
        ->execute();
    }
    else {
      // Insert a new record.
      $this->database->insert('panelizer_migration')
        ->fields($fields)
        ->execute();
    }
  }

  /**
   * Validates the entity before migration.
   *
   * @param int $entity_id
   *   The entity ID to validate.
   * @param string $entity_type
   *   The entity type (e.g., node, user).
   *
   * @return bool
   *   TRUE if the entity is valid for migration, FALSE otherwise.
   */
  public function validateEntity($entity_id, $entity_type) {
    $query = $this->database->select('panelizer_migration', 'pm')
      ->fields('pm')
      ->condition('entity_id', $entity_id)
      ->condition('entity_type', $entity_type)
      ->execute()
      ->fetchObject();

    return $query ?? FALSE;
  }

  private function checkingViewId($view_id, $display_id) {
    switch ($view_id) {
      case 'news':
        if ($display_id == 'block_newslist') {
          $view_id = 'noticias_sapi';
        }
        break;

      case 'documentos_de_contratacion':
        if ($display_id == 'block') {
          $view_id = 'documentos_de_contratacion_sapi';
        }
        break;

      case 'licencias_de_funcionamiento':
        if ($display_id == 'block' || $display_id == 'block_1' || $display_id == 'block_2') {
          $view_id = 'licencias_de_funcionamiento_sapi';
        }
        break;

      case 'curriculum_vitae':
        if ($display_id == 'block_2') {
          $view_id = 'curriculum_vitae_sapi';
        }
        break;

      case 'notifications_pc':
        if ($display_id == 'block') {
          $view_id = 'notifications_pc_sapi';
        }
        break;

      case 'right_of_petition':
        if ($display_id == 'block_vista5dias') {
          $view_id = 'right_of_petition_sapi';
        }
        break;

      case 'documentos_servicios':
        if ($display_id == 'block_5') {
          $view_id = 'documentos_servicios_sapi';
        }
        break;

      case 'vacancies':
        if ($display_id == 'block_cpdr') {
          $view_id = 'vacancies_sapi';
        }
        break;

      case 'encargos_sapi':
        if ($display_id == 'block') {
          $view_id = 'encargos_sapi_solr';
        }
        break;

      case 'hiring_process':
        if ($display_id == 'block' || $display_id == 'block_1') {
          $view_id = 'hiring_process_sapi';
        }
        break;

      case 'documents':
        if ($display_id == 'block' || $display_id == 'block_9' || $display_id == 'block_10' || $display_id == 'block_11') {
          $view_id = 'documents_sapi';
        }
        break;
    }

    return $view_id;
  }

}
