<?php

namespace Drupal\custom_route_indexer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\Entity\Index; 

class RouteIndexerForm extends ConfigFormBase {

  public function getFormId() {
    return 'custom_route_indexer_form';
  }

  protected function getEditableConfigNames() {
    return ['custom_route_indexer.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('custom_route_indexer.settings');
    $routes = $config->get('routes') ?? [];
    $selected_index = $config->get('index_id');
  
    // Cargar todos los índices disponibles.
    $options = [];
    $indexes = Index::loadMultiple();
    foreach ($indexes as $index) {
      $options[$index->id()] = $index->label();
    }
  
    $form['index_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Índice de Search API'),
      '#options' => $options,
      '#default_value' => $selected_index,
      '#required' => TRUE,
    ];
  
    $form['routes'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Rutas para indexar'),
      '#tree' => TRUE,
    ];
  
    $num_routes = $form_state->get('num_routes');
    if ($num_routes === NULL) {
      $num_routes = count($routes) ?: 1;
      $form_state->set('num_routes', $num_routes);
    }
  
    for ($i = 0; $i < $num_routes; $i++) {
      $form['routes'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Ruta %num', ['%num' => $i + 1]),
        '#default_value' => $routes[$i] ?? '',
      ];
    }
  
    $form['add_route'] = [
      '#type' => 'submit',
      '#value' => $this->t('Agregar Ruta'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::updateRoutesCallback',
        'wrapper' => 'routes-wrapper',
      ],
    ];
  
    $form['#prefix'] = '<div id="routes-wrapper">';
    $form['#suffix'] = '</div>';
  
    return parent::buildForm($form, $form_state);
  }

  public function updateRoutesCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  public function addOne(array &$form, FormStateInterface $form_state) {
    $num = $form_state->get('num_routes');
    $form_state->set('num_routes', $num + 1);
    $form_state->setRebuild();
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('custom_route_indexer.settings')
      ->set('index_id', $form_state->getValue('index_id'))
      ->set('routes', $form_state->getValue('routes'))
      ->save();
  
    parent::submitForm($form, $form_state);
  }
}
