<?php

namespace Drupal\digital\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Client for interacting with the Wikipedia API.
 */
class WikipediaApiClient {

  /**
   * HTTP client.
   */
  protected ClientInterface $httpClient;

  /**
   * Constructor.
   */
  public function __construct(ClientInterface $httpClient) {
    $this->httpClient = $httpClient;
  }

  /**
   * Gets page information from Wikipedia.
   *
   * Returns the decoded JSON response.
   *
   * @param string $pageTitle
   *   Wikipedia page title.
   *
   * @return array|null
   *   Decoded JSON response or NULL.
   */
  public function getPageData(string $pageTitle): ?array {

    $url = 'https://en.wikipedia.org/w/api.php';

    try {

      $response = $this->httpClient->request('GET', $url, [
        'query' => [
          'action' => 'query',
          'format' => 'json',
          'prop' => 'pageimages',
          'piprop' => 'original',
          'titles' => $pageTitle,
        ],
        'timeout' => 20,
      ]);

      return json_decode(
        $response->getBody()->getContents(),
        TRUE
      );

    }
    catch (GuzzleException $e) {
      return NULL;
    }

  }

}
