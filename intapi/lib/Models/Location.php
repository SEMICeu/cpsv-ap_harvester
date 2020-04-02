<?php
/*
 * Location
 */
namespace Models;
use JsonSerializable;

/*
 * Location
 */
class Location implements JsonSerializable {

      /* @var string $title  */
      private $title;


      /* @var string $location */
      private $id;


     /* @var string $isTypeOf */
     private $isTypeOf;


    public function __construct() {
        $this->isTypeOf = "Location";
         // allocate your stuff
     }

      public function getId(){
        return $this->id;
      }

      public function setId($id){
        $this->id = $id;
      }

      public function getTitle() {
        return $this->title;
      }

      public function setTitle($title){
        $this->title = $title;
      }

      public function jsonSerialize(){
        return array_filter(get_object_vars($this));
      }
}

