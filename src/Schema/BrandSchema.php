<?php

namespace Drupal\digital\Schema;

use Drupal\Core\Image\ImageFactoryInterface;

class BrandSchema {


private $term;
private $url;
private $fileUrl;


public function __construct($term,$url,$fileUrl){

$this->term=$term;
$this->url=$url;
$this->fileUrl=$fileUrl;

}



public function build(){


$image = null;
$width = '200';
$height = '200';

if(
$this->term->hasField('field_digital_image')
&& !$this->term->get('field_digital_image')->isEmpty()
){

$file =
$this->term
->get('field_digital_image')
->entity;

$file_image = \Drupal::service('image.factory')->get($file->getFileUri());

if ($file_image->isValid()) {
  $width = $file_image->getWidth();
  $height = $file_image->getHeight();
}


$image=[
"@type"=>"ImageObject",
"url"=>$this->fileUrl->generateAbsoluteString(
$file->getFileUri()
),
"width"=>$width,
"height"=>$height
];

}


$wikidataUrl = null;
$wikipediaUrl = null;
$twitterUrl = null;
$facebookUrl = null;
$instagramUrl = null;

if (
  $this->term->hasField('field_wikidata_url') &&
  !$this->term->get('field_wikidata_url')->isEmpty()
) {
  $wikidataUrl = $this->term->get('field_wikidata_url')->uri;
}

if (
  $this->term->hasField('field_wikipedia_url') &&
  !$this->term->get('field_wikipedia_url')->isEmpty()
) {
  $wikipediaUrl = $this->term->get('field_wikipedia_url')->uri;
}

if (
  $this->term->hasField('field_digital_twitter_url') &&
  !$this->term->get('field_digital_twitter_url')->isEmpty()
) {
  $twitterUrl = $this->term->get('field_digital_twitter_url')->uri;
}

if (
  $this->term->hasField('field_digital_facebook_url') &&
  !$this->term->get('field_digital_facebook_url')->isEmpty()
) {
  $facebookUrl = $this->term->get('field_digital_facebook_url')->uri;
}

if (
  $this->term->hasField('field_digital_instagram_url') &&
  !$this->term->get('field_digital_instagram_url')->isEmpty()
) {
  $instagramUrl = $this->term->get('field_digital_instagram_url')->uri;
}

$sameAs = array_values(array_filter([
  $wikidataUrl,
  $wikipediaUrl,
  $twitterUrl,
  $facebookUrl,
  $instagramUrl,
]));





$schema = [

  "@type" => "Brand",

  "@id" => $this->url . "#brand",

  "name" => $this->term->label(),

  "logo" => $image,

  "description" => strip_tags(
    $this->term->label() . ' and more on My Urban Clothing.'
  ),

];

if (!empty($sameAs)) {
  $schema["sameAs"] = $sameAs;
}

return $schema;





}


}
