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
      '#default_value' => 'taxonomy_term',
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
    $result_terms = $this->panelizer->getPanelizerTerms();

    $form['nodes'] = [
      '#type' => 'details',
      '#title' => 'Migrar Contenidos',
      '#states' => [
        'visible' => [
          ':input[id="entity-type"]' => ['value' => 'node'],
        ],
      ],
      '#open' => TRUE,
      'summary' => [
        '#type' => 'container',
        '#prefix' => '<div>',
        '#suffix' => '</div>',
        '#markup' => $this->t('@count Contenidos', ['@count' => count($result)]),
      ],
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
      'summary' => [
        '#type' => 'container',
        '#prefix' => '<div>',
        '#suffix' => '</div>',
        '#markup' => $this->t('@count Contenidos', ['@count' => count($result_terms)]),
      ],
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
    $debug = FALSE;
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
              // Ignore the block if the node have not the field.
              if ($item->type == 'entity_field' && !$node->hasField($field_name)) {
                continue;
              }

              // Ignore the hidden blocks.
              if (!$item->shown) {
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
                      $block_result = $this->panelizer->addViewInBlock($item->subtype, $configuration);
                      $field_configuration = $block_result['block'];
                      $block_plugin_id = $field_configuration['id'];
                      $result_messages = array_merge($result_messages, $block_result['messages']);
                      break;

                    case 'block':
                      if (strpos($item->subtype, 'facetapi') === 0) {
                        $result_messages[] = $this->t('Subtype @subtype', [
                          '@subtype' => $item->subtype,
                        ]);
                      }

                      $block_result = $this->panelizer->addBlockContentInBlock($item->subtype, $configuration);
                      $field_configuration = $block_result['block'];
                      $block_plugin_id = $field_configuration['id'];
                      $result_messages = array_merge($result_messages, $block_result['messages']);
                      break;

                    case 'node':
                      $node_referenced = \Drupal::entityTypeManager()->getStorage('node')
                        ->load($configuration['nid']);
                      if ($node_referenced) {
                        $block_plugin_id = 'node:' . $configuration['nid'];
                      //   $provider = 'node';
                      //   $node_referenced_title = $node_referenced->getTitle();

                      //   $this->panelizer->createDefaultConfiguration(
                      //     $block_plugin_id,
                      //     $node_referenced_title,
                      //     $provider,
                      //     $label_display,
                      //   );
                      //   $this->panelizer->addContextMappingConfig(['entity' => 'layout_builder.entity']);
                      //   $field_configuration = $this->panelizer->block_config;
                      //   $field_configuration['links'] = $configuration['links'] ?? 1;
                      //   $field_configuration['leave_node_title'] = $configuration['leave_node_title'] ?? 0;
                      //   $field_configuration['build_mode'] = $configuration['build_mode'] ?? 'full';
                      //   $field_configuration['link_node_title'] = $configuration['link_node_title'] ?? 0;
                      //   $field_configuration['identifier'] = $configuration['identifier'] ?? 1;
                        // $field_configuration['content'] = \Drupal::entityTypeManager()->getViewBuilder('node')
                        //   ->view($node_referenced, $configuration['build_mode']);
                        $block_content_test = [
                          '#type' => 'entity',
                          '#entity_type' => 'node',
                          '#entity' => \Drupal\node\Entity\Node::load(106258),
                          '#view_mode' => 'full',
                        ];
                        $field_configuration = [
                          'id' => 'inline_block:custom',
                          'label' => 'Render nodo 106258',
                          'provider' => 'layout_builder',
                          'label_display' => false,
                          'view_mode' => 'full',
                          'content' => $block_content,
                        ];
                      }
                      else {
                        $result_messages[] = $this->t('Node with ID @nid not found.', ['@nid' => $configuration['nid']]);
                      }
                      break;

                    case 'panels_mini':
                      $panels_mini_name = $item->subtype;
                      $minipanel = $this->panelizer->getPanelsMiniData($panels_mini_name);

                      foreach ($minipanel as $panel_block) {
                        $panel_config = unserialize($panel_block->configuration);
                        switch ($panel_block->type) {
                          case 'views':
                            $block_result = $this->panelizer->AddViewInBlock($panel_block->subtype, $panel_config);
                            $field_configuration = $block_result['block'];
                            $result_messages = array_merge($result_messages, $block_result['messages']);
                            break;

                          case 'block':
                            if (strpos($item->subtype, 'facetapi') === 0) {
                              $result_messages[] = $this->t('Subtype @subtype', [
                                '@subtype' => $item->subtype,
                              ]);
                            }

                            $block_result = $this->panelizer->addBlockContentInBlock($item->subtype, $configuration);
                            $field_configuration = $block_result['block'];
                            $block_plugin_id = $field_configuration['id'];
                            $result_messages = array_merge($result_messages, $block_result['messages']);
                            break;
                        }

                        if (isset($field_configuration)) {
                          // Create the layout builder component (item/block).
                          $field_component = new SectionComponent(
                            \Drupal::service('uuid')->generate(),
                            $region,
                            $field_configuration
                          );
                          // Append the component to the section.
                          $row['section']->appendComponent($field_component);
                        }
                      }
                      break;

                    case 'custom':
                      $this->panelizer->addCustomBlock($item->subtype, $configuration, $item->name);
                      $field_configuration = $block_result['block'];
                      $block_plugin_id = $field_configuration['id'];
                      break;

                      default:
                      $result_messages[] = $this->t('Unknown type @type for panel @panel.', [
                        '@type' => $type,
                        '@panel' => $panel,
                      ]);
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
              $this->t('Node @nid @title has been migrated to Layout Builder.', ['@nid' => $node->id(), '@title' => $node->getTitle()])
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

        $terms = $this->panelizer->getPanelizerTerms(['panelizer_entity'], $tids);
        $panels_display = $this->panelizer->getPanelizerTerms(['panels_display'], $tids);
        // Creamos las secciones (filas) de Layout Builder.
        $sections = $this->panelizer->createLayoutSections($panels_display);
        foreach ($terms as $term_value) {
          $result_messages = [];
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
            $panels_id = $this->panelizer->getPanelizerTerms(['panelizer_entity', 'panels_pane'], [$term_value->entity_id]);
            foreach ($panels_id as $item) {
              $field_name = str_replace('taxonomy_term:', '', $item->subtype);
              // Ignore the block if the taxonomy have not the field.
              if ($item->type == 'entity_field' && !$term->hasField($field_name)) {
                continue;
              }

              // Ignore the hidden blocks.
              if (!$item->shown) {
                continue;
              }

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

                    case 'views':;
                      $block_result = $this->panelizer->addViewInBlock($item->subtype, $configuration);
                      $field_configuration = $block_result['block'];
                      $block_plugin_id = $field_configuration['id'];
                      $result_messages = array_merge($result_messages, $block_result['messages']);
                      break;

                    case 'block':
                      if (strpos($item->subtype, 'facetapi') === 0) {
                        $result_messages[] = $this->t('Subtype @subtype', [
                          '@subtype' => $item->subtype,
                        ]);
                      }

                      $block_result = $this->panelizer->addBlockContentInBlock($item->subtype, $configuration);
                      $field_configuration = $block_result['block'];
                      $block_plugin_id = $field_configuration['id'];
                      $result_messages = array_merge($result_messages, $block_result['messages']);
                      break;

                    case 'panels_mini':
                      $panels_mini_name = $item->subtype;
                      $minipanel = $this->panelizer->getPanelsMiniData($panels_mini_name);
                      foreach ($minipanel as $panel_block) {
                        $panel_config = unserialize($panel_block->configuration);
                        switch ($panel_block->type) {
                          case 'views':
                            $block_result = $this->panelizer->AddViewInBlock($panel_block->subtype, $panel_config);
                            $field_configuration = $block_result['block'];
                            $result_messages = array_merge($result_messages, $block_result['messages']);
                            break;

                          case 'block':
                            $block_result = $this->panelizer->addBlockContentInBlock($panel_block->subtype, $panel_config);
                            $field_configuration = $block_result['block'];
                            $result_messages = array_merge($result_messages, $block_result['messages']);
                            break;
                        }

                        if (isset($field_configuration)) {
                          // Create the layout builder component (item/block).
                          $field_component = new SectionComponent(
                            \Drupal::service('uuid')->generate(),
                            $region,
                            $field_configuration
                          );
                          // Append the component to the section.
                          $row['section']->appendComponent($field_component);
                        }
                      }
                      break;

                    case 'custom':
                      $block_result = $this->panelizer->addCustomBlock($item->subtype, $configuration);
                      $field_configuration = $block_result['block'];
                      $block_plugin_id = $field_configuration['id'];
                      break;

                    default:
                      $result_messages[] = $this->t('Unknown type @type for panel @panel.', [
                        '@type' => $type,
                        '@panel' => $panel,
                      ]);
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
                      $field_configuration,
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
        }

        break;
    }

    if ($debug || $this->panelizer->debug) {
      die();
    }
  }

}
