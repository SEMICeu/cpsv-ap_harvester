<?php
namespace Models;
use JsonSerializable;

/*
* Evidence
 */
class Evidence implements JsonSerializable {
     /* @var string $id  */
     private $id;

     /* @var string $identifier */
     private $identifier;

     /* @var string $title  */
     private $title;

     /* @var string $description */
     private $description;

     /* @var string $language */
     private $language;

     /* @var string $related */
     private $related;

     /* @var string $type */
     private $type;

     /* @var string $isPrivateOf */
     private $isTypeOf;

     public function __construct() {
         $this->isTypeOf = "Evidence";
      }

     public function getId() {
       return $this->id;
     }

     public function setId($id){
       $this->id = $id;
    }

     public function getIdentifier() {
        return $this->identifier;
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

     public function getDescription() {
        return $this->description;
     }

     public function setDescription($description) {
        $this->description = $description;
     }


      public function getLanguage() {
        return $this->language;
      }
 
      public function setLanguage($language) {
        $this->language = $language;
      }

      public function getRelated() {
        return $this->related;
      }

      public function setRelated($related) {
        $this->related = $related;
     }

      public function getType() {
        return $this->type;
      }

      public function setType($type) {
        $this->type = $type;
      }

     public function jsonSerialize(){
       return array_filter(get_object_vars($this));
     }
}

