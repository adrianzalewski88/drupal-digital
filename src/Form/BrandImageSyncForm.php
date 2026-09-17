<?php

namespace Drupal\digital\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;

/**
 * Provides the Brand Sync form.
 */
class BrandImageSyncForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'digital_brand_image_sync_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $tid = $this->getBrandTid();

    $form['tid'] = [
      '#type' => 'hidden',
      '#value' => $tid,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate Content'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Gets the current Brand taxonomy term ID.
   */
  protected function getBrandTid(): ?int {

    $route_match = $this->getRouteMatch();

    // If the route parameter is a loaded taxonomy term entity.
    $term = $route_match->getParameter('taxonomy_term');
    if ($term && method_exists($term, 'id')) {
      return (int) $term->id();
    }

    // If the route parameter is simply a tid.
    $tid = $route_match->getParameter('tid');
    if ($tid) {
      return (int) $tid;
    }

    return NULL;
  }


/***
public function submitForm(array &$form, FormStateInterface $form_state): void {

  $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

//  $tids = $storage->getQuery()
//    ->accessCheck(FALSE)
//    ->condition('vid', 'digital_brand')
//    ->execute();

  $tids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('vid', 'digital_brand')
    ->exists('field_digital_instagram_url')
    ->notExists('field_digital_image')
    ->execute();

//  $tids = $storage->getQuery()
//    ->accessCheck(FALSE)
//    ->condition('vid', 'digital_brand')
//    ->notExists('field_digital_description')
//    ->execute();


  $queue = \Drupal::queue('brand_image_sync');

  foreach ($tids as $tid) {
    $queue->createItem([
      'tid' => (int) $tid,
    ]);
  }

  $this->messenger()->addStatus(
    $this->t('@count brand images have been added to the brand sync queue.', [
      '@count' => count($tids),
    ])
  );
}
***/




  public function submitForm(array &$form, FormStateInterface $form_state): void {

    $tid = (int) $form_state->getValue('tid');

    $queue = \Drupal::queue('brand_instagram_image_sync');

    $queue->createItem([
      'tid' => $tid,
    ]);

    $this->messenger()->addStatus(
      $this->t('Brand @tid queued.', [
        '@tid' => $tid,
      ])
    );
  }


}
