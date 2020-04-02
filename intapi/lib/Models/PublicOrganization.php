<?php
/*
 * PublicOrganization
 */
namespace Models;
use JsonSerializable;

/*
 * PublicOrganization
 */
class PublicOrganization implements JsonSerializable {
    /* @var string $id  */
    private $id;

    /* @var string $title; */
    private $title;

    /* @var string $prefLabel  */
    private $prefLabel;

    /* @var string $spatial  */
    private $spatial;


     /* @var string $isTypeOf */
     private $isTypeOf;

     public function __construct() {
      $this->isTypeOf = "PublicOrganisation";
         // allocate your stuff
     }

    public function getId() {
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


    public function getPrefLabel() {
      return $this->prefLabel;
    }
 
    public function setPrefLabel($prefLabel) {
      $this->prefLabel = $prefLabel;
    }

    public function getSpatial() {
      return $this->spatial;
    }

    public function setSpatial($spatial) {
      $this->spatial = $spatial;
    }

    public function jsonSerialize(){
      return array_filter(get_object_vars($this));
    }

}
