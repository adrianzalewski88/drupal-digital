<?php

namespace Drupal\digital\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Plugin implementation of the 'buy_link_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "buy_link_formatter",
 *   label = @Translation("Buy Link"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class BuyLinkFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];

    foreach ($items as $delta => $item) {

      // Get the URL stored in the Link field.
      $url = Url::fromUri($item->uri);

      // Create the Buy link.
      $link = Link::fromTextAndUrl(
        $this->t('Discover'),
        $url
      );

$elements[$delta] = [
  '#type' => 'link',
  '#title' => $this->t('Discover Now'),
  '#url' => $url,
  '#options' => [
    'attributes' => [
      'class' => [
        'btn',
        'btn-primary',
        'buy-link',
      ],
      'target' => '_blank',
      'rel' => 'nofollow sponsored noopener',
    ],
  ],
];



    }

    return $elements;
  }

}
