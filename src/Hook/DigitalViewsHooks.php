<?php

declare(strict_types=1);

namespace Drupal\digital\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hook implementations for the Digital module.
 */
class DigitalViewsHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_views_data().
   */
  #[Hook('views_data')]
  public function viewsData(): array {
    $data = [];

    /*
     * Product Display Description.
     */
    $data['views']['digital_product_display_description'] = [
      'title' => $this->t('Product Display Description'),
      'help' => $this->t(
        'Displays the Product Display Description from the current content.'
      ),
      'area' => [
        'id' => 'product_display_description',
      ],
    ];

    /*
     * Brand Display Description.
     */
    $data['views']['digital_brand_display_description'] = [
      'title' => $this->t('Brand Display Description'),
      'help' => $this->t(
        'Displays the Brand Display Description from the current content.'
      ),
      'area' => [
        'id' => 'brand_display_description',
      ],
    ];

    /*
     * Blog Display Description.
     */
    $data['views']['digital_blog_display_description'] = [
      'title' => $this->t('Blog Display Description'),
      'help' => $this->t(
        'Displays the Blog Display Description from the current content.'
      ),
      'area' => [
        'id' => 'blog_display_description',
      ],
    ];

    /*
     * Blog Display Description.
     */
    $data['views']['digital_style_display_description'] = [
      'title' => $this->t('Style Display Description'),
      'help' => $this->t(
        'Displays the Style Display Description from the current content.'
      ),
      'area' => [
        'id' => 'style_display_description',
      ],
    ];

    return $data;
  }

}
