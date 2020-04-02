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

      /* @var string $identifier  */
      private $identifier;


      /* @var string $ownedby */
      private $ownedBy;

      /* @var string $type */
      private $type;

      /* @var string $hasInput */
      private $hasInput;

      /* @var string $openingHours */
      private $openingHours;

      /* @var string $availabilityRestriction */
      private $hoursAvailable;

      /* @var string $isTypeOf */
      private $isTypeOf;

      public function __construct() {
         $this->isTypeOf = "Channel";
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
      public function setIdentifier($identifier){
        $this->identifier = $identifier;
      }


      public function getOwnedBy() {
         return $this->ownedBy;
      }

      public function setOwnedBy($ownedBy){
         $this->ownedBy = $ownedBy;
      }

      public function getType(){
        return $this->type;
      }
   
      public function setType($type) {
        $this->type = $type;
      }

      public function getHasInput() {
         return $this->hasInput;
      }

      public function setHasInput($hasInput){
         $this->hasInput = $hasInput;
      }

      public function getOpeningHours() {
          return $this->openingHours;
      }

      public function setOpeningHours($openingHours){
          $this->openingHours = $openingHours;
      }

      public function getHoursAvailable() {
          return $this->hoursAvailable;
      }

      public function setHoursAvailable($hoursAvailable){
         $this->hoursAvailable = $hoursAvailable;
       }

      public function jsonSerialize(){
        return array_filter(get_object_vars($this));
      }
}

