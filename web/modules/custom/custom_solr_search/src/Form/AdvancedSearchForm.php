<?php

namespace Drupal\custom_solr_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AdvancedSearchForm extends FormBase {

  public function getFormId() {
    return 'custom_solr_advanced_search_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'custom_solr_search/global';
    $form['#attributes']['class'][] = 'custom-search-form';

    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Buscar'),
      '#attributes' => [
        'id' => 'search-input',
        'placeholder' => 'Escribe para buscar...',
        'autocomplete' => 'off',
      ],
      '#default_value' => $form_state->getValue('search'),
    ];

    $form['toggle_advanced'] = [
      '#type' => 'button',
      '#value' => $this->t('Búsqueda avanzada'),
      '#attributes' => [
        'onclick' => 'jQuery(".advanced-filters").toggle(); return false;',
        'class' => ['toggle-advanced'],
      ],
    ];

    $form['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['advanced-filters']],
    ];

    // Autor
    $users = \Drupal::entityTypeManager()->getStorage('user')->loadMultiple();
    $author_options = ['' => '- Seleccione -'];
    foreach ($users as $user) {
      if ($user->id() != 0) {
        $author_options[$user->id()] = $user->getDisplayName();
      }
    }
    $form['advanced']['author'] = [
      '#type' => 'select',
      '#title' => 'Autor',
      '#options' => $author_options,
      '#default_value' => $form_state->getValue('author'),
    ];

    // Tema
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('taxonomy');
    $topic_options = ['' => '- Seleccione -'];
    foreach ($terms as $term) {
      $topic_options[$term->tid] = $term->name;
    }
    $form['advanced']['topic'] = [
      '#type' => 'select',
      '#title' => 'Tema',
      '#options' => $topic_options,
      '#default_value' => $form_state->getValue('topic'),
    ];

    $form['advanced']['file_type'] = [
      '#type' => 'textfield',
      '#title' => 'Tipo de archivo',
      '#default_value' => $form_state->getValue('file_type'),
    ];

    $form['advanced']['filename'] = [
      '#type' => 'textfield',
      '#title' => 'Nombre del archivo',
      '#default_value' => $form_state->getValue('filename'),
    ];

    $form['advanced']['date'] = [
      '#type' => 'date',
      '#title' => 'Fecha de publicación',
      '#default_value' => $form_state->getValue('date'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Buscar',
    ];

    if ($form_state->getValue('search') || $form_state->getValue('author') || $form_state->getValue('topic')) {
      $form['results'] = $this->buildResults($form_state);
    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  
protected function buildResults(FormStateInterface $form_state) {
  $keywords = $form_state->getValue('search');
  $author = $form_state->getValue('author');
  $topic = $form_state->getValue('topic');
  $file_type = $form_state->getValue('file_type');
  $filename = $form_state->getValue('filename');
  $date = $form_state->getValue('date');

  // Paginación moderna en Drupal 10+
  $pager_parameters = \Drupal::service('pager.parameters');
  $current_page = $pager_parameters->findPage();
  $limit = 10;
  $offset = $current_page * $limit;

  $index = \Drupal::entityTypeManager()->getStorage('search_api_index')->load('icbf');
  $query = $index->query()->keys($keywords)->range($offset, $limit);

  if ($author) $query->addCondition('field_author', $author);
  if ($topic) $query->addCondition('field_topic', $topic);
  if ($file_type) $query->addCondition('field_file_type', $file_type);
  if ($filename) $query->addCondition('field_filename', $filename, 'CONTAINS');
  if ($date) $query->addCondition('field_publication_date', $date);

  $results = $query->execute();

  // Conteo para paginación
  $count_query = $index->query()->keys($keywords);
  if ($author) $count_query->addCondition('field_author', $author);
  if ($topic) $count_query->addCondition('field_topic', $topic);
  if ($file_type) $count_query->addCondition('field_file_type', $file_type);
  if ($filename) $count_query->addCondition('field_filename', $filename, 'CONTAINS');
  if ($date) $count_query->addCondition('field_publication_date', $date);

  $total_results = $count_query->execute()->getResultCount();



  // Inicializar el pager con el número total de resultados
  $current_page = \Drupal::service('pager.parameters')->findPage();
  \Drupal::service('pager.manager')->createPager($total_results, $limit);
  $rendered = [];
  foreach ($results as $item) {
    $fields = $item->getFields();
    $rendered[] = [
      'title' => isset($fields['title']) ? $fields['title']->getValues()[0] : '',
      'uri' => isset($fields['field_source_url']) ? $fields['field_source_url']->getValues()[0] : $fields['url']->getValues()[0],
      'snippet' => isset($fields['search_api_excerpt']) ? $fields['search_api_excerpt']->getValues()[0] : '',
      'file_type' => isset($fields['field_file_type']) ? $fields['field_file_type']->getValues()[0] : '',
    ];
  }
  

  return [
    '#theme' => 'search_results',
    '#results' => $rendered,
    '#total_results' => $total_results,
    '#pager' => [
      '#type' => 'pager',
    ],
  ];
}

}
