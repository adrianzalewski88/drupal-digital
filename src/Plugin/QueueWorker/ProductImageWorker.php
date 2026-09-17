<?php

namespace Drupal\digital\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

/**
 * Processes product images.
 *
 * @QueueWorker(
 *   id = "digital_product_image_queue",
 *   title = @Translation("Optimize product images"),
 *   cron = {"time" = 60}
 * )
 */
class ProductImageWorker extends QueueWorkerBase {


  /**
   * {@inheritdoc}
   */
  public function processItem($data) {

    $node = Node::load($data['nid']);

    if (!$node) {
      return;
    }


    // Do not overwrite existing images.
    if (!$node->get('field_digital_product_image')->isEmpty()) {
      return;
    }


    \Drupal::logger('digital')->notice(
      'Starting image processing for node @nid',
      [
        '@nid' => $node->id(),
      ]
    );


    $temp_file = NULL;


    try {

      /*
       * Download image.
       */
      $client = \Drupal::httpClient();


      $response = $client->get($data['image_url'], [
        'timeout' => 30,
      ]);


      if ($response->getStatusCode() !== 200) {
        throw new \Exception('Image download failed.');
      }


      /*
       * Save temporary image.
       */
      $directory = 'temporary://digital_product_images';


$temp_directory = 'temporary://digital_product_images';

\Drupal::service('file_system')
  ->prepareDirectory(
    $temp_directory,
    \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY
  );

//      \Drupal::service('file_system')
//        ->prepareDirectory(
//          $directory,
//          \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY
//        );


      $temp_file = $directory . '/' . uniqid('product_') . '.jpg';


      file_put_contents(
        $temp_file,
        $response->getBody()->getContents()
      );


      /*
       * Load image.
       */
      $image = \Drupal::service('image.factory')
        ->get($temp_file);


      if (!$image->isValid()) {
        throw new \Exception('Invalid image.');
      }


      /*
       * Resize to max width 500px.
       */
      if ($image->getWidth() > 500) {

        $image->scale(500);

      }


      /*
       * Save optimized WebP.
       */
      $filename = 'product_' . $node->id() . '.webp';

      $destination = 'public://products/' . $filename;


//      \Drupal::service('file_system')
//        ->prepareDirectory(
//          'public://products',
//          \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY
//        );

$directory = 'public://products';

\Drupal::service('file_system')
  ->prepareDirectory(
    $directory,
    \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY
  );


      if (!$image->save($destination)) {
        throw new \Exception('Could not save WebP image.');
      }

      $image = NULL;
      gc_collect_cycles();

      /*
       * Create Drupal managed file.
       */
      $file = File::create([
        'uri' => $destination,
      ]);


      $file->setPermanent();

      $file->save();


      /*
       * Attach image.
       */
      $node->set('field_digital_product_image', [
        'target_id' => $file->id(),
        'alt' => $data['alt'],
      ]);


      $node->save();


      \Drupal::logger('digital')->notice(
        'Finished image processing for node @nid',
        [
          '@nid' => $node->id(),
        ]
      );


    }
    catch (\Exception $e) {


      \Drupal::logger('digital')
        ->error(
          'Image processing failed for node @nid: @message',
          [
            '@nid' => $data['nid'],
            '@message' => $e->getMessage(),
          ]
        );


    }


    /*
     * Remove temporary file.
     */
    if ($temp_file && file_exists($temp_file)) {
      unlink($temp_file);
    }

  }

}
