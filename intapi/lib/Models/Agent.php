<?php
/*
 * Agent
 */
namespace Models;
use JsonSerializable;

class Agent implements JsonSerializable {
      /* @var string $id  */
      private $id;
      /* @var string $identifier */
      private $identifier;
      /* @var string $name */
      private $name;
      /* @var string $playsRole */
      private $playsRole;
      /* @var string $hasAddress */
      private $hasAddress;

     /* @var string $isTypeOf */
     private $isTypeOf;

    public function __construct() {
        $this->isTypeOf = "Agent";
         // allocate your stuff
     }
      public function getId(){
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
      public function getName(){
        return $this->name;
      }
      public function setName($name){
        $this->name = $name;
      }
      public function getPlaysRole() {
        $this->playsRole;
      }
      public function setPlaysRole($playsRole){
        $this->playsRole = $playsRole;
      }

      public function getHasAddress(){
        $this->hasAddress;
      }
      public function setHasAddress($hasAddress){
        $this->hasAddress = $hasAddress;
      }

      public function jsonSerialize(){
        return array_filter(get_object_vars($this));
      }
}

