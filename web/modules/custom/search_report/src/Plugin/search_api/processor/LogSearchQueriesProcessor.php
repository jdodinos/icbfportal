<?php

namespace Drupal\search_report\Plugin\search_api\processor;

use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Query\QueryInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @SearchApiProcessor(
 *   id = "log_search_queries_processor",
 *   label = @Translation("Log Search Queries Processor"),
 *   description = @Translation("Registra las búsquedas realizadas en una tabla personalizada."),
 *   stages = {
 *     "pre_query" = 0
 *   }
 * )
 */
class LogSearchQueriesProcessor extends ProcessorPluginBase {

  /**
   * La conexión a base de datos.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $instance */
    $instance = parent::createInstance($container, $configuration, $plugin_id, $plugin_definition);
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   *
   * Se ejecuta antes de que la búsqueda se envíe a Solr (stages.pre_query).
   */
  public function preSearch(QueryInterface $query) {
    // Obtener el término de búsqueda.
    // Nota: la propiedad "keys" del query puede variar según cómo se realice la búsqueda en tu configuración.
    $search_keys = $query->getKeys();

    // A veces, $search_keys podría ser array, string, etc. Manejamos ambos casos.
    $search_term = '';
    if (is_string($search_keys)) {
      $search_term = $search_keys;
    }
    elseif (is_array($search_keys)) {
      $search_term = implode(' ', $search_keys);
    }

    // Opcional: Obtener cuántos resultados se estiman, 
    // pero en "preSearch" aún no se ha ejecutado la consulta.
    // En su lugar, podemos dejarlo en 0 o manejarlo de otra forma.

    // Registrar en la tabla:
    if (!empty($search_term)) {
      $this->connection->insert('search_log')
        ->fields([
          'search_term' => $search_term,
          'results_count' => 0,     // Ajustar después si fuera necesario.
          'created' => \Drupal::time()->getRequestTime(),
        ])
        ->execute();
    }
  }

}
