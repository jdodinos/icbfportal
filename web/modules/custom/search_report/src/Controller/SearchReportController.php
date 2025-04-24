<?php

namespace Drupal\search_report\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Controlador para mostrar reportes de búsquedas.
 */
class SearchReportController extends ControllerBase {

  /**
   * Conexión a la base de datos.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * Construye la página de reporte.
   */
  public function reportPage() {
    // Ejemplo: obtener las 50 búsquedas más recientes.
    $query = $this->connection->select('search_log', 'srl')
      ->fields('srl', ['id', 'search_term', 'results_count', 'created'])
      ->orderBy('created', 'DESC')
      ->range(0, 50);
    $results = $query->execute()->fetchAll();

    // Prepara datos para la plantilla.
    $rows = [];
    foreach ($results as $record) {
      $rows[] = [
        'id' => $record->id,
        'search_term' => $record->search_term,
        'results_count' => $record->results_count,
        // Convertir timestamp a fecha legible.
        'created' => date('d/m/Y H:i', $record->created),
      ];
    }

    return [
      '#theme' => 'search_report_template',
      '#title' => $this->t('Reporte de Búsquedas'),
      '#items' => $rows,
      // Podrías inyectar más data para graficar.
    ];
  }

}
