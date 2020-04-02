<?php
/*
 * Concept
 */
namespace Models;
use JsonSerializable;
/*
 * Concept
 */
class Concept implements JsonSerializable {
      /* @var string $prefLabel  */
   private $prefLabel;


     /* @var string $isTypeOf */
     private $isTypeOf;

     /* @var string $id */
    private $id;

    public function __construct() {
        $this->isTypeOf = "Concept";
         // allocate your stuff
     }

      public function getId() {
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

