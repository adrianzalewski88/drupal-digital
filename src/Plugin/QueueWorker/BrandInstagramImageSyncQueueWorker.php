<?php

namespace Drupal\digital\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\digital\Service\BrandInstagramImageSyncService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Synchronizes Instagram profile images.
 *
 * @QueueWorker(
 *   id = "brand_instagram_image_sync",
 *   title = @Translation("Brand Instagram Image Sync"),
 *   cron = {"time" = 60}
 * )
 */
class BrandInstagramImageSyncQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Entity manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Sync service.
   */
  protected BrandInstagramImageSyncService $brandInstagramImageSyncService;

  /**
   * Logger.
   */
  protected LoggerChannelInterface $logger;

  /**
   * Constructor.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entityTypeManager,
    BrandInstagramImageSyncService $brandInstagramImageSyncService,
    LoggerChannelInterface $logger,
  ) {

    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition
    );

    $this->entityTypeManager = $entityTypeManager;
    $this->brandInstagramImageSyncService = $brandInstagramImageSyncService;
    $this->logger = $logger;

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
      $container->get('entity_type.manager'),
      $container->get('digital.brand_instagram_image_sync'),
      $container->get('logger.channel.digital'),
    );

  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data): void {

    if (empty($data['tid'])) {
      return;
    }

    $term = $this->entityTypeManager
      ->getStorage('taxonomy_term')
      ->load($data['tid']);

    if (!$term) {

      $this->logger->warning(
        'Brand term @tid not found.',
        [
          '@tid' => $data['tid'],
        ]
      );

      return;

    }

    try {

      $this->brandInstagramImageSyncService
        ->sync($term);

    }
    catch (\Throwable $e) {

      $this->logger->error(
        'Instagram image sync failed for term @tid: @message',
        [
          '@tid' => $term->id(),
          '@message' => $e->getMessage(),
        ]
      );

      throw $e;

    }

  }

}
