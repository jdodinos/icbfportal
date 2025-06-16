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

/**
 * The form for migrating Panelizer.
 */
class PanelizerMigrationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'panelizer_migration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // $block_manager = \Drupal::service('plugin.manager.block');
    // $definitions = $block_manager->getDefinitions();
    // dump(array_keys($definitions));

    $result = $this->getPanelizerNodes();
    $header = [
      'nid' => $this->t('Nid'),
      'status' => $this->t('Status'),
      'title' => $this->t('Title'),
      'entity' => $this->t('Entity Data'),
      'messages' => $this->t('Observations'),
    ];
    $options = [];
    foreach ($result as $item) {
      $nid = $item->nid;
      $title = $item->title;
      $type = $item->entity_type;
      $url = Url::fromRoute('entity.node.canonical', ['node' => $nid]);
      $link = Link::fromTextAndUrl($title, $url);
      $data = $this->validateEntity($nid, 'node');
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
        'nid' => $nid,
        'status' => $status,
        'title' => $link,
        'entity' => $type,
        'messages' => $messages,
      ];
    }
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
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
    $values = $form_state->getValue('table');
    $nids = array_filter($values);
    if (empty($nids)) {
      \Drupal::messenger()->addError($this->t('No nodes selected for migration.'));
      return;
    }

    $nodes = $this->getPanelizerNodes(['node'], $nids);
    $panels_display = $this->getPanelizerNodes(['panels_display'], $nids);

    // Creamos las secciones (filas) de Layout Builder.
    $sections = [];
    foreach ($panels_display as $item) {
      $sections[$item->did] = [];
      $layout_settings = unserialize($item->layout_settings);

      if (empty($layout_settings) || !isset($layout_settings['items'])) {
        $layout_settings = [
          "items" => [
            "main" => [
              "type" => "row",
              "contains" => "column",
              "children" => ['main-row'],
              "parent" => NULL,
            ],
            "main-row" => [
              "type" => "row",
              "contains" => "region",
              "children" => ["center"],
              "parent" => 1,
            ],
            "center" => [
              "type" => "region",
              "title" => "Centrado",
              "width" => 100,
              "width_type" => "%",
              "parent" => "main-row",
            ],
          ],
        ];
      }

      $main = $layout_settings['items']['main'];
      foreach ($main['children'] as $region_name) {
        foreach ($layout_settings['items'] as $item_name => $value) {
          if ($item_name === $region_name) {
            $num_children = count($value['children']);
            switch ($num_children) {
              case 4:
                $section_type = 'layout_fourcol_section';
                break;

              case 3:
                $section_type = 'layout_threecol_section';
                break;

              case 2:
                $section_type = 'layout_twocol_section';
                break;

              default:
                $section_type = 'layout_onecol';
            }

            // Crear una sección (fila) con un layout de una columna.
            $sections[$item->did][] = [
              'children' => $value['children'],
              'section_name' => $region_name,
              'section' => new Section($section_type),
            ];
          }
        }
      }
    }

    foreach ($nodes as $node_value) {
      $result_messages = [];
      // Ensure that Layout Builder is enabled and customized.
      $node = \Drupal::entityTypeManager()->getStorage('node')
        ->load($node_value->nid);
      if (!$node) {
        continue;
      }
      $node_type = $node->getType();
      // Ensure that Layout Builder is enabled and has the field.
      if ($node->hasField('layout_builder__layout')) {
        /** @var \Drupal\layout_builder\Field\LayoutSectionItemList $layout_field */
        $layout_field = $node->get('layout_builder__layout');
        $count = count($layout_field);

        // Delete all items in the layout field.
        for ($i = $count - 1; $i >= 0; $i--) {
          $layout_field->removeItem($i);
        }
        // Save the node.
        $node->save();

        $did = $node_value->did;
        $section_node = $sections[$did];
        foreach ($section_node as $row) {
          $node->get('layout_builder__layout')->appendItem($row['section']);
        }

        // Add new sections to the node.
        $layout_field = $node->get(OverridesSectionStorage::FIELD_NAME);
        $panels_id = $this->getPanelizerNodes(['node', 'panels_pane'], [$node_value->nid]);
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
              $provider = 'layout_builder';

              $section_id = $row['section']->getLayoutId();
              switch ($section_id) {
                case 'layout_onecol':
                  $section_regions = ['content'];
                  break;

                case 'layout_twocol_section':
                  $section_regions = ['first', 'second'];
                  break;

                case 'layout_threecol_section':
                  $section_regions = [
                    'first',
                    'second',
                    'third',
                  ];
                  break;

                case 'layout_fourcol_section':
                  $section_regions = [
                    'first',
                    'second',
                    'third',
                    'fourth',
                  ];
                  break;
              }

              $position = array_search($panel, $row['children']);
              $region = $section_regions[$position];
              $block_plugin_id = NULL;
              $label_display = FALSE;
              if ($configuration['override_title'] && $configuration['override_title_text']) {
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
                  $block_plugin_id = 'page_title_block';
                  $field_configuration = [
                    'id' => $block_plugin_id,
                    'label' => 'Title page',
                    'provider' => 'core',
                    'label_display' => $label_display,
                    'configuration' => [
                      'id' => 'page_title_block',
                      'label' => 'Title page',
                      'provider' => 'core',
                    ],
                  ];
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

                      $field_configuration = [
                        'id' => $block_plugin_id,
                        'label' => $configuration['override_title_text'] ?? $label,
                        'provider' => $provider,
                        'label_display' => $label_display,
                        'formatter' => [
                          'override_title' => $configuration['override_title'],
                          'override_title_text' => $configuration['override_title_text'],
                          'override_title_heading' => $configuration['override_title_heading'],
                        ],
                      ];
                      dump($configuration);
                      dump($item);


                      if (isset($configuration['args']) && !empty($configuration['args'])) {
                        dump('en views');
                        dump($configuration['args']);
                        dump($node);
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
                    $field_configuration = [
                      'id' => $block_plugin_id,
                      'provider' => $provider,
                    ];
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

                      $field_configuration = [
                        'id' => $block_plugin_id,
                        'label' => '',
                        'provider' => 'block_content',
                        'label_display' => $label_display,
                        'context_mapping' => [],
                        'formatter' => [
                          'override_title' => $configuration['override_title'],
                          'override_title_text' => $configuration['override_title_text'],
                          'override_title_heading' => $configuration['override_title_heading'],
                        ],
                        'view_mode' => 'full',
                      ];
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

                  // $field_configuration = [
                  //   'id' => $block_plugin_id,
                  //   'label' => $label,
                  //   'provider' => $provider,
                  // ];
                  break;

                case 'node':
                  $configuration['nid'] = 303142;
                  $configuration['build_mode'] = 'full';
                  $node_referenced = \Drupal::entityTypeManager()->getStorage('node')
                    ->load($configuration['nid']);
                  if ($node_referenced) {
                    $block_plugin_id = 'node:' . $configuration['nid'];
                    $field_configuration = [
                      'id' => $block_plugin_id,
                      'label' => $node_referenced->getTitle(),
                      'label_display' => $label_display,
                      'provider' => 'node',
                      'links' => $configuration['links'] ?? 1,
                      'leave_node_title' => $configuration['leave_node_title'] ?? 0,
                      'build_mode' => $configuration['build_mode'] ?? 'full',
                      'link_node_title' => $configuration['link_node_title'] ?? 0,
                      'identifier' => $configuration['identifier'] ?? '',
                      'content' => \Drupal::entityTypeManager()->getViewBuilder('node')
                        ->view($node_referenced, $configuration['build_mode']),
                      'context_mapping' => [
                        'entity' => 'layout_builder.entity',
                      ],
                    ];
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
                  dump('en default');
                  dump($item);
                  dump($configuration);
                  break;
              }

              if (isset($block_plugin_id)) {
                if (empty($field_configuration)) {
                  $field_configuration = [
                    'id' => $block_plugin_id,
                    'label' => $label,
                    'provider' => $provider,
                    'label_display' => $label_display,
                    'context_mapping' => [
                      'entity' => 'layout_builder.entity',
                    ],
                    'formatter' => $configuration,
                  ];
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

        // Save the record to have information about the migration.
        $result = \Drupal::database()->select('panelizer_migration', 'pm')
          ->fields('pm', ['id'])
          ->condition('entity_id', $node_value->nid)
          ->condition('entity_type', 'node')
          ->execute()
          ->fetchField();
        $fields = [
          'entity_id' => $node_value->nid,
          'entity_type' => 'node',
          'status' => 1,
        ];
        if (!empty($result_messages)) {
          // Status 2 indicates that there were messages.
          $fields['status'] = 2;
          $fields['data'] = json_encode($result_messages);
        }
        if ($result) {
          // Update the existing record.
          \Drupal::database()->update('panelizer_migration')
            ->fields($fields)
            ->condition('id', $result)
            ->execute();
        }
        else {
          // Insert a new record.
          \Drupal::database()->insert('panelizer_migration')
            ->fields($fields)
            ->execute();
        }

        // Add a message to the user.
        \Drupal::messenger()->addMessage(
          $this->t('Node @nid has been migrated to Layout Builder.', ['@nid' => $node->id()])
        );
      }
    }

    // die();
  }

  /**
   * Fetches Panelizer nodes from the database.
   *
   * @param array $nids
   *   An optional array of node IDs to filter the results.
   *
   * @return \Drupal\Core\Database\StatementInterface
   *   The database query result.
   */
  private function getPanelizerNodes($table_to_return = ['node'], $nids = []) {
    $connection = Database::getConnection('default', 'migrate');
    $query = $connection->select('node', 'n');
    $query->innerJoin('panelizer_entity', 'pe', 'n.vid = pe.revision_id');
    $query->innerJoin('panels_display', 'pd', 'pe.did = pd.did');
    $query->condition('pe.did', 0, '<>');
    $query->condition('pd.layout', 'onecol_page', '<>');
    if (!empty($nids)) {
      $query->condition('n.nid', $nids, 'IN');
    }

    if (in_array('node', $table_to_return)) {
      $query->fields('n', ['nid', 'vid', 'title']);
      $query->addField('n', 'type', 'entity_type');
      $query->fields('pe', ['did']);
      $query->orderBy('n.nid', 'ASC');
    }
    if (in_array('panelizer_entity', $table_to_return)) {
      $query->fields('pe');
    }
    if (in_array('panels_display', $table_to_return)) {
      $query->fields('pd');
    }
    if (in_array('panels_pane', $table_to_return)) {
      $query->innerJoin('panels_pane', 'pp', 'pe.did = pp.did');
      $query->fields('pp');
      $query->orderBy('pp.position', 'ASC');
      $query->orderBy('pp.panel', 'ASC');
    }

    return $query->execute()->fetchAll();
  }

  /**
   * Validates the entity before migration.
   *
   * @param int $entity_id
   *   The entity ID to validate.
   * @param string $entity_type
   *   The entity type (e.g., node, user).
   *
   * @return bool
   *   TRUE if the entity is valid for migration, FALSE otherwise.
   */
  private function validateEntity($entity_id, $entity_type) {
    $query = \Drupal::database()->select('panelizer_migration', 'pm')
      ->fields('pm')
      ->condition('entity_id', $entity_id)
      ->condition('entity_type', $entity_type)
      ->execute()
      ->fetchObject();

    return $query ?? FALSE;
  }

}
