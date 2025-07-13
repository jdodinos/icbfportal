<?php

namespace Drupal\custom_solr_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a minimal header search block (solo input + botón).
 *
 * @Block(
 *   id = "custom_solr_minimal_search_block",
 *   admin_label = @Translation("Minimal Solr Search Block")
 * )
 */
class MinimalSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /** @var FormBuilderInterface */
  protected $formBuilder;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  public function build() {
    // Obtiene solo el MinimalSearchForm.
    return $this->formBuilder->getForm('Drupal\\custom_solr_search\\Form\\MinimalSearchForm');
  }
}

