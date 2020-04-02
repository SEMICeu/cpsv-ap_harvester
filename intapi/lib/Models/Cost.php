<?php
namespace Models;
use JsonSerializable;
/*
* Cost
 */
class Cost implements JsonSerializable {
     /* @var string $id  */
     private $id;
     /* @var string $description */
     private $description;
     /* @var string $value */
     private $value;
     /* @var string $currency */
     private $currency;

     /* @var string ifAccessedThrough */
     private $ifAccessedThrough;

     /* @var string isDefinedBy */
     private $isDefinedBy;

     public function __construct() {
        $this->isTypeOf = "Cost";
     }
     public function getId() {
        return $this->id;
     }
     public function setId($id){
        $this->id = $id;
     }
     public function getIdentifier(){
        return $this->identifer;
     }
     public function setIdentifier($identifier){
        $this->identifier = $identifier;
     }
     public function getDescription() {
        return $this->description;
     }
     public function setDescription($description) {
        $this->description = $description;
     }
     public function getValue() {
        return $this->value;
     }
     public function setValue($value) {
        $this->value = $value;
     }
     public function getCurrency() {
        $this->currency;
     }
     public function setCurrency($currency) {
        $this->currency = $currency;
     }

     public function getIfAccessedThrough() {
        $this->ifAccessedThrough;
     }
     public function setIfAccessedThrough($ifAccessedThrough) {
        $this->ifAccessedThrough = $ifAccessedThrough;
     }

     public function getIsDefinedBy() {
        $this->isDefinedBy;
     }
     public function setIsDefinedBy($isDefinedBy) {
        $this->isDefinedBy = $isDefinedBy;
     }






     public function jsonSerialize(){
        return array_filter(get_object_vars($this));
     }
}
