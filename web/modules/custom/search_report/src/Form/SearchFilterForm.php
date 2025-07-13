<?php

namespace Drupal\search_report\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Symfony\Component\HttpFoundation\Request;
class SearchFilterForm extends FormBase {

  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  public function getFormId() {
    return 'search_reports_filter_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
	  $request = Request::createFromGlobals();
	  $form['start_date'] = [
      		'#type' => 'date',
      		'#title' => $this->t('Fecha de Inicio'),
		'#required' => TRUE,
		'#default_value'=>$request->query->get("start"),	
		
    	  ];

    $form['end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Fecha de Fin'),
      '#required' => TRUE,
      '#default_value'=>$request->query->get("end")
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Buscar'),
      '#button_type' => 'primary',
    ];

    $form['actions']['reset'] = [
      '#type' => 'submit',
      '#value' => $this->t('Limpiar'),
      '#submit' => ['::resetForm'],
    ];

    if ($request->query->get("start")) {
	$data = $this->getSearchData($request->query->get("start"), $request->query->get("end"));
	\Drupal::logger('search_report')->debug('<pre>DATA A TWIG: @data</pre>', [
 	   '@data' => print_r($data, TRUE),
  	]);
      	$form['report'] = [
        	'#theme' => 'search_reports',
        	'#data' => $data,
      	];
    }
    $values = $form_state->getValues();

    // 2) Registra el dump de valores en dblog.
    \Drupal::logger('search_report')->log(
      RfcLogLevel::DEBUG,
      '<pre>FormState values: @vals</pre>',
      ['@vals' => print_r($values, TRUE)]
    );

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
  $start = $form_state->getValue('start');
  $end   = $form_state->getValue('end');

  // Redirige a la misma ruta, pero agregando ?start=YYYY-MM-DD&end=YYYY-MM-DD
  $form_state->setRedirect('search_reports.dashboard', [], [
    'query' => [
      'start' => $start,
      'end'   => $end,
    ],
  ]);
  }

  public function resetForm(array &$form, FormStateInterface $form_state) {
    $form_state->setValues([]);
    $form_state->setRebuild();
  }

  private function getSearchData($start, $end) {
    $query = $this->database->select('search_log', 's')
      ->fields('s', ['term', 'results_count', 'response_time', 'timestamp'])
      ->condition('timestamp', strtotime($start), '>=')
      ->condition('timestamp', strtotime($end . ' 23:59:59'), '<=');

    $results = $query->execute()->fetchAll();

    $processed = [
      'response_times' => [],
      'search_usage' => [],
      'most_searched' => [],
      'no_results' => [],
    ];

    foreach ($results as $row) {
      $date = date('d/m/Y H:i', $row->timestamp);

      $processed['response_times'][] = [
        'x' => $date,
        'y' => (float) $row->response_time,
      ];

      $processed['search_usage'][] = [
        'x' => $date,
        'y' => 1,
      ];

      if ($row->results_count > 0) {
        $processed['most_searched'][$row->term] = ($processed['most_searched'][$row->term] ?? 0) + 1;
      }
      else {
        $processed['no_results'][] = [
          'term' => $row->term,
          'date' => $date,
        ];
      }
    }

    return $processed;
  }
}
