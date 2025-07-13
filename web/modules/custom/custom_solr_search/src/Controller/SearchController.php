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
      if ($index) {
        $query = $index->query()
		       ->keys($string)->range(0, 5);
  
        $results = $query->execute();
  	
        foreach ($results as $item) {
	  $fields = $item->getFields();
          	  
	 \Drupal::logger('custom_solr_search')->notice('Autocomplete fields: @keys', [
          '@keys' => implode(', ', array_keys($fields)),
        ]);
	  // Fallback a ss_title si es más simple
          if (isset($fields['title'])) {
            $label = $fields['title']->getValues()[0];
	  }
	  elseif(isset($fields['filename'])){
	    $label = $fields['filename']->getValues()[0];
	  }
          else {
            $label = 'Sin título';
          }
  
	  $matches[] = ['value' => $label, 'label' => $label];
        }
      }
    }  
    return new JsonResponse($matches);
  }

}


