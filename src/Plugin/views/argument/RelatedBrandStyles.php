<?php

namespace Drupal\digital\Plugin\views\argument;

use Drupal\taxonomy\TermInterface;
use Drupal\views\Attribute\ViewsArgument;
use Drupal\views\Plugin\views\argument\ArgumentPluginBase;

/**
 * Provides a contextual filter for related digital brands.
 *
 * Finds digital_brand taxonomy terms that share at least one
 * digital_style with the current digital_brand term.
 */
#[ViewsArgument(
  id: 'digital_related_brand_styles',
)]
class RelatedBrandStyles extends ArgumentPluginBase {

  /**
   * {@inheritdoc}
   */
  public function hasDefaultArgument(): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultArgument(): ?string {
    $term = $this->getCurrentBrandTerm();

    if (!$term) {
      return NULL;
    }

    return (string) $term->id();
  }

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE): void {
    $term = $this->getCurrentBrandTerm();

    if (!$term) {
      $this->addNoResultsCondition();
      return;
    }

    $current_tid = (int) $term->id();

    /*
     * Get the digital_styles terms attached to the current brand.
     */
    $style_tids = $this->getCurrentBrandStyleIds($term);

    /*
     * A brand without styles cannot have related brands.
     */
    if (empty($style_tids)) {
      $this->addNoResultsCondition();
      return;
    }

    /*
     * Get the base table used by the taxonomy term View.
     */
    $base_table = $this->view->storage->get('base_table');
    $base_field = $this->view->storage->get('base_field');

    $base_alias = $this->query->ensureTable($base_table);

    if (!$base_alias) {
      $this->addNoResultsCondition();
      return;
    }

    /*
     * Only return digital_brand taxonomy terms.
     */
    $this->query->addWhere(
      0,
      "$base_alias.vid",
      'digital_brand',
      '='
    );

    /*
     * Exclude the current brand.
     */
    $this->query->addWhere(
      0,
      "$base_alias.$base_field",
      $current_tid,
      '<>'
    );

    /*
     * Get the database table/column information for
     * field_digital_styles.
     */
    $term_storage = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term');

    $table_mapping = $term_storage->getTableMapping();

    $styles_table = $table_mapping->getFieldTableName(
      'field_digital_styles'
    );

    $styles_columns = $table_mapping->getColumnNames(
      'field_digital_styles'
    );

    if (empty($styles_columns['target_id'])) {
      $this->addNoResultsCondition();
      return;
    }

    $styles_target_column = $styles_columns['target_id'];

    /*
     * Join field_digital_styles for the brands being returned
     * by the View.
     */
    $styles_alias = $this->query->ensureTable($styles_table);


    if (!$styles_alias) {
      $this->addNoResultsCondition();
      return;
    }

    /*
     * Match ANY style belonging to the current brand.
     *
     * Example:
     *
     * Current brand:
     *   Streetwear
     *   Skate
     *   Athletic
     *
     * Related brand qualifies if it has:
     *   Streetwear OR Skate OR Athletic
     */
    $this->query->addWhere(
      0,
      "$styles_alias.$styles_target_column",
      $style_tids,
      'IN'
    );

  }

  /**
   * Gets the current digital_brand taxonomy term.
   *
   * @return \Drupal\taxonomy\TermInterface|null
   *   The current digital_brand term, or NULL.
   */
  protected function getCurrentBrandTerm(): ?TermInterface {
    $term = \Drupal::routeMatch()->getParameter('taxonomy_term');

    /*
     * Normal Drupal taxonomy term route.
     */
    if ($term instanceof TermInterface) {
      return $term->bundle() === 'digital_brand'
        ? $term
        : NULL;
    }

    /*
     * Fallback for routes that provide a numeric TID.
     */
    if (is_numeric($term)) {
      $term = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->load((int) $term);

      if (
        $term instanceof TermInterface &&
        $term->bundle() === 'digital_brand'
      ) {
        return $term;
      }
    }

    return NULL;
  }

  /**
   * Gets the digital_style term IDs from the current brand.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The current digital_brand term.
   *
   * @return int[]
   *   Digital_style taxonomy term IDs.
   */
  protected function getCurrentBrandStyleIds(
    TermInterface $term
  ): array {
    if (!$term->hasField('field_digital_styles')) {
      return [];
    }

    $field = $term->get('field_digital_styles');

    if ($field->isEmpty()) {
      return [];
    }

    $style_tids = [];

    foreach ($field->getValue() as $item) {
      if (!empty($item['target_id'])) {
        $style_tids[] = (int) $item['target_id'];
      }
    }

    /*
     * Ignore very broad styles when finding related brands.
     *
     * These styles are too generic and would make nearly every
     * brand appear related.
     */
    $excluded_style_tids = [
      534, // Streetwear
      536, // Urban Casual
      537, // Hip-Hop Fashion
    ];

    $style_tids = array_diff(
      array_unique($style_tids),
      $excluded_style_tids
    );

    return array_values(array_unique($style_tids));
  }

  /**
   * Adds a condition that guarantees no results.
   */
  protected function addNoResultsCondition(): void {
    $base_table = $this->view->storage->get('base_table');
    $base_field = $this->view->storage->get('base_field');

    $base_alias = $this->query->ensureTable($base_table);

    if ($base_alias) {
      $this->query->addWhere(
        0,
        "$base_alias.$base_field",
        0,
        '='
      );
    }
  }

}
