<?php

namespace Drupal\icbf_migrations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Drupal\layout_builder\Plugin\SectionStorage\OverridesSectionStorage;
use Drupal\layout_builder\SectionList;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\views\Entity\View;
use Drupal\Core\Render\Markup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\icbf_migrations\PanelizerMigrationService;

/**
 * The form for migrating Panelizer.
 */
class PanelizerMigrationForm extends FormBase {
  protected $panelizer;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'panelizer_migration_form';
  }

  /**
   *
   */
  public function __construct(PanelizerMigrationService $panelizer_service) {
    $this->panelizer = $panelizer_service;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('icbf_migrations.panelizer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /**** PRUEBASSSS */
    // $block_manager = \Drupal::service('plugin.manager.block');
    // $definitions = $block_manager->getDefinitions();
    // dump(array_keys($definitions));
    /**** FIN PRUEBASSSS */


    $entity_types = $this->panelizer->getEntityTypes();
    $form['entity_type'] = [
      '#type' => 'radios',
      '#options' => $entity_types,
      '#attributes' => [
        'id' => 'entity-type',
      ],
      '#default_value' => 'node',
    ];

    // Table structure.
    $header = [
      'id' => $this->t('ID'),
      'status' => $this->t('Status'),
      'title' => $this->t('Title'),
      'entity' => $this->t('Entity Data'),
      'messages' => $this->t('Observations'),
    ];

    $result = $this->panelizer->getPanelizerNodes();
    $form['nodes'] = [
      '#type' => 'details',
      '#title' => 'Migrar Contenidos',
      '#states' => [
        'visible' => [
          ':input[id="entity-type"]' => ['value' => 'node'],
        ],
      ],
      '#open' => TRUE,
    ];

    $form['taxonomies'] = [
      '#type' => 'details',
      '#title' => 'Migrar Taxonomías',
      '#states' => [
        'visible' => [
          ':input[id="entity-type"]' => ['value' => 'taxonomy_term'],
        ],
      ],
      '#open' => TRUE,
    ];

    $this->panelizer;

    $options = [];
    foreach ($result as $item) {
      $nid = $item->nid;
      $title = $item->title;
      $type = $item->entity_type;
      $url = Url::fromRoute('entity.node.canonical', ['node' => $nid]);
      $link = Link::fromTextAndUrl($title, $url);
      $data = $this->panelizer->validateEntity($nid, 'node');
      $status = 'Pendiente';
      $messages = $this->t('No messages.');
      if ($data) {
        if ($data->status == 1) {
          $status = 'Migrado';
        }
        elseif ($data->status == 2) {
          $status = 'Con Observaciones';
          $messages = json_decode($data->data, TRUE);
          $messages = Markup::create(implode('<br>', $messages));
        }
      }

      $options[$nid] = [
        'id' => $nid,
        'status' => $status,
        'title' => $link,
        'entity' => $type,
        'messages' => $messages,
      ];
    }

    $form['nodes']['nids'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => $this->t('No nodes found'),
    ];


    $result_terms = $this->panelizer->getPanelizerTerms();
    $options_term = [];
    foreach ($result_terms as $item) {
      $term_id = $item->entity_id;
      $term_name = $item->name;
      $vocabulary = $item->vocabulary;
      $url = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term_id]);
      $link = Link::fromTextAndUrl($term_name, $url);
      $data = $this->panelizer->validateEntity($term_id, 'taxonomy_term');
      $status = 'Pendiente';
      $messages = $this->t('No messages.');
      if ($data) {
        if ($data->status == 1) {
          $status = 'Migrado';
        }
        elseif ($data->status == 2) {
          $status = 'Con Observaciones';
          $messages = json_decode($data->data, TRUE);
          $messages = Markup::create(implode('<br>', $messages));
        }
      }

      $options_term[$term_id] = [
        'id' => $term_id,
        'status' => $status,
        'title' => $link,
        'entity' => $vocabulary,
        'messages' => $messages,
      ];
    }
    $form['taxonomies']['tids'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options_term,
      '#empty' => $this->t('No users found'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Migrate'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity_type = $form_state->getValue('entity_type');

    switch ($entity_type) {
      case 'node':
        $nids = array_filter($form_state->getValue('nids'));
        if (empty($nids)) {
          \Drupal::messenger()->addError($this->t('No nodes selected for migration.'));
          return;
        }

        $nodes = $this->panelizer->getPanelizerNodes(['node'], $nids);
        $panels_display = $this->panelizer->getPanelizerNodes(['panels_display'], $nids);
        // Creamos las secciones (filas) de Layout Builder.
        $sections = $this->panelizer->createLayoutSections($panels_display);

        foreach ($nodes as $node_value) {
          $result_messages = [];
          // Ensure that Layout Builder is enabled and customized.
          $node = \Drupal::entityTypeManager()->getStorage('node')
            ->load($node_value->nid);
          if (!$node) {
            continue;
          }
          // Reset the Entity layout regions.
          $this->panelizer->deleteLayoutSectionsForEntity($node);
          $node_type = $node->getType();

          // Ensure that Layout Builder is enabled and has the field.
          if ($node->hasField('layout_builder__layout')) {
            $did = $node_value->did;
            $section_node = $sections[$did];
            foreach ($section_node as $row) {
              $node->get('layout_builder__layout')->appendItem($row['section']);
            }

            // Add new sections to the node.
            $layout_field = $node->get(OverridesSectionStorage::FIELD_NAME);
            $panels_id = $this->panelizer->getPanelizerNodes(['node', 'panels_pane'], [$node_value->nid]);
            foreach ($panels_id as $item) {
              $field_name = str_replace('node:', '', $item->subtype);
              if ($item->type == 'entity_field' && !$node->hasField($field_name)) {
                continue;
              }

              $did = $item->did;
              $panel = $item->panel;
              $type = $item->type;
              $section_node = $sections[$did];
              $configuration = unserialize($item->configuration);
              if (isset($configuration['formatter']) && $configuration['formatter'] == 'text_glazed_builder') {
                $configuration['formatter'] = 'text_default';
              }

              foreach ($section_node as &$row) {
                $field_configuration = [];
                if (isset($row['children']) && in_array($panel, $row['children'])) {
                  $block_plugin_id = NULL;
                  $provider = 'layout_builder';

                  $section_id = $row['section']->getLayoutId();
                  $position = array_search($panel, $row['children']);
                  $region = $this->panelizer->getRegionInSection($section_id, $position);

                  $label_display = FALSE;
                  if (isset($configuration['override_title']) && $configuration['override_title'] && isset($configuration['override_title_text']) && $configuration['override_title_text']) {
                    $label_display = TRUE;
                  }

                  switch ($type) {
                    case 'entity_field':
                    case 'node_body':
                      if ($type == 'node_body') {
                        $field_name = 'body';
                      }

                      if ($node->hasField($field_name)) {
                        $layout_field = 'field_block';
                        $label = $field_name;
                        $block_plugin_id = "{$layout_field}:node:{$node_type}:{$field_name}";
                      }
                      else {
                        $result_messages[] = $this->t('Field @field_name not found in node type @node_type.', [
                          '@field_name' => $field_name,
                          '@node_type' => $node_type,
                        ]);
                      }
                      break;

                    case 'node_title':
                      $this->panelizer->createDefaultConfiguration(
                        'page_title_block',
                        'Title page',
                        'core',
                        $label_display,
                      );
                      $field_configuration = $this->panelizer->block_config;
                      break;

                    case 'node_links':
                      $field_name = 'links';
                      $layout_field = 'extra_field_block';
                      $label = 'Links';
                      $block_plugin_id = "{$layout_field}:node:{$node_type}:{$field_name}";
                      break;

                    case 'views':
                      $view_id = $item->subtype;
                      $display_id = $configuration['display'];
                      $view = View::load($view_id);
                      if ($view) {
                        $displays = $view->get('display');
                        if (isset($displays[$display_id])) {
                          $layout_field = 'views_block';
                          $label = "Views {$view_id} - Display {$displays[$display_id]['display_title']} - Display ID {$display_id}";
                          $block_plugin_id = "{$layout_field}:{$view_id}-{$display_id}";
                          $provider = 'views';
                          $label = $configuration['override_title_text'] ?? $label;

                          $this->panelizer->createDefaultConfiguration(
                            $block_plugin_id,
                            $label,
                            $provider,
                            $label_display,
                          );
                          $this->panelizer->addFormatterConfig($configuration);
                          $field_configuration = $this->panelizer->block_config;

                          if (isset($configuration['args']) && !empty($configuration['args'])) {
                            // $this->panelizer->addContextMappingConfig(['entity' => 'layout_builder.entity']);
                            // dump('en views');
                            // dump($configuration['args']);
                            // dump($node);
                            $field_configuration['configuration'] = [
                              'arguments' => [8154],
                            ];
                            // $field_configuration['context_mapping'] = [
                            //   'argument_1' => 8154
                            // ];
                            dump($field_configuration);
                            $result_messages[] = $this->t('Migrating view @view_id with display @display_id, Please review.', [
                              '@view_id' => $view_id,
                              '@display_id' => $display_id,
                            ]);
                          }

                        }
                        else {
                          $result_messages[] = $this->t('Display @display_id not found in view @view_id.', [
                            '@display_id' => $display_id,
                            '@view_id' => $view_id,
                          ]);
                        }
                      }
                      else {
                        $result_messages[] = $this->t('View @view_id not found.', ['@view_id' => $view_id]);
                      }
                      break;

                    case 'block':
                      if (strpos($item->subtype, 'md_megamenu') === 0) {
                        $md_megamenus = [
                          'md_megamenu-2' => 'menu-prueba1',
                          'md_megamenu-6' => 'men-compras-locales',
                          'md_megamenu-7' => 'menu-transparencia',
                          'md_megamenu-8' => 'mgm-5c775d3a74ba0',
                          'md_megamenu-10' => 'mgm-5ce41f0ce4a05',
                          'md_megamenu-11' => 'mgm-5d3f4fe8cd42b',
                          'md_megamenu-12' => 'mgm-5da0e630d2a93',
                          'md_megamenu-15' => 'mgm-5e7a6aab9aad3',
                          'md_megamenu-16' => 'mgm-5edaba119f9a0',
                          'md_megamenu-17' => 'mgm-5edfcfafa91eb',
                          'md_megamenu-18' => 'mgm-5ee27f3730710',
                          'md_megamenu-19' => 'mgm-6038204b97dda',
                          'md_megamenu-20' => 'mgm-6049302480ef2',
                          'md_megamenu-21' => 'mfu',
                        ];
                        $provider = 'tb_megamenu';
                        $block_plugin_id = 'tb_megamenu_menu_block:' . $md_megamenus[$item->subtype] ?? $item->subtype;
                        $this->panelizer->createDefaultConfiguration(
                          $block_plugin_id,
                          '',
                          $provider,
                          $label_display,
                        );
                        $this->panelizer->addFormatterConfig($configuration);
                        $field_configuration = $this->panelizer->block_config;
                      }
                      if (strpos($item->subtype, 'facetapi') === 0) {
                        continue 2;
                      }
                      else {
                        // Caso genérico para otros bloques.
                        $position_explode = strpos($item->subtype, '-');
                        $block_type = substr($item->subtype, 0, $position_explode);
                        $block_id = substr($item->subtype, $position_explode + 1);

                        if ($block_type == 'block') {
                          $block_uuid = \Drupal::database()->select('block_content', 'b')
                            ->fields('b', ['uuid'])
                            ->condition('id', $block_id)
                            ->execute()->fetchField();
                        }
                        if ($block_type == 'bean') {
                          $block_title = str_replace('-', ' ', $block_id);
                          $block_query = \Drupal::database()->select('block_content', 'b');
                          $block_query->join('block_content_field_data', 'bcfd', 'b.id = bcfd.id');
                          $block_query->fields('b', ['uuid'])
                            ->condition('bcfd.info', $block_title);
                          $block_uuid = $block_query->execute()->fetchField();
                        }

                        if (isset($block_uuid)) {
                          $block_plugin_id = "block_content:$block_uuid";
                          $provider = 'block_content';

                          $this->panelizer->createDefaultConfiguration(
                            $block_plugin_id,
                            '',
                            $provider,
                            $label_display,
                          );
                          $this->panelizer->addFormatterConfig($configuration);
                          $this->panelizer->addViewMode('full');
                          $field_configuration = $this->panelizer->block_config;
                        }
                      }

                      if (!isset($block_plugin_id)) {
                        $block_plugin_id = $item->subtype;
                      }

                      // Verificar existencia del plugin.
                      $plugin_manager = \Drupal::service('plugin.manager.block');
                      if (!$plugin_manager->hasDefinition($block_plugin_id)) {
                        $result_messages[] = $this->t('Block plugin ID @id not found.', ['@id' => $block_plugin_id]);
                        break;
                      }
                      break;

                    case 'node':
                      $node_referenced = \Drupal::entityTypeManager()->getStorage('node')
                        ->load($configuration['nid']);
                      if ($node_referenced) {
                        $block_plugin_id = 'node:' . $configuration['nid'];
                        $provider = 'node';
                        $node_referenced_title = $node_referenced->getTitle();

                        $this->panelizer->createDefaultConfiguration(
                          $block_plugin_id,
                          $node_referenced_title,
                          $provider,
                          $label_display,
                        );
                        $this->panelizer->addContextMappingConfig(['entity' => 'layout_builder.entity']);
                        $field_configuration = $this->panelizer->block_config;
                        $field_configuration['links'] = $configuration['links'] ?? 1;
                        $field_configuration['leave_node_title'] = $configuration['leave_node_title'] ?? 0;
                        $field_configuration['build_mode'] = $configuration['build_mode'] ?? 'full';
                        $field_configuration['link_node_title'] = $configuration['link_node_title'] ?? 0;
                        $field_configuration['identifier'] = $configuration['identifier'] ?? 1;
                        $field_configuration['content'] = \Drupal::entityTypeManager()->getViewBuilder('node')
                          ->view($node_referenced, $configuration['build_mode']);
                      }
                      else {
                        $result_messages[] = $this->t('Node with ID @nid not found.', ['@nid' => $configuration['nid']]);
                      }
                      break;

                    default:
                      $result_messages[] = $this->t('Unknown type @type for panel @panel.', [
                        '@type' => $type,
                        '@panel' => $panel,
                      ]);
                      // dump('en default');
                      // dump($item);
                      // dump($configuration);
                      break;
                  }

                  if (isset($block_plugin_id)) {
                    if (empty($field_configuration)) {
                      $this->panelizer->createDefaultConfiguration(
                        $block_plugin_id,
                        $label,
                        $provider,
                        $label_display,
                      );
                      $this->panelizer->addContextMappingConfig(['entity' => 'layout_builder.entity']);
                      $this->panelizer->addFormatterConfig($configuration);
                      $field_configuration = $this->panelizer->block_config;
                    }

                    // Create the layout builder component (item/block).
                    $field_component = new SectionComponent(
                      \Drupal::service('uuid')->generate(),
                      $region,
                      $field_configuration
                    );
                    // Append the component to the section.
                    $row['section']->appendComponent($field_component);
                  }
                  break;
                }
              }
            }

            // Save the node.
            $node->save();
            $this->panelizer->insertNewLog($node->id(), 'node', $result_messages);

            // Add a message to the user.
            \Drupal::messenger()->addMessage(
              $this->t('Node @nid has been migrated to Layout Builder.', ['@nid' => $node->id()])
            );
          }
        }
        break;

      case 'taxonomy_term':
        $tids = array_filter($form_state->getValue('tids'));
        if (empty($tids)) {
          \Drupal::messenger()->addError($this->t('No terms selected for migration.'));
          return;
        }
        // dump($tids);

        $terms = $this->panelizer->getPanelizerTerms(['panelizer_entity'], $tids);
        $panels_display = $this->panelizer->getPanelizerTerms(['panels_display'], $tids);
        // Creamos las secciones (filas) de Layout Builder.
        $sections = $this->panelizer->createLayoutSections($panels_display);
        foreach ($terms as $term_value) {
          $result_messages = [];
          // dump($term_value);
          // dump($term_value->entity_id);
          // Ensure that Layout Builder is enabled and customized.
          $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
            ->load($term_value->entity_id);
          if (!$term) {
            continue;
          }

          // Reset the Entity layout regions.
          $this->panelizer->deleteLayoutSectionsForEntity($term);

          // Ensure that Layout Builder is enabled and has the field.
          if ($term->hasField('layout_builder__layout')) {
            $did = $term_value->did;
            $section_node = $sections[$did];
            foreach ($section_node as $row) {
              $term->get('layout_builder__layout')->appendItem($row['section']);
            }








            // Add new sections to the node.
            $layout_field = $term->get(OverridesSectionStorage::FIELD_NAME);
            // dump($layout_field);
            // dump($term_value);
            $panels_id = $this->panelizer->getPanelizerTerms(['panelizer_entity', 'panels_pane'], [$term_value->entity_id]);
            // dump('$panels_id');
            // dump($panels_id);
            foreach ($panels_id as $item) {
              $field_name = str_replace('taxonomy_term:', '', $item->subtype);
              if ($item->type == 'entity_field' && !$term->hasField($field_name)) {
                continue;
              }
              // dump('$item');
              // dump($item);

              $did = $item->did;
              $panel = $item->panel;
              $type = $item->type;
              $voc_machine_name = $item->voc_machine_name;
              $section_node = $sections[$did];
              $configuration = unserialize($item->configuration);
              if (isset($configuration['formatter']) && $configuration['formatter'] == 'text_glazed_builder') {
                $configuration['formatter'] = 'text_default';
              }

              foreach ($section_node as &$row) {
                $field_configuration = [];
                if (isset($row['children']) && in_array($panel, $row['children'])) {
                  $block_plugin_id = NULL;
                  $provider = 'layout_builder';

                  $section_id = $row['section']->getLayoutId();
                  $position = array_search($panel, $row['children']);
                  $region = $this->panelizer->getRegionInSection($section_id, $position);

                  $label_display = FALSE;
                  if (isset($configuration['override_title']) && $configuration['override_title'] && isset($configuration['override_title_text']) && $configuration['override_title_text']) {
                    $label_display = TRUE;
                  }
                  switch ($type) {
                    case 'entity_field':
                    case 'node_body':
                      if ($type == 'node_body') {
                        $field_name = 'body';
                      }

                      if ($term->hasField($field_name)) {
                        $layout_field = 'field_block';
                        $label = $field_name;
                        $block_plugin_id = "{$layout_field}:taxonomy_term:{$voc_machine_name}:{$field_name}";
                      }
                      else {
                        $result_messages[] = $this->t('Field @field_name not found in vocabulary type @vocabulary.', [
                          '@field_name' => $field_name,
                          '@vocabulary' => $voc_machine_name,
                        ]);
                      }
                      break;

                    case 'node_title':
                      $this->panelizer->createDefaultConfiguration(
                        'page_title_block',
                        'Title page',
                        'core',
                        $label_display,
                      );
                      $field_configuration = $this->panelizer->block_config;
                      break;

                    case 'node_links':
                      $field_name = 'links';
                      $layout_field = 'extra_field_block';
                      $label = 'Links';
                      $block_plugin_id = "{$layout_field}:taxonomy_term:{$node_type}:{$field_name}";
                      break;

                    case 'views':
                      $view_id = $item->subtype;
                      $display_id = $configuration['display'];
                      $view = View::load($view_id);
                      if ($view) {
                        $displays = $view->get('display');
                        if (isset($displays[$display_id])) {
                          $layout_field = 'views_block';
                          $label = "Views {$view_id} - Display {$displays[$display_id]['display_title']} - Display ID {$display_id}";
                          $block_plugin_id = "{$layout_field}:{$view_id}-{$display_id}";
                          $provider = 'views';
                          $label = $configuration['override_title_text'] ?? $label;

                          $this->panelizer->createDefaultConfiguration(
                            $block_plugin_id,
                            $label,
                            $provider,
                            $label_display,
                          );
                          $this->panelizer->addFormatterConfig($configuration);
                          $field_configuration = $this->panelizer->block_config;

                          if (isset($configuration['args']) && !empty($configuration['args'])) {
                            // $this->panelizer->addContextMappingConfig(['entity' => 'layout_builder.entity']);
                            // dump('en views');
                            // dump($configuration['args']);
                            $field_configuration['configuration'] = [
                              'arguments' => [8154],
                            ];
                            // $field_configuration['context_mapping'] = [
                            //   'argument_1' => 8154
                            // ];
                            dump($field_configuration);
                            $result_messages[] = $this->t('Migrating view @view_id with display @display_id, Please review.', [
                              '@view_id' => $view_id,
                              '@display_id' => $display_id,
                            ]);
                          }

                        }
                        else {
                          $result_messages[] = $this->t('Display @display_id not found in view @view_id.', [
                            '@display_id' => $display_id,
                            '@view_id' => $view_id,
                          ]);
                        }
                      }
                      else {
                        $result_messages[] = $this->t('View @view_id not found.', ['@view_id' => $view_id]);
                      }
                      break;

                    case 'block':
                      if (strpos($item->subtype, 'md_megamenu') === 0) {
                        $md_megamenus = [
                          'md_megamenu-2' => 'menu-prueba1',
                          'md_megamenu-6' => 'men-compras-locales',
                          'md_megamenu-7' => 'menu-transparencia',
                          'md_megamenu-8' => 'mgm-5c775d3a74ba0',
                          'md_megamenu-10' => 'mgm-5ce41f0ce4a05',
                          'md_megamenu-11' => 'mgm-5d3f4fe8cd42b',
                          'md_megamenu-12' => 'mgm-5da0e630d2a93',
                          'md_megamenu-15' => 'mgm-5e7a6aab9aad3',
                          'md_megamenu-16' => 'mgm-5edaba119f9a0',
                          'md_megamenu-17' => 'mgm-5edfcfafa91eb',
                          'md_megamenu-18' => 'mgm-5ee27f3730710',
                          'md_megamenu-19' => 'mgm-6038204b97dda',
                          'md_megamenu-20' => 'mgm-6049302480ef2',
                          'md_megamenu-21' => 'mfu',
                        ];
                        $provider = 'tb_megamenu';
                        $block_plugin_id = 'tb_megamenu_menu_block:' . $md_megamenus[$item->subtype] ?? $item->subtype;
                        $this->panelizer->createDefaultConfiguration(
                          $block_plugin_id,
                          '',
                          $provider,
                          $label_display,
                        );
                        $this->panelizer->addFormatterConfig($configuration);
                        $field_configuration = $this->panelizer->block_config;
                      }
                      if (strpos($item->subtype, 'facetapi') === 0) {
                        continue 2;
                      }
                      else {
                        // Caso genérico para otros bloques.
                        $position_explode = strpos($item->subtype, '-');
                        $block_type = substr($item->subtype, 0, $position_explode);
                        $block_id = substr($item->subtype, $position_explode + 1);

                        if ($block_type == 'block') {
                          $block_uuid = \Drupal::database()->select('block_content', 'b')
                            ->fields('b', ['uuid'])
                            ->condition('id', $block_id)
                            ->execute()->fetchField();
                        }
                        if ($block_type == 'bean') {
                          $block_title = str_replace('-', ' ', $block_id);
                          $block_query = \Drupal::database()->select('block_content', 'b');
                          $block_query->join('block_content_field_data', 'bcfd', 'b.id = bcfd.id');
                          $block_query->fields('b', ['uuid'])
                            ->condition('bcfd.info', $block_title);
                          $block_uuid = $block_query->execute()->fetchField();
                        }

                        if (isset($block_uuid)) {
                          $block_plugin_id = "block_content:$block_uuid";
                          $provider = 'block_content';

                          $this->panelizer->createDefaultConfiguration(
                            $block_plugin_id,
                            '',
                            $provider,
                            $label_display,
                          );
                          $this->panelizer->addFormatterConfig($configuration);
                          $this->panelizer->addViewMode('full');
                          $field_configuration = $this->panelizer->block_config;
                        }
                      }

                      if (!isset($block_plugin_id)) {
                        $block_plugin_id = $item->subtype;
                      }

                      // Verificar existencia del plugin.
                      $plugin_manager = \Drupal::service('plugin.manager.block');
                      if (!$plugin_manager->hasDefinition($block_plugin_id)) {
                        $result_messages[] = $this->t('Block plugin ID @id not found.', ['@id' => $block_plugin_id]);
                        break;
                      }
                      break;

              //       case 'node':
              //         $node_referenced = \Drupal::entityTypeManager()->getStorage('node')
              //           ->load($configuration['nid']);
              //         if ($node_referenced) {
              //           $block_plugin_id = 'node:' . $configuration['nid'];
              //           $provider = 'node';
              //           $node_referenced_title = $node_referenced->getTitle();

              //           $this->panelizer->createDefaultConfiguration(
              //             $block_plugin_id,
              //             $node_referenced_title,
              //             $provider,
              //             $label_display,
              //           );
              //           $this->panelizer->addContextMappingConfig(['entity' => 'layout_builder.entity']);
              //           $field_configuration = $this->panelizer->block_config;
              //           $field_configuration['links'] = $configuration['links'] ?? 1;
              //           $field_configuration['leave_node_title'] = $configuration['leave_node_title'] ?? 0;
              //           $field_configuration['build_mode'] = $configuration['build_mode'] ?? 'full';
              //           $field_configuration['link_node_title'] = $configuration['link_node_title'] ?? 0;
              //           $field_configuration['identifier'] = $configuration['identifier'] ?? 1;
              //           $field_configuration['content'] = \Drupal::entityTypeManager()->getViewBuilder('node')
              //             ->view($node_referenced, $configuration['build_mode']);
              //         }
              //         else {
              //           $result_messages[] = $this->t('Node with ID @nid not found.', ['@nid' => $configuration['nid']]);
              //         }
              //         break;

                    default:
                      $result_messages[] = $this->t('Unknown type @type for panel @panel.', [
                        '@type' => $type,
                        '@panel' => $panel,
                      ]);
                      // dump('en default');
                      // dump($item);
                      // dump($configuration);
                      break;
                  }

                  if (isset($block_plugin_id)) {
                    if (empty($field_configuration)) {
                      $this->panelizer->createDefaultConfiguration(
                        $block_plugin_id,
                        $label,
                        $provider,
                        $label_display,
                      );
                      $this->panelizer->addContextMappingConfig(['entity' => 'layout_builder.entity']);
                      $this->panelizer->addFormatterConfig($configuration);
                      $field_configuration = $this->panelizer->block_config;
                    }

                    // Create the layout builder component (item/block).
                    $field_component = new SectionComponent(
                      \Drupal::service('uuid')->generate(),
                      $region,
                      $field_configuration
                    );
                    // Append the component to the section.
                    $row['section']->appendComponent($field_component);
                  }
                  break;
                }
              }
            }

            // Save the node.
            $term->save();
            $this->panelizer->insertNewLog($term_value->entity_id, 'taxonomy_term', $result_messages);

            // Add a message to the user.
            \Drupal::messenger()->addMessage(
              $this->t('Term @id has been migrated to Layout Builder.', ['@id' => $term->id()])
            );






























          }
          // dump($term);
        }
        // dump($panels_display);
        // dump($sections);
        break;
    }

  }

}
