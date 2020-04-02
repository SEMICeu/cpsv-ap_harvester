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

      /* @var string $identifier */
      private $identifier;

      /* @var string $email */
      private $email;
     
      /* @var string $telephone */
      private $telephone;

      /* @var string $fax; */
      private $fax;
      
      /* @var string $hours available */
      private $hoursAvailable;
 
      /* @var string $isTypeOf */
      private $isTypeOf;

      public function __construct() {
        $this->isTypeOf = "ContactPoint";
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


      public function getEmail() {
         return $this->email;
      }

      public function setEmail($email){
         $this->email = $email;
      }

      public function getTelephone() {
         return $this->telephone;
      }

      public function setTelephone($telephone){
         $this->telephone = $telephone;
      }

      public function getFax() {
         return $this->fax;
     }

      public function setFax($fax){
         $this->fax = $fax;
      }

      public function getHoursAvailable() {
         return $this->hoursAvailable;
      }

      public function setHoursAvailable($hoursavailable){
         $this->hoursAvailable = $hoursavailable;
      }

     public function jsonSerialize(){
         return array_filter(get_object_vars($this));
      }
}

