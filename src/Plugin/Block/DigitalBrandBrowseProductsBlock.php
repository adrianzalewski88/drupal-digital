<?php

namespace Drupal\digital\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[Block(
  id: "digital_brand_browse_products_block",
  admin_label: new \Drupal\Core\StringTranslation\TranslatableMarkup("Digital Brand Browse Products Button")
)]
class DigitalBrandBrowseProductsBlock extends BlockBase implements ContainerFactoryPluginInterface {

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

    if ($term->bundle() !== 'digital_brand') {
      return [];
    }

    // Hide when "No Products" is checked.
    if (
      $term->hasField('field_digital_no_products') &&
      !$term->get('field_digital_no_products')->isEmpty() &&
      (bool) $term->get('field_digital_no_products')->value
    ) {
      return [];
    }

    return [
      '#theme' => 'digital_brand_browse_products_block',
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
