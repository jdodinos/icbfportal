<?php

namespace Drupal\icbf_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Category Selection Block.
 *
 * @Block(
 *   id = "category_selection_block",
 *   admin_label = @Translation("Category selection Block")
 * )
 */
class CategorySelectionBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form Builder interface.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    return [
      '#type' => 'container',
      'form' => $this->formBuilder->getForm('Drupal\icbf_module\Form\CategorySelectionForm'),
      '#cache' => ['max-age' => 0],
    ];
  }

}
