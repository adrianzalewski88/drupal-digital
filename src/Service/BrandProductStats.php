<?php

namespace Drupal\digital\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

class BrandProductStats {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  public function getBrandStats(int $tid): array {
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');

    // Load the current brand term.
    $brand = $termStorage->load($tid);

    $brand_label = '';
    $brand_url = '';

    if ($brand) {
      $brand_label = $brand->label();
      $brand_url = $brand->toUrl()->toString();
    }

    $query = $nodeStorage->getQuery()
      ->condition('type', 'digital_product')
      ->condition('status', 1)
      ->condition('field_digital_brand.target_id', $tid)
      ->accessCheck(FALSE);

    $nids = $query->execute();

    $product_count = count($nids);
    $category_counts = [];

    if ($nids) {
      $nodes = $nodeStorage->loadMultiple($nids);

      foreach ($nodes as $node) {
        if (!$node->hasField('field_digital_category')) {
          continue;
        }

        foreach ($node->get('field_digital_category') as $category) {
          $cid = (int) $category->target_id;

          if ($cid <= 0) {
            continue;
          }

          if (!isset($category_counts[$cid])) {
            $category_counts[$cid] = 0;
          }

          $category_counts[$cid]++;
        }
      }
    }

    arsort($category_counts);

    $category_counts = array_slice(
      $category_counts,
      0,
      3,
      TRUE
    );

    $terms = [];

    if ($category_counts) {
      $terms = $termStorage->loadMultiple(array_keys($category_counts));
    }

    $top_categories = [];

    foreach ($category_counts as $cid => $count) {
      if (!isset($terms[$cid])) {
        continue;
      }

      $top_categories[] = [
        'tid' => $cid,
        'name' => $terms[$cid]->label(),
        'url' => $terms[$cid]->toUrl()->toString(),
        'count' => $count,
      ];
    }

    return [
      'brand_tid' => $tid,
      'brand_label' => $brand_label,
      'brand_url' => $brand_url,
      'product_count' => $product_count,
      'top_categories' => $top_categories,
    ];
  }

}
