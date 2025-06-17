<?php

namespace Drupal\icbf_migrations;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\layout_builder\Section;

/**
 * The Panelizer Migration Service class.
 */
class PanelizerMigrationService {
  private $secondDatabase = 'migrate';
  public $block_config;

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

    // if (in_array('node', $table_to_return)) {
    //   $query->fields('n', ['nid', 'vid', 'title']);
    //   $query->addField('n', 'type', 'entity_type');
    //   $query->fields('pe', ['did']);
    //   $query->orderBy('n.nid', 'ASC');
    // }
    if (in_array('panelizer_entity', $table_to_return)) {
      $query->fields('pe');
      $query->fields('ttd', ['name', 'vid']);
      $query->addField('tv', 'name', 'vocabulary');
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

            // Crear una sección (fila) con un layout de una columna.
            $sections[$item->did][] = [
              'children' => $value['children'],
              'section_name' => $region_name,
              'section' => new Section($section_type),
            ];
          }
        }
      }
    }

    return $sections;
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

}
