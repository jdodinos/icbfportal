<?php

namespace Drupal\custom_solr_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Url;

/**
 * Provides the advanced search form for the Custom Solr Search module.
 */
class AdvancedSearchForm extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return "custom_solr_advanced_search_form";
    }

    /**
     * Builds the search form.
     *
     * @param array $form
     *   The form structure.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   The form state.
     *
     * @return array
     *   The rendered form.
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        // Attach library and CSS class.
        $form["#attached"]["library"][] = "custom_solr_search/global";
        $form["#attributes"]["class"][] = "custom-search-form";

        // Ensure form uses GET and points to the search page route.
        $form["#method"] = "get";
        $form["#action"] = Url::fromRoute(
            "custom_solr_search.search_page"
        )->toString();

        // Get current request and query parameters.
        $request = Request::createFromGlobals();

        // Search input.
        $form["search"] = [
            "#type" => "textfield",
            "#name" => "search",
            "#title" => $this->t("Buscar"),
            "#attributes" => [
                "placeholder" => $this->t("Escribe para buscar..."),
                "autocomplete" => "off",
	    ],
	    '#autocomplete_route_name' => 'custom_solr_search.autocomplete',
 		 // Si tu ruta necesita parámetros, los pones aquí:
	    '#autocomplete_route_parameters' => [],
	    '#attributes' => [
	      'placeholder' => $this->t('Escribe para buscar...'),
	      'autocomplete' => 'off',
	    ],
            "#default_value" => $request->query->get("search", ""),
        ];
	// Submit button.
        $form["submit"] = [
		"#type" => "submit",
		"#id" => "botonBuscar",
            "#value" => $this->t("🔍"),
        ];


        // Toggle advanced filters.
        $form["toggle_advanced"] = [
		"#type" => "button",
		"#id" => "filtro_avanzado",
            "#value" => $this->t("Búsqueda avanzada"),
            "#attributes" => [
                "onclick" =>
                    'jQuery(".advanced-filters").toggle(); return false;',
                "class" => ["toggle-advanced"],
            ],
        ];

        // Advanced filters container.
        $form["advanced"] = [
            "#type" => "container",
            "#attributes" => ["class" => ["advanced-filters"],"style"=>["display:none"]],
        ];
	$form['advanced']['close'] = [
    	'#type' => 'markup',
    	'#markup' => '<button type="button" class="close-advanced" title="' . $this->t('Cerrar filtros') . '">✖</button><br><small>Cerrar</small>',
    	// Sin wrappers extra
    	'#prefix'     => '<div class="advanced-close-wrapper">',
	'#suffix'     => '</div>',
	"#attributes" => [
                "onclick" =>
                    'jQuery(".advanced-filters").toggle(); return false;'
            ],

  	];
        // Author filter.
        $users = \Drupal::entityTypeManager()
            ->getStorage("user")
            ->loadMultiple();
        $author_options = ["" => "- Seleccione -"];
        foreach ($users as $user) {
            if ($user->id() != 0) {
                $author_options[$user->id()] = $user->getDisplayName();
            }
        }
        $placeholder = ["" => $author_options[""]];
        unset($author_options[""]);
        asort($author_options, SORT_FLAG_CASE | SORT_NATURAL);
        $author_options = $placeholder + $author_options;
        $form["advanced"]["author"] = [
            "#type" => "select",
            "#name" => "author",
            "#title" => $this->t("Autor"),
            "#options" => $author_options,
            "#default_value" => $request->query->get("author", ""),
            "#ajax" => [
                "callback" => "::updateTopicOptions",
                "wrapper" => "topic-wrapper",
            ],
        ];
        $author_value = $form_state->getValue("author");
        if ($author_value === null) {
            // Primera carga o recarga normal: miro GET
            $author_value = $request->query->get("author", "");
        }

        // Topic filter.
        $form["advanced"]["topic_wrapper"] = [
            "#type" => "container",
            "#attributes" => ["id" => "topic-wrapper"],
        ];
        $form["advanced"]["topic_wrapper"]["topic"] = [
            "#type" => "select",
            "#title" => $this->t("Tema"),
            "#options" => $this->getTopicOptions($author_value),
            "#default_value" => $request->query->get("topic", ""),
        ];
        // File type filter.
        $file_type_options = [
            "" => "- Seleccione -",
            "application/pdf" => $this->t("PDF"),
            "application/msword" => $this->t("Word (.doc)"),
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => $this->t(
                "Word (.docx)"
            ),
            "application/vnd.ms-excel" => $this->t("Excel (.xls)"),
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => $this->t(
                "Excel (.xlsx)"
            ),
            "text/html" => $this->t("HTML"),
	    "application/xml" => $this->t("XML"),
	    "image/png" => $this->t("PNG"),
            "contenido" => $this->t("Contenido"),
        ];

        $form["advanced"]["file_type"] = [
            "#type" => "select",
            "#name" => "filemime",
            "#title" => $this->t("Tipo de archivo"),
            "#options" => $file_type_options,
            "#default_value" => $request->query->get("filemime", ""),
        ];

        // Filename filter.
        $form["advanced"]["filename"] = [
            "#type" => "textfield",
            "#name" => "filename",
            "#title" => $this->t("Nombre del archivo"),
            "#default_value" => $request->query->get("filename", ""),
        ];

        // Publication date filter.
        $form["advanced"]["date"] = [
            "#type" => "date",
            "#name" => "date",
            "#title" => $this->t("Fecha de publicación"),
            "#default_value" => $request->query->get("date", ""),
        ];

       // Submit button.
        // Contenedor para acciones extra en la sección avanzada.
$form['advanced']['actions'] = [
  '#type' => 'container',
  '#attributes' => ['class' => ['advanced-filters-actions']],
];

// Botón “Limpiar filtros” que borra todos los inputs y resetea la búsqueda.
$form['advanced']['actions']['clear'] = [
  '#type' => 'button',
  '#value' => $this->t('Limpiar filtros'),
  '#attributes' => [
    'class' => ['button', 'button--secondary', 'clear-filters'],
    // Con JS: vacía inputs y submits el form (GET sin parámetros).
    'onclick' => <<<EOT
      (function(){
        var \$form = jQuery(this).closest('form');
        \$form.find('input[type=text], select, input[type=date]').val('');
        return false;
      })();
    EOT,
  ],
];
 
	$form["advanced"]["actions"]["submit"] = [
                "#type" => "submit",
                "#id" => "botonBuscarAdvanced",
            "#value" => $this->t("Buscar"),
        ];

        // Render results if any search parameter is present.
        if (count($request->query->all()) > 0) {
            $form["results"] = $this->buildResults($request);
        }
        $form["#cache"]["max-age"] = 0;
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // No-op: GET submission handles reload.
    }
    /**
     * Callback AJAX para recargar solo el select de temas.
     */
    public function updateTopicOptions(
        array &$form,
        FormStateInterface $form_state
    ) {
        return $form["advanced"]["topic_wrapper"];
    }

    /**
     * Genera el array de opciones de tema, filtrando por autor si viene.
     */
    private function getTopicOptions($author_id)
    {
        $options = ['' => '- Seleccione -'];
  // Cargo todos los temas si no hay autor
  if (empty($author_id)) {
    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('section_tag'); // o el vocab correct0
    foreach ($terms as $t) {
      $options[$t->tid] = $t->name;
    }
    return $options;
  }

  // 2) Petición HTTP a Solr para facet “topic” filtrando por author.
  $solr_url = 'http://localhost:8983/solr/icbf/select';
  try {
    $query = [
      'q'           => 'its_author:' . $author_id,
      'rows'        => 0,
      'wt'          => 'json',
      'facet'       => 'true',
      'facet.field' => 'its_topic',
      'facet.limit' => -1,
    ];
    // Loguea la URL completa que vas a llamar.
    \Drupal::logger('custom_solr_search')->notice('Calling Solr: @url?@qs', [
      '@url' => $solr_url,
      '@qs'  => http_build_query($query),
    ]);

    $response = \Drupal::httpClient()->get($solr_url, ['query' => $query]);
    $status   = $response->getStatusCode();
    $body     = (string) $response->getBody();

    // Loguea el código HTTP y el JSON crudo.
    \Drupal::logger('custom_solr_search')->notice('Solr response status: @code', [
      '@code' => $status,
    ]);
    \Drupal::logger('custom_solr_search')->notice('Solr response body: @body', [
      '@body' => $body,
    ]);
  }
  catch (RequestException $e) {
    \Drupal::logger('custom_solr_search')->error('Error fetching Solr facets: @msg', [
      '@msg' => $e->getMessage(),
    ]);
    return $options;
  }

  $data = json_decode($body, TRUE);
  $flat = $data['facet_counts']['facet_fields']['its_topic'] ?? [];
  // flat = [ "6162", count, "6163", count, ... ]
  
  \Drupal::logger('custom_solr_search')->notice('Data retornada: @msg', [
      '@msg' => $flat,
    ]);

  
  $tids = [];
  for ($i = 0; $i < count($flat); $i += 2) {
    $tids[] = (int) $flat[$i];
  }
  if (empty($tids)) {
    return $options;
  }

  // 3) Cargar solo esos términos y armar el select
  $terms = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term')
    ->loadMultiple($tids);
  foreach ($terms as $term) {
    $options[$term->id()] = $term->getName();
  }

  return $options;
    }
    /**
     * Builds the search results render array.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The current request object.
     *
     * @return array
     *   A render array containing results and pager.
     */
    protected function buildResults(Request $request)
    {
        $keywords = $request->query->get("search", "");
        $author = $request->query->get("author", "");
        $topic = $request->query->get("topic", "");
        $file_type = $request->query->get("filemime", "");
        $filename = $request->query->get("filename", "");
        $date = $request->query->get("date", "");

        // Pagination.
        $current_page = \Drupal::service("pager.parameters")->findPage();
        $limit = 10;
        $offset = $current_page * $limit;
        \Drupal::logger("custom_solr_search")->notice(
            "buildResults filtering by topic = @t",
            [
                "@t" => $topic,
            ]
        );
        // Load index.
        $index = \Drupal::entityTypeManager()
            ->getStorage("search_api_index")
            ->load("icbf");
        if (!$index) {
            return ["#markup" => $this->t("Índice no disponible")];
        }

        $query = $index
            ->query()
            ->keys($keywords)
            ->range($offset, $limit)
            ->addTag("skip_access_check");
        if ($author) {
            // Creamos un grupo OR
            $or = $query->createConditionGroup("OR");
            // Filtrar nodos por el campo author
            $or->addCondition("author", $author);
            // Filtrar archivos por el campo author_1
            $or->addCondition("author_1", $author);
            // Lo agregamos a la query
            $query->addConditionGroup($or);
        }
        if ($topic) {
            $query->addCondition("topic", $topic);
        }

        if ($filename) {
            $query->addCondition("filename", $filename, "CONTAINS");
        }
        if ($file_type === "contenido") {
            $query->addCondition("entity_type", "node");
        } elseif ($file_type) {
            $query->addCondition("filemime", $file_type);
        }
        if ($date) {
            $start_of_day = strtotime($date . " 00:00:00");
            $end_of_day = strtotime($date . " 23:59:59");

            // 2) Crear un grupo AND para el rango:
            $date_group = $query->createConditionGroup("AND");
            // Para archivos (campo 'created' es timestamp)
            $date_group->addCondition("created", $start_of_day, ">=");
            $date_group->addCondition("created", $end_of_day, "<=");
            $query->addConditionGroup($date_group);
        }

        $results = $query->execute();

        // Count for pager.
        $count_query = $index
            ->query()
            ->keys($keywords)
            ->addTag("skip_access_check");
        //if ($author)    { $count_query->addCondition('author', $author); }
        if ($author) {
            $or_count = $count_query->createConditionGroup("OR");
            $or_count->addCondition("author", $author);
            $or_count->addCondition("author_1", $author);
            $count_query->addConditionGroup($or_count);
        }
        if ($topic) {
            $count_query->addCondition("topic", $topic);
        }
        if ($file_type === "contenido") {
            $count_query->addCondition("entity_type", "node");
        } elseif ($file_type) {
            $count_query->addCondition("filemime", $file_type);
        }

        if ($filename) {
            $count_query->addCondition("filename", $filename, "CONTAINS");
        }
        if ($date) {
            $count_date_group = $count_query->createConditionGroup("AND");
            $count_date_group->addCondition("created", $start_of_day, ">=");
            $count_date_group->addCondition("created", $end_of_day, "<=");
            $count_query->addConditionGroup($count_date_group);
        }
        $total_results = $count_query->execute()->getResultCount();

        \Drupal::service("pager.manager")->createPager($total_results, $limit);
        foreach ($results as $item) {
            $fields = $item->getFields();
            // Compruebo primero si existe el datasource.
            $datasource = isset($fields["entity_type"])
                ? $fields["entity_type"]->getValues()[0]
                : "";

            if ($datasource === "file") {
                // Campos de archivo: filename, uri, filemime…
                $label = isset($fields["filename"])
                    ? $fields["filename"]->getValues()[0]
                    : "";
                $file_url_generator = \Drupal::service("file_url_generator");
                $uri = isset($fields["uri"])
                    ? $fields["uri"]->getValues()[0]
                    : "";
                $url = $uri
                    ? $file_url_generator->generateAbsoluteString($uri)
                    : "";
                $mime = isset($fields["filemime"])
                    ? $fields["filemime"]->getValues()[0]
                    : "";
                $snippet = $this->t("Archivo MIME: @mime", ["@mime" => $mime]);
                $dump = json_encode(
                    array_map(function ($field) {
                        return $field->getValues();
                    }, $fields),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                );
                $rendered[] = [
                    "title" => $label,
                    "uri" => $url,
                    "snippet" => $snippet,
                    "file_type" => $mime,
                ];
            } else {
                // Resultado de nodo u otro tipo: chequeo campos de nodo.
                $label = isset($fields["title"])
                    ? $fields["title"]->getValues()[0]
                    : "";
                $url = isset($fields["url"])
                    ? $fields["url"]->getValues()[0]
                    : "";
                $snippet = isset($fields["search_api_excerpt"])
                    ? $fields["search_api_excerpt"]->getValues()[0]
                    : "";
                // Si quieres mostrar file_type para nodos también:
                $ftype = isset($fields["filemime"])
                    ? $fields["filemime"]->getValues()[0]
                    : "Contenido";
                $dump = json_encode(
                    array_map(function ($field) {
                        return $field->getValues();
                    }, $fields),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                );

                $rendered[] = [
                    "title" => $label,
                    "uri" => $url,
                    "snippet" => $snippet,
                    "file_type" => $ftype,
                ];
            }
        }
        return [
            "#theme" => "search_results",
            "#results" => $rendered,
            "#total_results" => $total_results,
            "#pager" => ["#type" => "pager"],
            "#cache" => ["max-age" => 0],
        ];
    }
}


