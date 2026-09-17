<?php

namespace Drupal\digital\Product;


use Drupal\Core\Path\CurrentPathStack;
use Drupal\path_alias\AliasManagerInterface;


class CollectionContext {


  protected CurrentPathStack $currentPath;

  protected AliasManagerInterface $aliasManager;



  public function __construct(
    CurrentPathStack $currentPath,
    AliasManagerInterface $aliasManager
  ){

    $this->currentPath = $currentPath;

    $this->aliasManager = $aliasManager;

  }




  public function getContext($entity = NULL): array {


    /*
     * Brand taxonomy page
     */

    if(
      $entity &&
      $entity->getEntityTypeId() === 'taxonomy_term'
    ){

      return [

        "type"=>"brand",

        "brand"=>$entity->id(),

        "title" =>
          $entity->label() .
          " Clothing Collection",

        "description" =>
          "Browse " .
          $entity->label() .
          " clothing, apparel, and streetwear styles."

      ];

    }



    $alias =
      trim(
        $this->aliasManager
          ->getAliasByPath(
            $this->currentPath->getPath()
          ),
        '/'
      );



    switch($alias){

      case "discover":

        return [

          "type"=>"all",

          "brand"=>NULL,

          "title"=>
            "Popular Urban Clothing Collection",

          "description"=>
            "Browse popular urban clothing, streetwear, and apparel."

        ];



      case "mens":

        return [

          "type"=>"mens",

          "brand"=>NULL,

          "title"=>
            "Men's Urban Clothing Collection",

          "description"=>
            "Browse men's urban clothing, streetwear, and fashion apparel."

        ];



      case "womens":

        return [

          "type"=>"womens",

          "brand"=>NULL,

          "title"=>
            "Women's Urban Clothing Collection",

          "description"=>
            "Browse women's urban clothing, streetwear, and fashion apparel."

        ];


    }



    return [];

  }


}
