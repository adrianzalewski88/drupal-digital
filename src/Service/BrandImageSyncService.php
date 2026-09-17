<?php

namespace Drupal\digital\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Synchronizes a brand image from Wikipedia.
 */
class BrandImageSyncService {

  /**
   * Entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Wikipedia image service.
   */
  protected WikipediaImageService $wikipediaImageService;

  /**
   * Remote image downloader.
   */
  protected RemoteImageDownloader $remoteImageDownloader;

  /**
   * Logger.
   */
  protected LoggerChannelInterface $logger;

  /**
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    WikipediaImageService $wikipediaImageService,
    RemoteImageDownloader $remoteImageDownloader,
    LoggerChannelInterface $logger,
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->wikipediaImageService = $wikipediaImageService;
    $this->remoteImageDownloader = $remoteImageDownloader;
    $this->logger = $logger;
  }

  /**
   * Synchronize a brand image.
   */
  public function sync(TermInterface $term): bool {

    // Only process digital_brand vocabulary.
    if ($term->bundle() !== 'digital_brand') {
      return FALSE;
    }

    // Skip if no Wikipedia URL.
    if ($term->get('field_wikipedia_url')->isEmpty()) {
      return FALSE;
    }

    $wikipediaUrl = $term->get('field_wikipedia_url')->uri;

    $imageUrl = $this->wikipediaImageService->getImageUrl($wikipediaUrl);

    if (!$imageUrl) {

      $this->logger->warning(
        'No Wikipedia image found for term @tid.',
        [
          '@tid' => $term->id(),
        ]
      );

      return FALSE;
    }

    $file = $this->remoteImageDownloader->download($imageUrl);

    if (!$file) {

      $this->logger->warning(
        'Unable to download image for term @tid.',
        [
          '@tid' => $term->id(),
        ]
      );

      return FALSE;
    }

    $term->set('field_digital_image', [
      'target_id' => $file->id(),
      'alt' => $term->label(),
      'title' => $term->label(),
    ]);

    $term->save();

    $this->logger->notice(
      'Brand image synchronized for term @tid.',
      [
        '@tid' => $term->id(),
      ]
    );

    return TRUE;

  }

}
