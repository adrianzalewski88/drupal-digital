<?php

namespace Drupal\digital\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'digital_star_rating' formatter.
 *
 * @FieldFormatter(
 *   id = "digital_star_rating",
 *   label = @Translation("Digital 5 Star Rating"),
 *   field_types = {
 *     "decimal"
 *   }
 * )
 */
class DigitalStarRatingFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];

    foreach ($items as $delta => $item) {

      $value = (float) $item->value;

      if ($value < 20) {
        $active = 1;
      }
      elseif ($value < 40) {
        $active = 2;
      }
      elseif ($value < 60) {
        $active = 3;
      }
      elseif ($value < 80) {
        $active = 4;
      }
      else {
        $active = 5;
      }

      $rating = round(max(0, min(100, (float) $item->value))) . '%';

      $elements[$delta] = [
        '#theme' => 'digital_star_rating',
        '#active' => $active,
        '#total' => 5,
        '#rating' => $rating,
      ];
    }

    return $elements;
  }

}
