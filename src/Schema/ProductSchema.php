<?php

namespace Drupal\digital\Schema;

use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Image\ImageFactory;

class ProductSchema {

  protected Node $product;

  public function __construct(Node $product) {

    $this->product = $product;

  }


  public function getId(): string {

    return $this->productUrl() . "#product";

  }


  public function build(): array {

    $product = $this->product;


    $price = NULL;

    if (
      !$product
        ->get('field_digital_sale_price')
        ->isEmpty()
    ) {

      $price =
        $product
        ->get('field_digital_sale_price')
        ->value;

    }
    elseif (
      !$product
        ->get('field_digital_full_price')
        ->isEmpty()
    ) {

      $price =
        $product
        ->get('field_digital_full_price')
        ->value;

    }



    $currency = "USD";


    if (
      !$product
        ->get('field_digital_currency')
        ->isEmpty()
    ) {

      $currency =
        $product
        ->get('field_digital_currency')
        ->value;

      $currency = str_replace(
        "$",
        "USD",
        $currency
      );

    }



    $schema = [
      "@type"=>"Product",
//      "@id"=>$this->productUrl() . "#product",
      "name"=>$product->label(),
//      "url"=>$this->productUrl(),
//      "mainEntityOfPage"=>[
//        "@id"=>$this->productUrl() . "#webpage",
//      ],
      "offers"=>[
        "@type"=>"Offer",
//        "@id"=>$this->productUrl() . "#offer",
        "url"=>$this->buyUrl(),
//        "url"=>$this->productUrl(),
        "price"=>$price,
        "priceCurrency"=>$currency,
        "availability"=>"https://schema.org/InStock",
        "itemCondition" => "https://schema.org/NewCondition",
        "seller" => [
          "@type" => "Organization",
          "name" => "Karmaloop",
        ],
        "businessFunction" => "https://purl.org/goodrelations/v1#Sell",
      ]

    ];



    /*
     * Description
     */
/****
// Default description.
$description = $product->label() . ' available on My Urban Clothing. Discover streetwear and fashion products from trusted online retailers.';

// Use custom description if available.
if (!$product->get('field_digital_description')->isEmpty()) {

  // Long text field.
  $description = $product->get('field_digital_description')->value;

  // Strip any HTML.
  $description = Html::decodeEntities(strip_tags($description));

  // Normalize whitespace.
  $description = preg_replace('/\s+/', ' ', trim($description));

  // Try to end on the first sentence if it fits.
  if (preg_match('/^(.{50,220}?[.!?])(\s|$)/u', $description, $matches)) {
    $description = trim($matches[1]);
  }
  else {
    // Otherwise truncate nicely on a word boundary.
    $description = Unicode::truncate(
      $description,
      160,
      TRUE,   // Word boundary
      TRUE    // Add ellipsis
    );
  }
}



      $schema["description"] = $description;
//      $schema["description"] =
//        strip_tags(
//          $product
//          ->get('field_digital_description')
//          ->value
//        );

**/

    /*
     * Image
     */
/** @var \Drupal\file\FileInterface $file */
if (!$product->get('field_digital_product_image')->isEmpty()) {

  $file = $product->get('field_digital_product_image')->entity;

  if ($file) {

    $image_url = \Drupal::service('file_url_generator')
      ->generateAbsoluteString($file->getFileUri());

    $image = \Drupal::service('image.factory')
      ->get($file->getFileUri());

    $schema['image'] = [
      '@type' => 'ImageObject',
      'url' => $image_url,
    ];

    if ($image->isValid()) {
      $schema['image']['width'] = $image->getWidth();
      $schema['image']['height'] = $image->getHeight();
    }

  }
}



//    if (
//      !$product
//        ->get('field_digital_product_image')
//        ->isEmpty()
//    ){
//
//      $schema["image"] =
//        $product
//        ->get('field_digital_product_image')
//        ->uri;
//
//    }
//if (!$product->get('field_digital_product_image')->isEmpty()) {
//  $file = $product->get('field_digital_product_image')->entity;
//
//  if ($file) {
//
//$schema['image'] = \Drupal::service('file_url_generator')
//  ->generateAbsoluteString($file->getFileUri());
//  }
//}


    /*
     * Brand reference
     */

    if (
      !$product
        ->get('field_digital_brand')
        ->isEmpty()
    ){

      $brand =
        $product
        ->get('field_digital_brand')
        ->entity;


      $schema["brand"] = [

        "@type" => "Brand",
        "@id" =>
          Url::fromRoute(
            'entity.taxonomy_term.canonical',
            [
              'taxonomy_term'=>$brand->id()
            ]
          )
          ->setAbsolute()
          ->toString()
          . "#brand",
	"name" => $brand->label()

      ];

    }



    $category_array = [
      "Clothing"
    ];

    /*
     * Category
     */

    if (
      !$product
        ->get('field_digital_category')
        ->isEmpty()
    ){

      $category =
        $product
        ->get('field_digital_category')
        ->entity;


      $category_array[] =
        $category->label();

    }


    $schema["category"] = $category_array;


    /*
     * Gender
     */

    if (
      !$product
        ->get('field_digital_gender')
        ->isEmpty()
    ){

      $gender =
        $product
        ->get('field_digital_gender')
        ->entity;

      $gender_label = str_replace(
          ['Mens', 'Womens', 'Unisex'],
          ['Male', 'Female', 'Unisex'],
          $gender->label()
      );


      $schema["audience"]=[

        "@type"=>"PeopleAudience",

        "suggestedGender"=>
          $gender_label

      ];

    }

    $schema["isRelatedTo"]=[
      "@id"=>"https://myurbanclothing.com/#organization",
    ];

    $schema["additionalProperty"]=[
      [
        "@type"=>"PropertyValue",
        "name"=>"Style",
        "value"=>"Streetwear",
      ],
    ];


    if (
      !$product
        ->get('field_digital_category')
        ->isEmpty()
    ){

      $category =
        $product
        ->get('field_digital_category')
        ->entity;


      $schema["additionalProperty"][] = [
        "@type"=>"PropertyValue",
        "name"=>"Product Type",
        "value"=>$category->label(),
      ];


    }


    return $schema;

  }



  private function productUrl(): string {

    return
      Url::fromRoute(
        'entity.node.canonical',
        [
          'node'=>$this->product->id()
        ]
      )
      ->setAbsolute()
      ->toString()
      ;

  }



  private function buyUrl(): string {

    if (
      !$this->product
        ->get('field_digital_product_buy_url')
        ->isEmpty()
    ){

      return
        $this->product
        ->get('field_digital_product_buy_url')
        ->uri;

    }

    return '';

  }


}
