<?php

namespace Drupal\digital\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an Author Bio block.
 *
 * @Block(
 *   id = "digital_author_bio_block",
 *   admin_label = @Translation("Digital Author Bio"),
 *   category = @Translation("Digital")
 * )
 */
class AuthorBioBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * Date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected DateFormatterInterface $dateFormatter;

  /**
   * Author constants.
   */
  private const AUTHOR_NAME = 'Adrian Zalewski';

  private const AUTHOR_TITLE = 'Senior Full Stack PHP Developer';

  private const AUTHOR_URL = 'https://myurbanclothing.com/about/adrian-zalewski';

  private const AUTHOR_IMAGE = 'https://myurbanclothing.com/sites/default/files/styles/digital_brand_logo/public/2026-08/fb-profile-pic.jpg.webp';

  private const AUTHOR_BIO = 'Founder and Editor of My Urban Clothing, Adrian Zalewski combines 15+ years of web development experience with a passion for urban fashion, streetwear, skate culture, and independent brands to help readers discover what’s next.';

  /**
   * Constructs the block.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $route_match,
    DateFormatterInterface $date_formatter
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {

    $node = $this->routeMatch->getParameter('node');

    if (!$node instanceof NodeInterface) {
      return [];
    }

    $created = $node->getCreatedTime();
    $changed = $node->getChangedTime();

return [
  '#theme' => 'digital_author_bio_block',
  '#node' => $node,
  '#author' => [
    'name' => self::AUTHOR_NAME,
    'title' => self::AUTHOR_TITLE,
    'url' => self::AUTHOR_URL,
    'image' => self::AUTHOR_IMAGE,
    'bio' => self::AUTHOR_BIO,
  ],
  '#published' => $this->dateFormatter->format($created, 'custom', 'F j, Y'),
  '#updated' => $this->dateFormatter->format($changed, 'custom', 'F j, Y'),
  '#published_iso' => date(DATE_ATOM, $created),
  '#updated_iso' => date(DATE_ATOM, $changed),
  '#cache' => [
    'contexts' => ['route'],
    'tags' => $node->getCacheTags(),
  ],
];


  }

}
