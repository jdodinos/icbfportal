<?php

namespace Drupal\icbf_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * The CategorySelectionController class.
 */
class CategorySelectionController extends ControllerBase {

  /**
   * {@inheritDoc}
   */
  public function getProcedureResponse(Request $request) {
    $options = [];
    $param = $request->query->get('cat');

    if ($param) {
      $query = \Drupal::entityQuery('taxonomy_term')
        ->accessCheck(FALSE)
        ->condition('vid', 'procedure')
        ->condition('field_category_citizen.target_id', $param);
      $tids = $query->execute();
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
        ->loadMultiple($tids);

      foreach ($terms as $term) {
        $term_id = $term->id();
        $term_name = $term->name->value;
        $options[] = [
          'id' => $term_id,
          'label' => $term_name,
          'url' => $term->toUrl()->toString(),
        ];
      }
    }

    return new JsonResponse($options);
  }

}
