<?php

namespace Drupal\digital\Schema;


class FaqSchema {


private $entity;
private $url;


public function __construct($entity,$url){

$this->entity=$entity;
$this->url=$url;

}




public function build(){


$questions=[];


foreach(
$this->entity->field_faqs as $item
){


$paragraph=$item->entity;


$questions[]=[

"@type"=>"Question",

"name"=>$paragraph
->field_faq_question
->value,


"acceptedAnswer"=>[

"@type"=>"Answer",

"text"=>strip_tags(
$paragraph
->field_faq_answer
->value
)

]


];


}



return [

"@type"=>"FAQPage",

"@id"=>$this->url."#faq",
"url"=>$this->url,
"isPartOf"=> [
    "@id" => $this->url . "#webpage",
],
"mainEntity"=>$questions

];


}


}
