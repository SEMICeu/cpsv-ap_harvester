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

