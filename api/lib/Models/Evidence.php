<?php
namespace Models;
use JsonSerializable;

/*
* Evidence
 */
class Evidence implements JsonSerializable {
     /* @var string $id  */
     private $id;

    /* @var string $label  */
     private $title;


     public function getId() {
       return $this->id;
     }

     public function setId($id){
       $this->id = $id;
    }

     public function getTitle() {
       return $this->title;
     }

     public function setTitle($title) {
       $this->title = $title;
     }

     public function jsonSerialize(){
       return array_filter(get_object_vars($this));
     }
}

