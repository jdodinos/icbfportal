<?php

namespace Drupal\search_report\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    $form['start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Fecha de Inicio'),
      '#required' => TRUE,
    ];

    $form['end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Fecha de Fin'),
      '#required' => TRUE,
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

    if ($form_state->getValue('start_date')) {
      $data = $this->getSearchData($form_state->getValue('start_date'), $form_state->getValue('end_date'));
      $form['report'] = [
        '#theme' => 'search_reports',
        '#data' => $data,
      ];
    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {}

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
