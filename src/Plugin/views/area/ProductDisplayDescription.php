<?php

declare(strict_types=1);

namespace Drupal\digital\Plugin\views\area;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\views\Attribute\ViewsArea;
use Drupal\views\Plugin\views\area\AreaPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Views area for the Product Display Description.
 */
#[ViewsArea('product_display_description')]
class ProductDisplayDescription extends AreaPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match service.
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * Constructs a ProductDisplayDescription area plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $route_match,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE): array {
    // Try to get the current node.
    $entity = $this->routeMatch->getParameter('node');

    // If there is no node, try to get the current taxonomy term.
    if (!$entity instanceof NodeInterface) {
      $entity = $this->routeMatch->getParameter('taxonomy_term');
    }

    // Make sure we have either a node or taxonomy term.
    if (
      !$entity instanceof NodeInterface &&
      !$entity instanceof TermInterface
    ) {
      return [];
    }

    // Make sure the entity has the field.
    if (!$entity->hasField('field_product_display_desc')) {
      return [];
    }

    // Get the field.
    $field = $entity->get('field_product_display_desc');

    // Do not render anything if the field is empty.
    if ($field->isEmpty()) {
      return [];
    }

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'text-center',
          'mb-4',
          'digital-description',
        ],
      ],
      'content' => [
        '#type' => 'processed_text',
        '#text' => $field->value,
        '#format' => $field->format,
      ],
    ];
  }

}
