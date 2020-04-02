<?php
/*
 * Business Event
 */
namespace Models;
use JsonSerializable;
/*
 * Business Event
 */
class CriterionRequirement implements JsonSerializable {

      /* @var string $id  */
      private $id;

      /* @var string $identifier */
      private $identifier;

      /* @var string $title */
      private $title;

      /* @var string $type */
      private $type;

      /* @var string $isTypeOf */
      private $isTypeOf;

      public function __construct() {
        $this->isTypeOf = "CriterionRequirement";
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
 
      public function getTitle(){
        return $this->title;
      }

      public function setTitle($title) {
        $this->title = $title;
      }

      public function getType(){
        return $this->type;
     }

      public function setType($type) {
        $this->type = $type;
      }

       public function jsonSerialize(){
        return array_filter(get_object_vars($this));
      }
}

