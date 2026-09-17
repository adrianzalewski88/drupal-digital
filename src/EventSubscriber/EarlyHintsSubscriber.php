<?php

namespace Drupal\digital\EventSubscriber;

use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EarlyHintsSubscriber implements EventSubscriberInterface {

  /**
   * Adds preload Link headers for aggregated CSS files.
   */
  public function onResponse(ResponseEvent $event): void {
    $response = $event->getResponse();

    // Only process HTML responses.
    if (!$response instanceof HtmlResponse) {
      return;
    }

    $content = $response->getContent();

    if (!$content) {
      return;
    }

    // Find stylesheet links in the generated HTML.
    preg_match_all(
      '/<link[^>]+rel=["\']stylesheet["\'][^>]+href=["\']([^"\']+)["\']/i',
      $content,
      $matches
    );

    if (!empty($matches[1])) {

      foreach ($matches[1] as $href) {
        // Decode HTML entities such as &amp;.
        $href = html_entity_decode($href, ENT_QUOTES | ENT_HTML5);

        // Only preload local Drupal CSS assets.
        if (str_starts_with($href, '/sites/default/files/css/')) {
          $response->headers->set(
            'Link',
            '<' . $href . '>; rel=preload; as=style',
            false
          );
        }
      }
    }


    // Find JavaScript files in the generated HTML.
    preg_match_all(
      '/<script[^>]+src=["\']([^"\']+)["\']/i',
      $content,
      $js_matches
    );

    if (!empty($js_matches[1])) {
      foreach ($js_matches[1] as $src) {
        // Decode HTML entities such as &amp;.
        $src = html_entity_decode($src, ENT_QUOTES | ENT_HTML5);

        // Only preload local Drupal aggregated JS assets.
        if (str_starts_with($src, '/sites/default/files/js/')) {
          $response->headers->set(
            'Link',
            '<' . $src . '>; rel=preload; as=script',
            false
          );
        }
      }
    }


    $response->headers->set(
      'Link',
      '</themes/custom/digitaltheme/images/urban-title-bg-clothing-01.webp>; rel=preload; as=image',
      false
    );

    $response->headers->set(
      'Link',
      '</themes/custom/digitaltheme/images/urban-header-bg-clothing-01.webp>; rel=preload; as=image',
      false
    );

    $response->headers->set(
      'Link',
      '</sites/default/files/my-urban-clothing-logo.png>; rel=preload; as=image',
      false
    );

    $response->headers->set(
      'Link',
      '</themes/custom/digitaltheme/images/cart-icon.png>; rel=preload; as=image',
      false
    );

    $response->headers->set(
      'Link',
      '</themes/custom/digitaltheme/css/webfonts/fa-solid-900.woff2>; rel=preload; as=font; type="font/woff2"; crossorigin',
      false
    );

    $response->headers->set(
      'Link',
      '</themes/custom/digitaltheme/css/webfonts/fa-brands-400.woff2>; rel=preload; as=font; type="font/woff2"; crossorigin',
      false
    );


  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => ['onResponse', -100],
    ];
  }

}
