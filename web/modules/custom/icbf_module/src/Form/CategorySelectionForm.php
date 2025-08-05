<?php

namespace Drupal\icbf_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The form for migrating Panelizer.
 */
class CategorySelectionForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'category_selection_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['container'] = [
      '#prefix' => '<div class="widgetCitizenContainer container"><div id="icbf-module-category-selection-form" class="row">',
      '#suffix' => '</div></div>',
      'header' => [
        '#prefix' => '<span style="font-size:14px;">',
        '#suffix' => '</span>',
        '#markup' => 'Fecha de Actualización: 19/05/2025',
      ],
    ];

    $options_cat_citizen = $this->termAsOptions('citizen_category');
    $options = ['undefined' => 'Quien eres'];
    foreach ($options_cat_citizen as $term_id => $term_name) {
      $options[$term_id] = $term_name;
    }
    $form['container']['left'] = [
      '#prefix' => '<div class="col-xs-12 col-sm-6">',
      '#suffix' => '</div>',
      'citizen_category' => [
        '#type' => 'select',
        '#title' => $this->t('What is your current situation'),
        '#title_display' => FALSE,
        '#options' => $options,
        '#default_value' => 'undefined',
        '#attributes' => ['class' => ['select-citizen-category']],
      ],
    ];

    $options = ['undefined' => 'Qué te gustaría hacer'];
    $form['container']['right'] = [
      '#prefix' => '<div class="col-xs-12 col-sm-6">',
      '#suffix' => '</div>',
      'procedure' => [
        '#prefix' => '<div id="container-procedure" class="col-xs-12 col-sm-6">',
        '#suffix' => '</div>',
        '#type' => 'select',
        '#title' => $this->t('What do you want to do'),
        '#title_display' => FALSE,
        '#options' => $options,
        '#default_value' => 'undefined',
        '#attributes' => ['class' => ['select-procedure']],
        '#states' => [
          'visible' => [
            ':input[name="citizen_category"]' => ['!value' => 'undefined'],
          ],
        ],
      ],
      'button' => [
        '#type' => 'container',
        '#markup' => '<a class="btn btn-default form-button" href="#">Consulta el portafolio de Servicios Institucional</a>',
        '#states' => [
          'visible' => [
            ':input[name="citizen_category"]' => ['!value' => 'undefined'],
          ],
        ],
      ],
    ];

    $form['#attached']['library'][] = 'icbf_module/style';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Return an array of vocabulary.
   *
   * @param string $vocabulary
   *   The vocabulary machine name.
   *
   * @return array
   *   The options.
   */
  private function termAsOptions($vocabulary) {
    $options = [];
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
      ->loadTree($vocabulary);

    foreach ($terms as $t) {
      $options[$t->tid] = $t->name;
    }

    return $options;
  }

}
