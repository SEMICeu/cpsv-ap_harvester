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

