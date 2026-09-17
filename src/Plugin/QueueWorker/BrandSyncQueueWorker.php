<?php
namespace Drupal\digital\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\digital\Service\BrandSyncService;

/**
 * @QueueWorker(
 *   id = "brand_sync",
 *   title = @Translation("Brand Sync"),
 *   cron = {"time" = 300}
 * )
 */
class BrandSyncQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  protected BrandSyncService $brandSyncService;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    BrandSyncService $brandSyncService
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->brandSyncService = $brandSyncService;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('digital.brand_sync')
    );
  }

  public function processItem($data): void {

    if (!isset($data['tid'])) {
      return;
    }

    $this->brandSyncService->syncBrand((int) $data['tid']);

  }

}
