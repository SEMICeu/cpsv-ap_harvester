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

    /* @var string $isTypeOf */
    private $isTypeOf;

    /* @var string $id  */
    private $id;

    /* @var string $title  */
    private $title;

    /* @var string $description  */
    private $description;

    /* @var string $hasCompetentAuthority  */
    private $hasCompetentAuthority;

   /* @var keyword $keyword */
   private $keyword;

    /* @var string $sector */
    private $sector;

    /* @var string $thematicArea */
    private $thematicArea;

    /* @var string $type */
    private $type;

    /* @var string $language */
    private $language;

    /* @var string $status */
    private $status;

    /* @var string $isGroupedBy */
    private $isGroupedBy;

    /* @var string $requires */
    private $requires;

    /* @var string $related */
    private $related;

    /* @var string $hasCriterion */
    private $hasCriterion;

    /* @var string $hasParticipation */
    private $hasParticipation;

    /* @var string $hasInput */
    private $hasInput;

    /* @var string $hasLegalResource */
    private $hasLegalResource;

    /* @var string $produces */
    private $produces;

    /* @var string $follows */ 
    private $follows;

    /* @var string $spatial */
    private $spatial;

    /* @var string $hasChannel */
    private $hasChannel;

    /* @var string $hasContactPoint */
    private $hasContactPoint;

    /* @var string $processingTime */
    private $processingTime;

    /* @var string $hasCost */
    private $hasCost;

    /* @var string $isDescribedAt */
    private $isDescribedAt;

    /* @var string $isClassifiedBy */ 
    private $isClassifiedBy;


    public function __construct() {
       $this->isTypeOf = "PublicService";
        // allocate your stuff
    }


    public function getId(){
      return $this->id;
    }

    public function setId($id){
      $this->id  = $id;
    }

     public function getIdentifier(){
       return $this->identifier;
     }
    public function setIdentifier($identifier){
       $this->identifier  = $identifier;
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
      return $this->hasCompetentAuthority;
    }

    public function setCompetentAuthorityURI($competentAuthorityURI){
      $this->hasCompetentAuthority = $competentAuthorityURI;
    }

    public function getKeyword(){
      return $this->keyword;
    }

    public function setKeyword($keyword){
      $this->keyword = $keyword;
    }

  
    public function getThematicArea(){
      return $this->thematicArea;
    }

    public function setThematicArea($thematicArea){
      $this->thematicArea = $thematicArea;
    }
 
    public function getSector() {
      return $this->sector;
    }

    public function setSector($sector){
      $this->sector = $sector;
    }

    public function getType(){
      return $this->type;
    }

    public function setType($type){
      $this->type = $type;
    }

    public function getLanguage(){
      return $this->language;
    }

    public function setLanguage($language){
      $this->language = $language;
    }

     public function getStatus(){
       return $this->status;
     }

     public function setStatus($status){
       $this->status = $status;
     }

    public function getIsGroupedBy(){
       return $this->isGroupedBy;
     }

     public function setIsGroupedBy($isGroupedBy){
       $this->isGroupedBy = $isGroupedBy;
     }

     public function getRequires(){
       return $this->requires;
     }

     public function setRequires($requires){
       $this->requires = $requires;
     }

     public function getRelated(){
       return $this->related;
     }

     public function setRelated($related){
       $this->related = $related;
     }

     public function getHasCriterion(){
       return $this->hasCriterion;
     }

     public function setHasCriterion($hasCriterion){
       $this->hasCriterion = $hasCriterion;
     }

     public function getHasParticipation(){
        return $this->hasParticipation;
      }

      public function setHasParticipation($hasParticipation){
        $this->hasParticipation = $hasParticipation;
      }

      public function getHasInput(){
        return $this->hasInput;
      }

      public function setHasInput($hasInput){
        $this->hasInput = $hasInput;
      }

      public function getHasLegalResource(){
        return $this->hasLegalResource;
      }

      public function setHasLegalResource($hasLegalResource){
        $this->hasLegalResource = $hasLegalResource;
      }

      public function getProduces(){
         return $this->produces;
       }

       public function setProduces($produces){
         $this->produces = $produces;
       }

      public function getFollows(){
         return $this->follows;
       }

       public function setFollows($follows){
         $this->follows = $follows;
       }

       public function getSpatial(){
        return $this->spatial;
       }

       public function setSpatial($spatial){
         $this->spatial = $spatial;
       }
 

    public function getHasContactPoint(){
      return $this->hasContactPoint;
    }

    public function setHasContactPoint($hasContactPoint){
      $this->hasContactPoint = $hasContactPoint;
    }


     public function getHasChannel(){
       return $this->hasChannel;
     }

     public function setHasChannel($hasChannel){
       $this->hasChannel = $hasChannel;
     }

      public function getProcessingTime(){
        return $this->processingTime;
      }

      public function setProcessingTime($processingTime){
        $this->processingtime = $processingTime;
      }

      public function gethasCost(){
        return $this->hasCost;
      }

      public function setHasCost($hasCost){
        $this->hasCost = $hasCost;
      }

      public function getIsDescribedAt(){
        return $this->isDescribedAt;
      }

      public function setIsDescribedAt($isDescribedAt){
        $this->isDescribedAt = $isDescribedAt;
      }

      public function getIsClassifiedBy(){
        return $this->isClassifiedBy;
      }

     public function setIsClassifiedBy($isClassifiedBy){
        $this->isClassifiedBy = $isClassifiedBy;
      }


    public function jsonSerialize(){
      return array_filter(get_object_vars($this));
    }
}
