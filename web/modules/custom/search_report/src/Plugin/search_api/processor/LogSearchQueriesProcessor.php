<?php

namespace Drupal\search_report\Plugin\search_api\processor;

use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Query\QueryInterface;
use Drupal\Core\Database\Connection;
use Drupal\search_api\Annotation\SearchApiProcessor; 
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
/**
 * @SearchApiProcessor(
 *   id = "log_search_queries_processor",
 *   label = @Translation("Log Search Queries Processor"),
 *   description = @Translation("Registra las búsquedas realizadas en una tabla personalizada."),
 *   stages = {
 *     "preprocess_query" = 0
 *   }
 * )
 */
class LogSearchQueriesProcessor extends ProcessorPluginBase implements ContainerFactoryPluginInterface{

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
 public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $connection) {
    // Llamamos al constructor padre para que ProcessorPluginBase configure lo básico.
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Guardamos la conexión en la propiedad para poder usarla en preprocessSearchQuery().
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   *
   * Este método se encarga de “inyectar” la conexión a la base de datos
   * desde el contenedor de servicios de Drupal.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // Crea la instancia con el constructor definido arriba, pasándole 'database'.
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }
  /**
   * {@inheritdoc}
   *
   * Se ejecuta antes de que la búsqueda se envíe a Solr (stages.pre_query).
   */
  public function preprocessSearchQuery(QueryInterface $query) {
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
          'term' => $search_term,
	  'results_count' => 0,
     	  'response_time' => 0,	  // Ajustar después si fuera necesario.
	  'timestamp' => \Drupal::time()->getRequestTime(),
        ])
        ->execute();
    }
  }

}

