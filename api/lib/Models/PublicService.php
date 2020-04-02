<?php
/*
 * PublicService
 */
namespace Models;
use JsonSerializable;

/*
 * PublicService
 */
class PublicService implements JsonSerializable {
    /* @var string $id  */
    private $id;

    /* @var string $title  */
    private $title;

    /* @var string $description  */
    private $description;

    /* @var string $competentAuthorityURI  */
    private $competentAuthorityURI;

    /* @var string $evidenceURI */
    private $evidenceURI;

    /* @var string $channelURI */
    private $channelURI;

    /* @var string $sectorURI */
    private $sectorURI;

    /* @var string $typeURI */
    private $typeURI;

    /* @var string $languageURI */
    private $languageURI;

    /* @var string $contactPointURI */
    private $contactPointURI;

    /* @var string $eventURI */
    private $eventURI;

    public function getId(){
      return $this->id;
    }

    public function setId($id){
      $this->id  = $id;
    }

    public function getTitle(){
      return $this->title;
    }

    public function setTitle($title){
      $this->title = $title;
    }

    public function getDescription(){
      return $this->description;
    }
 
    public function setDescription($description){
      $this->description = $description;
    }

    public function getCompetentAuthorityURI(){
      return $this->competentAuthorityURI;
    }

    public function setCompetentAuthorityURI($competentAuthorityURI){
      $this->competentAuthorityURI = $competentAuthorityURI;
    }
    

    public function getEvidenceURI(){
       return $this->evidenceURI;
    }

    public function setEvidenceURI($evidenceURI){
      $this->evidenceURI = $evidenceURI;
    }


    public function getChannelURI(){
	return $this->channelURI;
    }

    public function setChannelURI($channelURI){
      $this->channelURI = $channelURI;
    }

 
    public function getSectorURI() {
      return $this->sectorURI;
    }

    public function setSectorURI($sectorURI){
      $this->sectorURI = $sectorURI;
    }

    public function getTypeURI(){
      return $this->typeURI;
    }

    public function setTypeURI($typeURI){
      $this->typeURI = $typeURI;
    }

    public function getLanguageURI(){
      return $this->languageURI;
    }

    public function setLanguageURI($languageURI){
      $this->languageURI = $languageURI;
    }

    public function getContactPointURI(){
      return $this->contactPointURI;
    }

    public function setContactPointURI($contactPointURI){
      $this->contactPointURI = $contactPointURI;
    }

    public function getEventURI(){
       return $this->eventURI;
    }

    public function setEventURI($eventURI){
      $this->eventURI = $eventURI;
   }

    public function jsonSerialize(){
      return array_filter(get_object_vars($this));
    }
}
