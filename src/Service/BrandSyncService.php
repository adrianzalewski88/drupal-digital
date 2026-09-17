<?php

namespace Drupal\digital\Service;

class BrandSyncService {

  public function __construct(
    protected BrandProductStats $brandProductStats,
    protected BrandPromptBuilder $promptBuilder,
    protected OpenAIClient $openAIClient,
    protected BrandMapper $brandMapper,
  ) {}

  public function syncBrand(int $tid): void {

    $stats = $this->brandProductStats->getBrandStats($tid);

    $prompt = $this->promptBuilder->build($stats);

    \Drupal::logger('digital')->notice(
      "Brand Sync Prompt:\n<pre>@prompt</pre>",
      [
        '@prompt' => $prompt,
      ]
    );


try {
  // Send prompt to OpenAI.
  $response = $this->openAIClient->generate($prompt);

  // Log raw response.
  \Drupal::logger('digital')->notice(
    "OpenAI Response:\n<pre>@response</pre>",
    [
      '@response' => $response,
    ]
  );

  // Decode JSON if you're using json_schema output.
  $data = json_decode($response, TRUE);

  if (json_last_error() !== JSON_ERROR_NONE) {
    throw new \RuntimeException('OpenAI returned invalid JSON.');
  }

  // Update Drupal taxonomy/entity.
  $this->brandMapper->updateBrand($tid, $data);

}
catch (\Throwable $e) {
  \Drupal::logger('digital')->error(
    'Brand sync failed: @message',
    [
      '@message' => $e->getMessage(),
    ]
  );

  throw $e;
}


  }

}
