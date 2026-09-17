<?php

namespace Drupal\digital\Schema;


class PageSchema {


private $node;
private $url;


public function __construct($node,$url){

$this->node=$node;
$this->url=$url;

}



public function build(){


return [

"@type"=>"WebPage",

"@id"=>$this->url,

"url"=>$this->url,

"name"=>$this->node->label(),

"isPartOf"=>[
"@id"=>"https://myurbanclothing.com/#website"
],

"description"=>strip_tags(
$this->node->get('body')->value ?? ''
),
"inLanguage"=>"en-US",

"mainEntity" => [
  "@id" => $this->url . "#collection",
],

];


}



}
