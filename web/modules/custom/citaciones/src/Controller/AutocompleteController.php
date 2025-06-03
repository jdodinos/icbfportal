<?php

namespace Drupal\citaciones\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * The controller for the autocomplete functionality.
 */
class AutocompleteController extends ControllerBase {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a AutocompleteController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns a list of autocomplete suggestions.
   *
   * @param string $string
   *   The string to search for.
   *
   * @return array
   *   An array of suggestions.
   */
  public function handleAutocompleteOfficerName(Request $request) {
    $matches = [];
    $string = $request->query->get('q');
    if ($string) {
      $query = $this->entityTypeManager->getStorage('node')->getQuery();
      $query->condition('type', 'summon')
        ->accessCheck(TRUE)
        ->condition('field_officer_name', "%$string%", 'LIKE')
        ->range(0, 20);
      $nids = $query->execute();

      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
      foreach ($nodes as $node) {
        $matches[] = ['value' => $node->label(), 'label' => $node->label()];
      }
    }

    return new JsonResponse($matches);
  }

}
