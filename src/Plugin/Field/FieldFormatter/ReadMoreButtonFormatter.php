<?php

namespace Drupal\digital\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Provides a configurable Read More button formatter.
 *
 * @FieldFormatter(
 *   id = "digital_read_more_button",
 *   label = @Translation("Digital Read More Button"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class ReadMoreButtonFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'button_type' => 'article',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $elements['button_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Button Type'),
      '#default_value' => $this->getSetting('button_type'),
      '#options' => [
        'article' => $this->t('Article'),
        'style' => $this->t('Style'),
        'brand' => $this->t('Brand'),
      ],
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    return [
      $this->t('Displays a "@type" button.', [
        '@type' => ucfirst($this->getSetting('button_type')),
      ]),
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];

    $entity = $items->getEntity();

    // Prevent Layout Builder preview errors.
    if (!$entity->id()) {
      return $elements;
    }

    switch ($this->getSetting('button_type')) {

      case 'style':
        $label = $this->t('View Style');
        break;

      case 'brand':
        $label = $this->t('View Brand');
        break;

      case 'article':
      default:
        $label = $this->t('View Article');
        break;

    }

    $elements[] = Link::fromTextAndUrl(
      $label,
      $entity->toUrl()
    )->toRenderable();

    $elements[0]['#attributes']['class'] = [
      'button',
      'button--primary',
      'digital-read-more',
    ];

    return $elements;
  }

}
