<?php
/*
 * LinguisticSystem
 */
namespace Models;
use JsonSerializable;
/*
 * LinguisticSystem
 */
class LinguisticSystem implements JsonSerializable {
      /* @var string $prefLabel  */
      private $prefLabel;


     /* @var string $id */
     private $id;

     /* @var string $isTypeOf */
     private $isTypeOf;


    public function __construct() {
        $this->isTypeOf = "LinguisticSystem";
         // allocate your stuff
     }


      public function getId(){
        return $this->id;
      }

      public function setId($id){
        $this->id = $id;
      }

      public function getPrefLabel() {
        return $this->prefLabel;
      }

      public function setPrefLabel($prefLabel){
        $this->prefLabel = $prefLabel;
      }

      public function jsonSerialize(){
        return array_filter(get_object_vars($this));
      }
}

