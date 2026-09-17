<?php

namespace Drupal\digital\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Synchronizes Instagram profile images for digital brands.
 */
class BrandInstagramImageSyncService {

  /**
   * Entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Instagram profile image service.
   */
  protected InstagramProfileImageService $instagramProfileImageService;

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
    InstagramProfileImageService $instagramProfileImageService,
    RemoteImageDownloader $remoteImageDownloader,
    LoggerChannelInterface $logger,
  ) {

    $this->entityTypeManager = $entityTypeManager;
    $this->instagramProfileImageService = $instagramProfileImageService;
    $this->remoteImageDownloader = $remoteImageDownloader;
    $this->logger = $logger;

  }

  /**
   * Synchronize an Instagram profile image.
   */
  public function sync(TermInterface $term): bool {

    // Only process digital_brand terms.
    if ($term->bundle() !== 'digital_brand') {
      return FALSE;
    }

    // Skip terms without an Instagram URL.
    if ($term->get('field_digital_instagram_url')->isEmpty()) {
      return FALSE;
    }

    $instagramUrl = $term
      ->get('field_digital_instagram_url')
      ->uri;

    $imageUrl = $this->instagramProfileImageService
      ->getProfileImageUrl($instagramUrl);

    if (!$imageUrl) {

      $this->logger->warning(
        'No Instagram profile image found for term @tid.',
        [
          '@tid' => $term->id(),
        ]
      );

      return FALSE;

    }

    $file = $this->remoteImageDownloader
      ->download($imageUrl);

    if (!$file) {

      $this->logger->warning(
        'Unable to download Instagram profile image for term @tid.',
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
      'Instagram profile image synchronized for term @tid.',
      [
        '@tid' => $term->id(),
      ]
    );

    return TRUE;

  }

}
