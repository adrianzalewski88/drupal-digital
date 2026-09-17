<?php

namespace Drupal\digital\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\taxonomy\TermInterface;

/**
 * Plugin implementation of the 'h3_text' formatter.
 *
 * @FieldFormatter(
 *   id = "h3_text",
 *   label = @Translation("H3 Text"),
 *   field_types = {
 *     "string",
 *     "string_long"
 *   }
 * )
 */
class H3TextFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $entity = $items->getEntity();

    foreach ($items as $delta => $item) {
      // Get the raw value directly from the field item.
      $value = $item->value;

      // If this field belongs to a taxonomy term, link the value
      // to the term's canonical page.
      if ($entity instanceof TermInterface) {
        $url = $entity->toUrl('canonical');

        $elements[$delta] = [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          'link' => [
            '#type' => 'link',
            '#title' => $value,
            '#url' => $url,
            '#options' => [
              'attributes' => [
                'title' => $this->t('Explore @style Fashion Style', [
                  '@style' => $value,
                ]),
              ],
            ],
          ],
        ];
      }
      else {
        // Fall back to plain H3 output for other string fields.
        $elements[$delta] = [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#value' => $value,
        ];
      }
    }

    return $elements;
  }

}
