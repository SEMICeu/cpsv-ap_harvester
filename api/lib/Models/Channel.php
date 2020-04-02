<?php
/*
 * Channel
 */
namespace Models;
use JsonSerializable;
/*
 * Channel
 */
class Channel implements JsonSerializable {
      /* @var string $id  */
      private $id;

      /* @var string $type */
      private $type;

      /* @var string $evidenceURI */
      private $evidenceURI;

      public function getId() {
        return $this->id;
      }
      public function setId($id){
        $this->id = $id;
      }

      public function getTypeURI(){
        return $this->type;
      }
   
      public function setTypeURI($type) {
        $this->type = $type;
      }

      public function getEvidenceURI() {
         return $this->evidenceURI;
      }

      public function setEvidenceURI($evidenceURI){
         $this->evidenceURI = $evidenceURI;
      }

      public function jsonSerialize(){
        return array_filter(get_object_vars($this));
      }
}

