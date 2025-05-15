<?php

namespace Drupal\custom_solr_extractor\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\Entity\Index;

class RoutesSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return ['custom_solr_extractor.settings'];
  }

  public function getFormId() {
    return 'custom_solr_extractor_routes_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('custom_solr_extractor.settings');
    $routes = $form_state->get('routes') ?? $config->get('routes') ?? [];
    $selected_index = $config->get('selected_index');

    // Inicializa el contador de rutas.
    if ($form_state->get('num_routes') === NULL) {
      $form_state->set('num_routes', count($routes) ?: 1);
    }

    $num_routes = $form_state->get('num_routes');

    // Índices disponibles.
    $index_options = [];
    $indexes = Index::loadMultiple();
    foreach ($indexes as $index_id => $index) {
      $index_options[$index_id] = $index->label();
    }

    $form['selected_index'] = [
      '#type' => 'select',
      '#title' => $this->t('Índice de Solr'),
      '#options' => $index_options,
      '#default_value' => $selected_index,
      '#required' => TRUE,
    ];

    $form['routes'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Rutas de documentos'),
    ];

    for ($i = 0; $i < $num_routes; $i++) {
      $form['routes']["route_$i"] = [
        '#type' => 'textfield',
        '#title' => $this->t('Ruta @i', ['@i' => $i + 1]),
        '#default_value' => $routes[$i] ?? '',
      ];
    }

    $form['add_route'] = [
      '#type' => 'submit',
      '#value' => $this->t('Agregar Ruta'),
      '#submit' => ['::addRoute'],
      '#limit_validation_errors' => [],
      '#ajax' => ['callback' => '::ajaxCallback', 'wrapper' => 'routes-wrapper'],
    ];

    $form['#prefix'] = '<div id="routes-wrapper">';
    $form['#suffix'] = '</div>';

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Guardar configuración'),
    ];
    $form['actions']['run_now'] = [
        '#type' => 'submit',
        '#value' => $this->t('Ejecutar ahora'),
        '#submit' => ['::runNow'],
      ];
    return $form;
  }
  public function runNow(array &$form, FormStateInterface $form_state) {
    // Llama al proceso de extracción.
    \custom_solr_extractor_process_documents( $form_state->getValue('selected_index'));
  
    $this->messenger()->addStatus($this->t('Procesamiento manual ejecutado.'));
  }
  
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  public function addRoute(array &$form, FormStateInterface $form_state) {
    $num_routes = $form_state->get('num_routes');
    $form_state->set('num_routes', $num_routes + 1);
    $form_state->setRebuild(TRUE);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $routes = [];

    foreach ($values as $key => $value) {
      if (strpos($key, 'route_') === 0 && !empty($value)) {
        $routes[] = $value;
      }
    }

    $this->config('custom_solr_extractor.settings')
      ->set('routes', $routes)
      ->set('selected_index', $form_state->getValue('selected_index'))
      ->save();

    $this->messenger()->addStatus($this->t('Configuración guardada.'));
  }
}
