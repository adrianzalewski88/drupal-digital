<?php

namespace Drupal\digital\Service;

/**
 * Retrieves Instagram profile image URLs.
 */
class InstagramProfileImageService {

  /**
   * Instagram API client.
   */
  protected InstagramApiClient $instagramApiClient;

  /**
   * Constructor.
   */
  public function __construct(
    InstagramApiClient $instagramApiClient,
  ) {
    $this->instagramApiClient = $instagramApiClient;
  }

  /**
   * Returns the profile image URL for an Instagram profile.
   *
   * @param string $instagramUrl
   *   The URL stored in field_digital_instagram_url.
   *
   * @return string|null
   *   Remote image URL or NULL.
   */
  public function getProfileImageUrl(string $instagramUrl): ?string {

    $instagramUrl = trim($instagramUrl);

    if ($instagramUrl === '') {
      return NULL;
    }

    // Drupal Link fields may contain internal:/ URLs.
    if (!str_starts_with($instagramUrl, 'http')) {
      return NULL;
    }

    // Normalize URL.
    $instagramUrl = rtrim($instagramUrl, '/') . '/';

    return $this->instagramApiClient
      ->getProfileImageUrl($instagramUrl);

  }

}
