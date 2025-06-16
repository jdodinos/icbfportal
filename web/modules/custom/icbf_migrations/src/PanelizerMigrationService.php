<?php

namespace Drupal\icbf_migrations;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

/**
 * The Panelizer Migration Service class.
 */
class PanelizerMigrationService {
  private $secondDatabase = 'migrate';

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
