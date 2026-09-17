<?php

namespace Drupal\digital\Service;

use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Downloads remote images into Drupal.
 */
class RemoteImageDownloader {

  /**
   * HTTP client.
   */
  protected ClientInterface $httpClient;

  /**
   * File repository.
   */
  protected FileRepositoryInterface $fileRepository;

  /**
   * File system.
   */
  protected FileSystemInterface $fileSystem;

  /**
   * Constructor.
   */
  public function __construct(
    ClientInterface $httpClient,
    FileRepositoryInterface $fileRepository,
    FileSystemInterface $fileSystem,
  ) {
    $this->httpClient = $httpClient;
    $this->fileRepository = $fileRepository;
    $this->fileSystem = $fileSystem;
  }

  /**
   * Downloads a remote image.
   *
   * @return \Drupal\file\Entity\File|null
   */
  public function download(string $url): ?File {

    try {

      $response = $this->httpClient->request('GET', $url);

      if ($response->getStatusCode() !== 200) {
        return NULL;
      }

      $contents = $response->getBody()->getContents();

      $directory = 'public://brand-images';

      $this->fileSystem->prepareDirectory(
        $directory,
        FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
      );

      $filename = basename(parse_url($url, PHP_URL_PATH));

      if (!$filename) {
        $filename = uniqid('brand_', TRUE) . '.jpg';
      }

      $destination = $directory . '/' . $filename;

      $file = $this->fileRepository->writeData(
        $contents,
        $destination,
        FileSystemInterface::EXISTS_RENAME,
      );

      if (!$file instanceof File) {
        return NULL;
      }

      $file->setPermanent();
      $file->save();

      return $file;

    }
    catch (GuzzleException $e) {
      return NULL;
    }

  }

}
