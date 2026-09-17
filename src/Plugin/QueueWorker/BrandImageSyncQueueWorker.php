<?php

namespace Drupal\digital\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\digital\Service\BrandImageSyncService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes Brand Image Sync queue items.
 *
 * @QueueWorker(
 *   id = "brand_image_sync",
 *   title = @Translation("Brand Image Sync"),
 *   cron = {"time" = 30}
 * )
 */
class BrandImageSyncQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  protected EntityTypeManagerInterface $entityTypeManager;

  protected BrandImageSyncService $brandImageSyncService;

  protected $logger;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entityTypeManager,
    BrandImageSyncService $brandImageSyncService,
    LoggerChannelFactoryInterface $loggerFactory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
    $this->brandImageSyncService = $brandImageSyncService;
    $this->logger = $loggerFactory->get('digital');
  }

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
      $container->get('digital.brand_image_sync'),
      $container->get('logger.factory'),
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
      $this->logger->warning('Brand term @tid not found.', [
        '@tid' => $data['tid'],
      ]);
      return;
    }

    try {
      $this->brandImageSyncService->sync($term);
    }
    catch (\Throwable $e) {

      $this->logger->error(
        'Brand image sync failed for term @tid: @message',
        [
          '@tid' => $term->id(),
          '@message' => $e->getMessage(),
        ]
      );

      throw $e;
    }

  }

}
