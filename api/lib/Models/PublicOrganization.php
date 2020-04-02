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

    /* @var string $label  */
    private $label;

    /* @var string $spatialURI  */
    private $spatialURI;

    public function getId() {
      return $this->id;
    }
   
    public function setId($id){
      $this->id = $id;
    }

    public function getLabel() {
      return $this->title;
    }
 
    public function setLabel($label) {
      $this->label = $label;
    }

    public function getSpatialURI() {
      return $this->spatialURI;
    }

    public function setSpatialURI($spatialURI) {
      $this->spatialURI = $spatialURI;
    }

    public function jsonSerialize(){
      return get_object_vars($this);
    }
}
