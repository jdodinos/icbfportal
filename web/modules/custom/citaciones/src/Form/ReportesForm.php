<?php

namespace Drupal\citaciones\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * The ReportesForm class.
 *
 * @package Drupal\citaciones\Form
 */
class ReportesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'reportes_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $locations = $this->getOptionsVocabulary('locations');
    $departments_municipalities = $this->getOptionsVocabulary('departments_municipalities');

    $form['general_parameters'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General Parameters'),
    ];
    $form['general_parameters']['leftcol'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-sm-6']],
      'description' => [
        '#markup' => $this->t('Select a general parameter to focus the information that will be shown in the report'),
      ],
    ];

    $form['general_parameters']['leftcol']['field_region_office'] = [
      '#type' => 'select',
      '#title' => $this->t('Regional'),
      '#options' => $locations,
      '#empty_option' => $this->t('- Select a region office -'),
      '#attributes' => ['class' => ['hs_summon_report']],
    ];

    $form['general_parameters']['leftcol']['locality'] = [
      '#type' => 'select',
      '#title' => $this->t('Location'),
      '#options' => $departments_municipalities,
      '#empty_option' => $this->t('- Select a location -'),
      '#attributes' => ['class' => ['hs_summon_report']],
    ];

    $form['general_parameters']['leftcol']['defendant'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Family defendant'),
      '#attributes' => ['maxlength' => 100],
      '#autocomplete_route_name' => 'citaciones.autocomplete_officer_name',
    ];

    $form['general_parameters']['leftcol']['set_button'] = [
      '#markup' => '<button id="set_button">' . $this->t('Set parameters') . '</button>',
    ];

    $form['general_parameters']['rightcol'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-sm-6']],
      'description' => [
        '#markup' => $this->t('Data of report'),
      ],
    ];

    $form['general_parameters']['rightcol']['data'] = [
      '#type' => 'checkboxes',
      '#description' => $this->t('Select the data that will be shown in the report.'),
      '#options' => [
        'field_officer_name' => $this->t('Summons to hearing'),
        'title' => $this->t('Citation name'),
        'field_region_office' => $this->t('Regional'),
        'field_lugar' => $this->t('Departament'),
        'field_creation_date' => $this->t('Creation date and hour'),
        'field_unpublish_date' => $this->t('Unpublish date and hour'),
        'field_legal_act' => $this->t('Legal action'),
        'field_act_number' => $this->t('Auto number'),
        'field_legal_act_date' => $this->t('Auto date and hour'),
        'field_legal_resolution' => $this->t('Hearing name'),
        'field_resolution_date' => $this->t('Hearing date and hour'),
        'field_sim_number' => $this->t('SIM number'),
        'field_summoned_address' => $this->t('Summon address'),
      ],
    ];

    $form['general_parameters']['rightcol']['summoned_data'] = [
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
      '#type' => 'fieldset',
    ];

    $form['general_parameters']['rightcol']['summoned_data']['data_summoned'] = [
      '#type' => 'checkboxes',
      '#title' => 'Datos citado',
      '#options' => [
        'field_full_name' => $this->t('Complete name'),
        'field_document_type' => $this->t('Document type'),
        'field_citizen_id' => $this->t('Document number'),
      ],
    ];

    $form['general_parameters']['rightcol']['child_data'] = [
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
      '#type' => 'fieldset',
    ];

    $form['general_parameters']['rightcol']['child_data']['data_child'] = [
      '#type' => 'checkboxes',
      '#title' => 'Datos niño, niña o adolescente',
      '#options' => [
        'field_children_fullname' => $this->t('Complete name'),
        'field_document_type' => $this->t('Document type'),
        'field_citizen_id' => $this->t('Document number'),
      ],
    ];

    $form['report_range'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Report range'),
    ];
    $form['report_range']['create_date'] = [
      '#type' => 'container',
      '#title' => 'Consolidado rango por fecha de creación',
    ];
    $form['report_range']['create_date']['description'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#markup' => $this->t('Generate a report by range of creation date'),
    ];

    $format = 'm/d/Y';
    $form['report_range']['create_date']['create_start_date'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#title' => $this->t('Initial date'),
      '#type' => 'datetime',
      '#date_date_date_format' => $format,
    ];

    $form['report_range']['create_date']['create_end_date'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#title' => $this->t('Final date'),
      '#type' => 'datetime',
      '#date_label_position' => 'none',
      '#date_date_date_format' => $format,
    ];

    $form['report_range']['create_date']['button'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#type' => 'submit',
      '#value' => $this->t('Generate report'),
      '#name' => 'createDateSubmit',
    ];

    $form['report_range']['publication_date'] = [
      '#type' => 'container',
      '#title' => $this->t('consolidated by range date of fixation'),
    ];

    $form['report_range']['publication_date']['description'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#markup' => $this->t('Generate a report by range of fixation date'),
    ];

    $form['report_range']['publication_date']['publication_start_date'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#title' => $this->t('Initial date'),
      '#date_label_position' => 'none',
      '#type' => 'datetime',
      '#date_date_format' => $format,
    ];

    $form['report_range']['publication_date']['publication_end_date'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#title' => $this->t('Final date'),
      '#date_label_position' => 'none',
      '#type' => 'datetime',
      '#date_date_format' => $format,
    ];

    $form['report_range']['publication_date']['button'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#type' => 'submit',
      '#name' => 'publicationDateSubmit',
      '#value' => $this->t('Generate report'),
    ];

    $form['report_range']['unpublish_date'] = [
      '#type' => 'container',
      '#title' => $this->t('Consolidate by range date of unfixation'),
    ];

    $form['report_range']['unpublish_date']['description'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#markup' => $this->t('Generate a report by range of unfixation date'),
    ];

    $form['report_range']['unpublish_date']['unpublishing_start_date'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#title' => $this->t('Initial date'),
      '#date_label_position' => 'none',
      '#type' => 'datetime',
      '#date_date_format' => $format,
    ];

    $form['report_range']['unpublish_date']['unpublishing_end_date'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#title' => $this->t('Final date'),
      '#date_label_position' => 'none',
      '#type' => 'datetime',
      '#date_date_format' => $format,
    ];

    $form['report_range']['unpublish_date']['button'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#type' => 'submit',
      '#name' => 'unpublishDateSubmit',
      '#value' => $this->t('Generate report'),
    ];

    $form['report_range']['sim'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Consolidate by SIM number'),
    ];

    $form['report_range']['sim']['description'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#markup' => $this->t('A report is generated by SIM number.'),
    ];

    $form['report_range']['sim']['start_number'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#type' => 'textfield',
      '#title' => $this->t('Initial number'),
    ];

    $form['report_range']['sim']['end_number'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#type' => 'textfield',
      '#title' => $this->t('Final number'),
    ];

    $form['report_range']['sim']['button'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#type' => 'submit',
      '#name' => 'simSubmit',
      '#value' => $this->t('Generate report'),
    ];

    $form['report_range']['legal_action'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Consolidate by type of legal action'),
    ];

    $form['report_range']['legal_action']['description'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#markup' => $this->t('A report is generated by type of legal action'),
    ];

    $form['report_range']['legal_action']['select_action'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#type' => 'select',
      '#title' => $this->t('Legal action'),
      '#options' => [
        '' => $this->T('-- Select--'),
        'Auto de apertura' => $this->t('Auto de apertura'),
        'Audiencia' => $this->t('Audiencia'),
      ],
    ];

    $form['report_range']['legal_action']['button'] = [
      '#prefix' => '<div class="col-sm-3">',
      '#suffix' => '</div>',
      '#type' => 'submit',
      '#name' => 'legalActionSubmit',
      '#value' => $this->t('Generate report'),
    ];

    $form['#attached']['library'] = [
      'citaciones/citaciones',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the triggering element.
    $triggering_element = $form_state->getTriggeringElement();
    $query_array = [];

    switch ($triggering_element['#name']) {
      case 'createDateSubmit':
        // Generate a report by range of creation date.
        $start_date = $form_state->getValue('create_start_date');
        $end_date = $form_state->getValue('create_end_date');

        if (!empty($start_date) || !empty($end_date)) {
          $query_array = [
            'created' => [
              'date' => date("m/d/Y", strtotime($start_date)),
              'time' => '00:00:00',
            ],
            'created_1' => [
              'date' => date("m/d/Y", strtotime($end_date)),
              'time' => '23:59:59',
            ],
          ];
        }
        break;

      case 'publicationDateSubmit':
        // Generate a report by range of fixation date.
        $start_date = $form_state->getValue('publication_start_date');
        $end_date = $form_state->getValue('publication_end_date');

        if (!empty($start_date) || !empty($end_date)) {
          $query_array = [
            'field_creation_date' => [
              'date' => date("m/d/Y", strtotime($start_date)),
              'time' => '00:00:00',
            ],
            'field_creation_date_1' => [
              'date' => date("m/d/Y", strtotime($end_date)),
              'time' => '23:59:59',
            ],
          ];
        }
        break;

      case 'unpublishDateSubmit':
        // Generate a report by range of unfixation date.
        $start_date = $form_state->getValue('unpublishing_start_date');
        $end_date = $form_state->getValue('unpublishing_end_date');

        if (!empty($start_date) || !empty($end_date)) {
          $query_array = [
            'field_unpublish_date' => [
              'date' => date("m/d/Y", strtotime($start_date)),
              'time' => '00:00:00',
            ],
            'field_unpublish_date_1' => [
              'date' => date("m/d/Y", strtotime($end_date)),
              'time' => '23:59:59',
            ],
          ];
        }
        break;

      case 'simSubmit':
        // Generate a report by SIM number.
        $start_number = $form_state->getValue('start_number');
        $end_number = $form_state->getValue('end_number');

        if (!empty($start_number) || !empty($end_number)) {
          $query_array = [
            'field_sim_number' => $start_number,
            'field_sim_number_1' => $end_number,
          ];
        }
        break;

      case 'legalActionSubmit':
        // Generate a report by type of legal action.
        $legal_action = $form_state->getValue('select_action');
        if (!empty($legal_action)) {
          $query_array = [
            'field_legal_act' => $legal_action,
          ];
        }
        break;
    }

    // Add the regional office to the query array.
    $region_office = $form_state->getValue('field_region_office');
    if ($region_office) {
      $query_array['field_region_office'] = $region_office == 'label_0' ? 'All' : $region_office;
    }

    // Add the locality to the query array.
    $locality = $form_state->getValue('locality');
    if ($locality) {
      $query_array['field_lugar'] = $locality == 'label_0' ? 'All' : $locality;
    }

    // Add the officer name to the query array.
    $defendant = $form_state->getValue('defendant');
    if ($defendant) {
      $officer_name = array_shift(explode('(', $defendant));
      $query_array['field_officer_name'] = $officer_name;
    }

    $fields_to_remove = [];
    foreach ($form_state->getValue('data') as $key => $value) {
      if ($value == 0) {
        $fields_to_remove[] = $key;
      }
    }
    foreach ($form_state->getValue('data_summoned') as $key => $value) {
      if ($value == 0) {
        $fields_to_remove[] = $key;
      }
    }
    foreach ($form_state->getValue('data_child') as $key => $value) {
      if ($value == 0) {
        $fields_to_remove[] = $key;
      }
    }
    $fields_to_remove = Json::encode($fields_to_remove);

    if (!empty($query_array)) {
      $url = Url::fromUserInput("/citaciones/exportar/$fields_to_remove", [
        'query' => $query_array,
      ])->toString();
      return new RedirectResponse($url);
    }
    else {
      \Drupal::messenger()->addMessage($this->t('You should filter the search.'));
    }

  }

  /**
   * Get the options for the vocabulary.
   *
   * @param string $vid
   *   The vocabulary ID.
   *
   * @return array
   *   An array of options for the vocabulary.
   */
  public function getOptionsVocabulary($vid) {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
      ->loadTree($vid, 0, NULL, TRUE);
    $options = [];
    foreach ($terms as $term) {
      $sub_terms = $this->loadTaxonomyTermsTree($vid, $term->id());
      if (empty($sub_terms)) {
        continue;
      }

      $term_name = $term->getName();
      $options[$term_name] = $sub_terms;
    }

    return $options;
  }

  /**
   * Load taxonomy terms in a tree structure.
   *
   * @param string $vocabulary_id
   *   The vocabulary ID.
   * @param int $parent
   *   The parent term ID.
   * @param int $max_depth
   *   The maximum depth to traverse.
   *
   * @return array
   *   An array of taxonomy terms in a tree structure.
   */
  function loadTaxonomyTermsTree($vocabulary_id, $parent = 0, $max_depth = NULL) {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
      ->loadTree($vocabulary_id, $parent, $max_depth, TRUE);
    if (empty($terms)) {
      return [];
    }

    $tree = [];
    foreach ($terms as $term) {
      $tree[$term->id()] = $term->getName();
    }

    return $tree;
  }

}


