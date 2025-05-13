<?php

namespace Drupal\custom_solr_search\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;

class SearchController extends ControllerBase {

  public function autocomplete(Request $request) {
    $string = $request->query->get('q');
    $matches = [];

    if ($string) {
      $index = \Drupal::entityTypeManager()->getStorage('search_api_index')->load('icbf');
      $query = $index->query()->keys($string)->range(0, 5);
      $results = $query->execute();

      foreach ($results as $item) {
        try {
          // Verifica que el datasource esté disponible
          $datasource = $item->getDatasource();
          $fields = $item->getFields();

          // Puedes usar ss_url como alternativa para "título"
          $url_field = $fields['title']->getValues()[0] ?? null;

          if ($url_field) {
            $slug = basename(parse_url($url_field, PHP_URL_PATH));
            $label = ucwords(str_replace('-', ' ', $slug));
          } else {
            $label = 'Sin título';
          }

          $matches[] = ['value' => $label, 'label' => $label];
        }
        catch (\Exception $e) {
          \Drupal::logger('custom_solr_search')->error('Autocomplete error: @msg', ['@msg' => $e->getMessage()]);
          continue;
        }
      }
    }

    return new JsonResponse($matches);
  }
}

