<?php
namespace Models;
use JsonSerializable;

/*
* Evidence
 */
class LegalResource implements JsonSerializable {
     /* @var string $id  */
     private $id;

     /* @var string $identifier */
     private $identifier;

     /* @var string $description */
     private $description;

     /* @var string $related */
     private $related;

     /* @var string $isPrivateOf */
     private $isTypeOf;

     public function __construct() {
         $this->isTypeOf = "LegalResource";
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

     public function setIdentifier($identifier) {
	$this->identifier = $identifier;
     }

     public function getDescription() {
        return $this->description;
     }

     public function setDescription($description) {
        $this->description = $description;
     }

      public function getRelated() {
        return $this->related;
      }

      public function setRelated($related) {
        $this->related = $related;
     }
     public function jsonSerialize(){
       return array_filter(get_object_vars($this));
     }
}

