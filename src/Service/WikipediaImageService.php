<?php

namespace Drupal\digital\Service;

/**
 * Resolves image URLs from Wikipedia pages.
 */
class WikipediaImageService {

  /**
   * Wikipedia API client.
   */
  protected WikipediaApiClient $apiClient;

  /**
   * Constructor.
   */
  public function __construct(
    WikipediaApiClient $apiClient,
  ) {
    $this->apiClient = $apiClient;
  }

  /**
   * Gets the original image URL for a Wikipedia page.
   *
   * @param string $wikipediaUrl
   *   Example:
   *   https://en.wikipedia.org/wiki/Apple_Inc.
   *
   * @return string|null
   *   Image URL or NULL.
   */
  public function getImageUrl(string $wikipediaUrl): ?string {

    $pageTitle = $this->extractPageTitle($wikipediaUrl);

    if (!$pageTitle) {
      return NULL;
    }

    $data = $this->apiClient->getPageData($pageTitle);

    if (empty($data['query']['pages'])) {
      return NULL;
    }

    $page = reset($data['query']['pages']);

    if (!empty($page['original']['source'])) {
      return $page['original']['source'];
    }

    return NULL;

  }

  /**
   * Extracts the page title from a Wikipedia URL.
   *
   * Example:
   * https://en.wikipedia.org/wiki/Apple_Inc.
   *
   * becomes
   *
   * Apple_Inc.
   */
  protected function extractPageTitle(string $url): ?string {

    $parts = parse_url($url);

    if (empty($parts['path'])) {
      return NULL;
    }

    if (!preg_match('#/wiki/(.+)$#', $parts['path'], $matches)) {
      return NULL;
    }

    return urldecode($matches[1]);

  }

}
