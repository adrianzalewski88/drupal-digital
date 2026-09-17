<?php

namespace Drupal\digital\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'Random_default' formatter.
 *
 * @FieldFormatter(
 *   id = "digital_product_full_price",
 *   label = @Translation("Digital Product Full Price"),
 *   field_types = {
 *     "decimal"
 *   }
 * )
 */
class DigitalProductFullPriceFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays a formatted price.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    $sale_price;

    $parentEntity = $items->getParent()->getEntity();

    if(!$parentEntity->get('field_digital_sale_price')->isEmpty()) {
      $sale_price = $parentEntity->get('field_digital_sale_price')->first()->getValue();
    }

    $currency = $parentEntity->get('field_digital_currency')->first()->getValue();

    foreach ($items as $delta => $item) {

      //dpm($item);
      // Render each element as markup.

      if(!empty($sale_price['value']) && $sale_price['value'] != $item->value) {
        $output = '<div class="product-price">'
 . '<span>' . $currency['value'] . '</span>'
 . '<span>' . $sale_price['value'] . '</span>'
// . '<link itemprop="availability" href="https://schema.org/InStock">'
 . '</div><div class="product-price-compare">'
 . $currency['value']
 . $item->value
 . '</div>';
      } else {
        $output = '<div class="product-price">'
 . '<span>' . $currency['value'] . '</span>'
 . '<span>' . $item->value . '</span>'
// . '<link itemprop="availability" href="https://schema.org/InStock">'
 . '</div>';
      }

      $element[$delta] = ['#markup' => $output];
    }

    return $element;
  }

}
