<?php

namespace Drupal\digital\Schema;


class CollectionSchema {


private $entity;
private $url;
protected array $context;


public function __construct($entity,$url,array $context){

$this->entity=$entity;
$this->url=$url;
$this->context=$context;

}



public function build(){


return [

"@type"=>"CollectionPage",

"@id"=>$this->url."#collection",

"url"=>$this->url,

"name"=>$this->entity->label()." Clothing Collection",

"description"=>"Browse " . $this->entity->label() . " products, apparel, and streetwear styles from trusted online retailers.",

"isPartOf" => [
  "@id" => $this->url . "#webpage",
],

"about" => [
  "@id" => "https://myurbanclothing.com/#organization",
],

//"mainEntity" => [
//  "@id" => $this->url . "#collection",
//],

];


}


}
