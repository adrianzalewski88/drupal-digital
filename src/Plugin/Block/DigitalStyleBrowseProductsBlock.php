<?php

namespace Drupal\digital\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[Block(
  id: "digital_style_browse_products_block",
  admin_label: new \Drupal\Core\StringTranslation\TranslatableMarkup("Digital Style Browse Products Button")
)]
class DigitalStyleBrowseProductsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * Constructor.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $route_match
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
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
  public function build(): array {

    $term = $this->routeMatch->getParameter('taxonomy_term');

    if (!$term instanceof TermInterface) {
      return [];
    }

    if ($term->bundle() !== 'digital_styles') {
      return [];
    }

    return [
      '#theme' => 'digital_style_browse_products_block',
      '#term_name' => $term->label(),
      '#cache' => [
        'contexts' => [
          'route',
        ],
        'tags' => $term->getCacheTags(),
      ],
    ];
  }

}
