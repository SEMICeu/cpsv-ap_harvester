<?php
namespace Models;
use JsonSerializable;
/*
* PublicServiceDataset
 */
class PublicServiceDataset implements JsonSerializable {
     /* @var string $id  */
     private $id;
     /* @var string $identifier */
     private $identifier;
     /* @var string $title */
     private $title;
     /* @var string $landingPage */
     private $landingPage;
     /* @var string $publisher */
      private $publisher;

      public function __construct() {
        $this->isTypeOf = "PublicServiceDataset";
     }
     public function getId() {
        return $this->id;
     }
     public function setId($id){
        $this->id = $id;
     }
     public function getIdentifier(){
        return $this->identifer;
     }
     public function setIdentifier($identifier){
        $this->identifier = $identifier;
     }
     public function getTitle() {
        return $this->title;
     }
     public function setTitle($title) {
        $this->title = $title;
     }

     public function getLandingPage() {
        return $this->landingPage;
     }
     public function setLandingPage($landingPage) {
        $this->landingPage = $landingPage;
     }

     public function getPublisher() {
        return $this->publisher;
     }
     public function setPublisher($publisher) {
        $this->publisher = $publisher;
     }





     public function jsonSerialize(){
        return array_filter(get_object_vars($this));
     }
}
