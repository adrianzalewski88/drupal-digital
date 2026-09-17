<?php

namespace Drupal\digital\Schema;


class ItemListSchema {


  protected array $products;

  protected string $url;

  protected string $title;

  protected string $description;


  public function __construct(
    array $products,
    string $url,
    string $title,
    string $description
  ){

    $this->products = $products;

    $this->url = $url;

    $this->title = $title;

    $this->description = $description;

  }


  public function build(): array {


    $items = [];

    $position = 1;


    foreach ($this->products as $product) {


      /*
       * Build the Product schema.
       *
       * ProductSchema contains the complete product
       * information including:
       *
       * - Product name
       * - Product URL
       * - Product ID
       * - Description
       * - Image
       * - Brand
       * - Category
       * - Gender
       * - Price
       * - Currency
       * - Offer
       * - Availability
       * - Seller
       * - Additional properties
       */
      $productSchema =
        new ProductSchema($product);


      $productData =
        $productSchema->build();


      /*
       * Add the complete Product entity
       * directly inside the ListItem.
       */
      $items[] = [

        "@type" =>
          "ListItem",

        "position" =>
          $position++,

        "item" =>
          $productData,

      ];


    }


    return [

      "@type" =>
        "ItemList",

      "@id" =>
        $this->url . "#featured-products",

      "name" =>
        $this->title,

      "description" =>
        $this->description,

      "numberOfItems" =>
        count($items),

      "itemListOrder" =>
        "https://schema.org/ItemListOrderDescending",

      "itemListElement" =>
        $items,

    ];


  }


}
