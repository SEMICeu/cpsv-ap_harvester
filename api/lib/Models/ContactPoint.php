<?php
/*
 * Contact Point
 */
namespace Models;
use JsonSerializable;
/*
* Contact Point
 */
class ContactPoint implements JsonSerializable {
      /* @var string $id  */
      private $id;

      public function getId() {
        return $this->id;
      }
      public function setId($id){
        $this->id = $id;
      }

     public function jsonSerialize(){
         return array_filter(get_object_vars($this));
      }
}

