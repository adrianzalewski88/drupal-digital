<?php

namespace Drupal\digital\Service;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Client for retrieving Instagram profile information.
 *
 * This class is intentionally designed as an abstraction layer so the
 * underlying implementation can be replaced without affecting callers.
 */
class InstagramApiClient {

  /**
   * HTTP client.
   */
  protected ClientInterface $httpClient;

  /**
   * Logger.
   */
  protected LoggerInterface $logger;

  /**
   * Constructor.
   */
  public function __construct(
    ClientInterface $httpClient,
    LoggerInterface $logger,
  ) {
    $this->httpClient = $httpClient;
    $this->logger = $logger;
  }

  /**
   * Returns an Instagram profile image URL.
   *
   * @param string $instagramUrl
   *   A URL such as:
   *   https://www.instagram.com/nike/
   *
   * @return string|null
   *   The remote profile image URL or NULL if unavailable.
   */
  public function getProfileImageUrl(string $instagramUrl): ?string {

    $this->logger->warning(
      'InstagramApiClient::getProfileImageUrl() has not been implemented. URL: @url',
      [
        '@url' => $instagramUrl,
      ]
    );

    return NULL;

  }

}
