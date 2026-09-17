<?php

namespace Drupal\digital\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\paragraphs\Entity\Paragraph;

class BrandMapper {

  /**
   * The entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs the BrandMapper service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Updates a digital_brand term from OpenAI JSON.
   *
   * @param int $tid
   *   The brand taxonomy term ID.
   * @param array $data
   *   The decoded OpenAI response.
   *
   * @throws \RuntimeException
   *   When the brand term cannot be found.
   */
  public function updateBrand(int $tid, array $data): void {

    /** @var \Drupal\taxonomy\TermInterface|null $term */
    $term = Term::load($tid);

    if (!$term) {
      throw new \RuntimeException("Brand term {$tid} not found.");
    }

    //
    // Basic brand fields.
    //

    $this->setIfProvided(
      $term,
      'field_digital_brand_name',
      $data['brand_name'] ?? NULL
    );

    $this->setIfProvided(
      $term,
      'field_page_heading',
      $data['page_heading'] ?? NULL
    );

    $this->setIfProvided(
      $term,
      'field_summary',
      $data['summary'] ?? NULL
    );

    $this->setIfProvided(
      $term,
      'field_digital_featured_desc',
      $data['featured_description'] ?? NULL
    );

    $this->setIfProvided(
      $term,
      'field_digital_description',
      $data['page_body_html'] ?? NULL
    );

    $this->setIfProvided(
      $term,
      'field_digital_brand_score',
      $data['brand_score'] ?? NULL
    );

    //
    // Taxonomy term description.
    //

    if (!empty($data['term_description'])) {
      $term->setDescription($data['term_description']);
    }


//
// Meta tags.
//

if (
  !empty($data['meta']) &&
  is_array($data['meta']) &&
  $term->hasField('field_metatags')
) {
  $meta = [];

  foreach (['title', 'description', 'abstract'] as $key) {
    if (
      isset($data['meta'][$key]) &&
      $data['meta'][$key] !== ''
    ) {
      $meta[$key] = $data['meta'][$key];
    }
  }

  if (!empty($meta)) {
    $term->set('field_metatags', serialize($meta));
  }
}


    //
    // Link fields.
    //
    // Empty URLs are ignored so an empty OpenAI response does not
    // overwrite an existing Drupal URL.
    //

    $this->setLinkIfProvided(
      $term,
      'field_wikidata_url',
      $data['wikidata_url'] ?? NULL
    );

    $this->setLinkIfProvided(
      $term,
      'field_wikipedia_url',
      $data['wikipedia_url'] ?? NULL
    );

    $this->setLinkIfProvided(
      $term,
      'field_digital_twitter_url',
      $data['digital_twitter_url'] ?? NULL
    );

    $this->setLinkIfProvided(
      $term,
      'field_digital_facebook_url',
      $data['digital_facebook_url'] ?? NULL
    );

    $this->setLinkIfProvided(
      $term,
      'field_digital_instagram_url',
      $data['digital_instagram_url'] ?? NULL
    );

    //
    // Styles.
    //
    // Only existing digital_styles terms are attached.
    // Missing styles are ignored.
    //

    if (isset($data['styles']) && is_array($data['styles'])) {
      $style_terms = $this->findStyleTerms($data['styles']);

      $term->set('field_digital_styles', $style_terms);
    }


//
// FAQs.
//
if (isset($data['faqs']) && is_array($data['faqs'])) {

  // Delete existing FAQ paragraphs first.
  $this->deleteExistingFaqs($term);

  // Create the new FAQ paragraphs.
  $faq_paragraphs = $this->createFaqParagraphs($data['faqs']);

  $term->set('field_faqs', $faq_paragraphs);
}


    //
    // AI bookkeeping.
    //

    $this->setIfProvided(
      $term,
      'field_ai_last_sync',
      date('Y-m-d\TH:i:s')
    );

    $this->setIfProvided(
      $term,
      'field_ai_sync_status',
      'success'
    );

    //
    // Save brand term.
    //

    $term->save();
  }

  /**
   * Sets a field only when a value was actually provided.
   *
   * NULL means the field should not be changed.
   */
  protected function setIfProvided(
    $entity,
    string $field,
    $value
  ): void {

    if (!$entity->hasField($field)) {
      return;
    }

    if ($value === NULL) {
      return;
    }

  $field_definition = $entity->getFieldDefinition($field);

  // Formatted text fields require both the value and text format.
  if ($field_definition->getType() === 'text_long' ||
      $field_definition->getType() === 'text_with_summary') {

    $entity->set($field, [
      'value' => $value,
      'format' => 'basic_html',
    ]);

    return;
  }

    $entity->set($field, $value);
  }

  /**
   * Sets a link field only when a non-empty URL is provided.
   *
   * Empty strings are intentionally ignored.
   */
  protected function setLinkIfProvided(
    $entity,
    string $field,
    ?string $url
  ): void {

    if (!$entity->hasField($field)) {
      return;
    }

    if ($url === NULL || trim($url) === '') {
      return;
    }

    $entity->set($field, [
      'uri' => trim($url),
      'title' => '',
    ]);
  }

  /**
   * Finds existing digital_styles terms by name.
   *
   * Missing styles are ignored.
   *
   * @param array $style_names
   *   Style labels returned by OpenAI.
   *
   * @return array
   *   Array of taxonomy term IDs.
   */
  protected function findStyleTerms(array $style_names): array {

    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');

    $term_ids = [];

    foreach ($style_names as $style_name) {

      if (!is_string($style_name)) {
        continue;
      }

      $style_name = trim($style_name);

      if ($style_name === '') {
        continue;
      }

      $terms = $term_storage->loadByProperties([
        'vid' => 'digital_styles',
        'name' => $style_name,
      ]);

      if (empty($terms)) {
        continue;
      }

      /** @var \Drupal\taxonomy\TermInterface $style_term */
      $style_term = reset($terms);

      $term_ids[] = $style_term->id();
    }

    return array_values(array_unique($term_ids));
  }

  /**
   * Creates FAQ item paragraphs.
   *
   * @param array $faqs
   *   FAQ data from OpenAI.
   *
   * @return array
   *   Array of paragraph entities.
   */
  protected function createFaqParagraphs(array $faqs): array {

    $paragraphs = [];

    foreach ($faqs as $faq) {

      if (!is_array($faq)) {
        continue;
      }

      $question = trim((string) ($faq['question'] ?? ''));
      $answer = trim((string) ($faq['answer'] ?? ''));

      if ($question === '' || $answer === '') {
        continue;
      }

      $paragraph = Paragraph::create([
        'type' => 'faq_item',
        'field_faq_question' => $question,
        'field_faq_answer' => $answer,
      ]);

      $paragraphs[] = $paragraph;
    }

    return $paragraphs;
  }

/**
 * Deletes all existing FAQ paragraphs assigned to a brand term.
 *
 * @param \Drupal\taxonomy\TermInterface $term
 *   The brand taxonomy term.
 */
protected function deleteExistingFaqs($term): void {

  if (!$term->hasField('field_faqs')) {
    return;
  }

  $faq_items = $term->get('field_faqs')->referencedEntities();

  foreach ($faq_items as $faq) {
    if ($faq->getEntityTypeId() === 'paragraph') {
      $faq->delete();
    }
  }

  // Remove the old references from the brand.
  $term->set('field_faqs', []);
}

}
