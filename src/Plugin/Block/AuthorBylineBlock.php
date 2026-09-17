<?php

namespace Drupal\digital\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an Author Byline block.
 */
#[\Drupal\Core\Block\Attribute\Block(
  id: 'digital_author_byline',
  admin_label: new \Drupal\Core\StringTranslation\TranslatableMarkup('Author Byline'),
  category: new \Drupal\Core\StringTranslation\TranslatableMarkup('Digital')
)]
class AuthorBylineBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected DateFormatterInterface $dateFormatter;

  /**
   * Constructs the Author Byline block.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $route_match,
    DateFormatterInterface $date_formatter,
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition
    );

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
    $plugin_definition,
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('date.formatter'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->getCurrentEntity();

    if (!$entity) {
      return [];
    }

    $timestamps = $this->getEntityDates($entity);

    if (!$timestamps['created']) {
      return [];
    }

    return [
      '#theme' => 'digital_author_byline',

      '#author_name' => 'Adrian Zalewski',
      '#author_email' => 'adrian@myurbanclothing.com',
      '#author_url' => 'https://myurbanclothing.com/about/adrian-zalewski',

      '#created' => $this->dateFormatter->format(
        $timestamps['created'],
        'custom',
        'F j, Y'
      ),

      '#updated' => $this->dateFormatter->format(
        $timestamps['updated'],
        'custom',
        'F j, Y'
      ),

      '#created_timestamp' => $timestamps['created'],
      '#updated_timestamp' => $timestamps['updated'],

      '#entity_type' => $entity->getEntityTypeId(),
      '#entity_id' => $entity->id(),

      '#cache' => [
        'contexts' => [
          'route',
        ],
        'tags' => $entity->getCacheTags(),
      ],
    ];
  }

  /**
   * Gets the entity associated with the current page.
   *
   * Supports node and taxonomy term pages.
   */
  protected function getCurrentEntity(): ?EntityInterface {
    // Content/node page.
    $node = $this->routeMatch->getParameter('node');

    if ($node instanceof EntityInterface && $node->getEntityTypeId() === 'node') {
      return $node;
    }

    // Taxonomy term page.
    $term = $this->routeMatch->getParameter('taxonomy_term');

    if ($term instanceof EntityInterface && $term->getEntityTypeId() === 'taxonomy_term') {
      return $term;
    }

    return NULL;
  }


protected function getEntityDates(EntityInterface $entity): array {
  $created = 0;
  $updated = 0;

  // Nodes.
  if ($entity->getEntityTypeId() === 'node') {
    $created = (int) $entity->getCreatedTime();
    $updated = (int) $entity->getChangedTime();
  }

  // Taxonomy terms.
  elseif ($entity->getEntityTypeId() === 'taxonomy_term') {
    if ($entity->hasField('created') && !$entity->get('created')->isEmpty()) {
      $created = (int) $entity->get('created')->value;
    }

    if ($entity->hasField('changed') && !$entity->get('changed')->isEmpty()) {
      $updated = (int) $entity->get('changed')->value;
    }
  }

  return [
    'created' => $created,
    'updated' => $updated,
  ];
}


public function access(
  AccountInterface $account,
  $return_as_object = FALSE,
) {
  return $return_as_object
    ? AccessResult::allowed()
    : TRUE;
}


  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(
      parent::getCacheContexts(),
      ['route']
    );
  }

}

