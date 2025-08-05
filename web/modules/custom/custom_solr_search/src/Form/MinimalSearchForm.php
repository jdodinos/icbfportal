<?php

namespace Drupal\custom_solr_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class MinimalSearchForm extends FormBase {

  public function getFormId() {
    return 'custom_solr_minimal_search_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t(''),
      '#attributes' => [
        'placeholder' => $this->t('Buscar por palabra'),
        'aria-label' => $this->t('Buscar'),
      ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('🔍'),
      '#attributes' => ['class' => ['minimal-search-button']],
    ];
    // Evitar que Drupal añada wrappers extra.
    //$form['#theme_wrappers'] = [];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Redirige a /buscador con el parámetro search.
    $search = $form_state->getValue('search');
    $form_state->setRedirect('custom_solr_search.search_page', [], ['query' => ['search' => $search]]);
  }
}

