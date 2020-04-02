<?php
/**
 * Harvester API
 * @version 1.0.0
 */
use Models\Location as Location;
use Models\PublicOrganization as PO;
use Models\PublicService as PS;
use Models\Evidence as Evidence;
use Models\Channel as Channel;
use Models\Concept as Concept;
use Models\LinguisticSystem as LinguisticSystem;
use Models\ContactPoint as ContactPoint;
use Models\BusinessEvent as BusinessEvent;
use Models\LifeEvent as LifeEvent;
use Models\CriterionRequirement as CriterionRequirement;
use Models\LegalResource as LegalResource;
use Models\Output as Output;
use Models\Cost as Cost;
use Models\PublicServiceDataset as PublicServiceDataset;
use Models\Agent as Agent;

use GuzzleHttp\Client;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as Xlsx;
use ML\JsonLD\JsonLD;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Slim\App();

function harvester_get_node($url){
     $client = new Client();
     $res = $client->request('GET', $url);
     return $res;
}


 /**
  * GET getBusinessEventByURI
  * Summary: Find BusinessEvent by URI
  * Notes: returns a single BusinessEvent
  * Output-Formats: [application/json]
 */
$app->GET('/v1/businessEventByURI', function($request, $response, $args) {
 
                $queryParams = $request->getQueryParams();
                $businessEventURI = $queryParams['businessEventURI'];

                $endpoint = 'http://35.181.155.22/sparql?default-graph-uri=http%3A%2F%2F35.181.155.22%2Fpilot&query=';
                $query = 'PREFIX dct:  <http://purl.org/dc/terms/> PREFIX cv: <http://data.europa.eu/m8g/>' .
                         'CONSTRUCT { ' .
                         '?s dct:identifier ?identifier . ' .
                         '?s dct:title ?title . ' .
                         '}' .
                         'WHERE { '.
                         '?s rdf:type cv:BusinessEvent . ' .
                         '?s dct:identifier ?identifier . ' .
                         '?s dct:title ?title . ' .
                         'FILTER(?s = <' . $businessEventURI . '>) ' .
                         '}' ;

                $output = '&format=application%2Fx-json%2Bld%2Bctx';
                $url = $endpoint . urlencode($query) . $output;
                $res = harvester_get_node($url)->getBody();
                if(empty(json_decode($res, true))) {
                   $response = $response->withStatus(404);
                } else {
                $identifier = json_decode($res)->{'@graph'}[0]->identifier;
                $title = json_decode($res)->{'@graph'}[0]->title;


                $be = new BusinessEvent;
                $be->setId($identifier);
                $be->setTitle($title);

                $message= json_encode($be);
                
                $response = $response->write($message)->withStatus(200);
                }
                return $response;
 });

/**
 * GET importSpreadSheetFromURL
 * Summary: Import a CPSV-AP 2.2.1 compliant spreadsheet by URL
 * Notes: Returns a public service list
 * Output-Formats: [application/json]
 */
$app->GET('/v1/importSpreadsheetFromURL', function($request, $response, $args) {

          $queryParams = $request->getQueryParams();
          $spreadsheetURL = $queryParams['spreadsheetURL'];
          
          $file_path = __DIR__.'/download/';
          $file_name = "data.xlsx";

          try {
             if (download($spreadsheetURL, $file_path, $file_name)['response_code'] == '200') {

                 $reader = new Xlsx;
                 $reader->setReadDataOnly(true);
                 $spreadsheet = $reader->load($file_path . $file_name);
/*
                 $file = file_get_contents(__DIR__ . '/rdf/CPSV-AP_v2.2.1.jsonld');
                 $context =  json_decode($file, true)['@context'];
*/


                 $cpsvap_array = array();


                 $spreadsheet->setActiveSheetIndexByName('Public Service');
                 $worksheet = $spreadsheet->getActiveSheet();
                 $highestRow = $worksheet->getHighestRow();


                 for ($x = 2; $x <= $highestRow; $x++){
                        $ps_uri = $worksheet->getCell('A' . $x)->getValue();
                        $ps_name =  $worksheet->getCell('B' . $x)->getValue();
                        $ps_identifier = $worksheet->getCell('A' . $x)->getValue();
			$ps_description = $worksheet->getCell('C' . $x)->getValue();
                        $ps_ca = $worksheet->getCell('D' . $x)->getValue();

                        $ps_type = $worksheet->getCell('H' . $x)->getValue();
                        if (!empty($ps_type)){
                           $ps_type_array = array_map('trim', explode(";",$ps_type));}


                        $ps_isGroupedBy = $worksheet->getCell('K' . $x)->getValue();
                        if (!empty($ps_isGroupedBy)){
                        $ps_isGroupedBy_array = array_map('trim', explode(";",$ps_isGroupedBy));}


                         $ps_hasCriterion = $worksheet->getCell('N' . $x)->getValue();
                         if (!empty($ps_hasCriterion)){
                         $ps_hasCriterion_array = array_map('trim', explode(";",$ps_hasCriterion));
                         }


                         $ps_isClassifiedBy = $worksheet->getCell('Z' . $x)->getValue();
                         if (!empty($ps_isClassifiedBy)){
                         $ps_isClassifiedBy_array = array_map('trim', explode(";",$ps_isClassifiedBy));
                         }

                        $ps_language = $worksheet->getCell('I' . $x)->getValue();
                        if (!empty($ps_language)){
                        $ps_language_array = array_map('trim', explode(";",$ps_language));}


                         $ps_hasInput = $worksheet->getCell('P' . $x)->getValue();
                         if (!empty($ps_hasInput)){
                         $ps_hasInput_array = array_map('trim', explode(";",$ps_hasInput));
                         }

                         $ps_hasLegalResource = $worksheet->getCell('Q' . $x)->getValue();
                         if (!empty($ps_hasLegalResource)){
                         $ps_hasLegalResource_array = array_map('trim', explode(";",$ps_hasLegalResource));
                         }

                         $ps_produces = $worksheet->getCell('R' . $x)->getValue();
                         if (!empty($ps_produces)){
                         $ps_produces_array = array_map('trim', explode(";",$ps_produces));
                         }

                         $ps_hasContactPoint = $worksheet->getCell('U' . $x)->getValue();
                         if (!empty($ps_hasContactPoint)){
                         $ps_hasContactPoint_array = array_map('trim', explode(";",$ps_hasContactPoint));
                         }

                         $ps_hasChannel = $worksheet->getCell('V' . $x)->getValue();
                         if (!empty($ps_hasChannel)){
                         $ps_hasChannel_array = array_map('trim', explode(";",$ps_hasChannel));
                         }

                         $ps_hasCost = $worksheet->getCell('X' . $x)->getValue();
                         if (!empty($ps_hasCost)){
                         $ps_hasCost_array = array_map('trim', explode(";",$ps_hasCost));
                         }

                         $ps_isDescribedAt = $worksheet->getCell('Y' . $x)->getValue();
                         if (!empty($ps_isDescribedAt)){
                         $ps_isDescribedAt_array = array_map('trim', explode(";",$ps_isDescribedAt));
                         }
			
/*			
			$ps_key = $worksheet->getCell('E' . $x)->getValue();
			if (!empty($ps_key)){
			$ps_key_array = array_map('trim', explode(";",$ps_key));}

                        $ps_sector = $worksheet->getCell('F' . $x)->getValue();
			if (!empty($ps_sector)){
                        $ps_sector_array = array_map('trim', explode(";",$ps_sector));}

                        $ps_thematicArea = $worksheet->getCell('G' . $x)->getValue();
			if (!empty($ps_thematicArea)){
                        $ps_thematicArea_array = array_map('trim', explode(";",$ps_thematicArea));}

                        $ps_type = $worksheet->getCell('H' . $x)->getValue();
			if (!empty($ps_type)){
                        $ps_type_array = array_map('trim', explode(";",$ps_type));}

                        $ps_status = $worksheet->getCell('J' . $x)->getValue();
                        
                        $ps_requires = $worksheet->getCell('L' . $x)->getValue();
			if (!empty($ps_requires)){
                        $ps_requires_array = array_map('trim', explode(";",$ps_requires));
			}

                        $ps_related = $worksheet->getCell('M' . $x)->getValue();
                        if (!empty($ps_related)){
                        $ps_related_array = array_map('trim', explode(";",$ps_related));
                        }


                         $ps_hasParticipation = $worksheet->getCell('O' . $x)->getValue();
                         if (!empty($ps_hasParticipation)){
                         $ps_hasParticipation_array = array_map('trim', explode(";",$ps_hasParticipation));
                         }

                         $ps_follows = $worksheet->getCell('S' . $x)->getValue();
                         if (!empty($ps_follows)){
                         $ps_follows_array = array_map('trim', explode(";",$ps_follows));
                         }

                         $ps_spatial = $worksheet->getCell('T' . $x)->getValue();
                         if (!empty($ps_spatial)){
                         $ps_spatial_array = array_map('trim', explode(";",$ps_spatial));
                         }                                                                          


                         $ps_processingTime = $worksheet->getCell('V' . $x)->getValue();
                      
*/			

                        $ps = new PS;
                        $ps->setId($ps_uri);
                        $ps->setTitle($ps_name);
			$ps->setIdentifier($ps_identifier);
                        $ps->setDescription($ps_description);
                        $ps->setCompetentAuthorityURI($ps_ca);

                        $ps->setType($ps_type_array); //Type
			$ps->setIsGroupedBy($ps_isGroupedBy_array); // Business event
			$ps->setHasCriterion($ps_hasCriterion_array); // Criterion requirement
			$ps->setIsClassifiedBy($ps_isClassifiedBy_array); // Concept
                        $ps->setLanguage($ps_language_array); // Linguistic System
			$ps->setHasInput($ps_hasInput_array); // Evidence
 			$ps->setHasLegalResource($ps_hasLegalResource_array); // Legal Resource
			$ps->setProduces($ps_produces_array); // Output
			$ps->setHasContactPoint($ps_hasContactPoint_array); // Contact Point
			$ps->setHasChannel($ps_hasChannel_array); // Channel
			$ps->setHasCost($ps_hasCost_array); // Cost
			$ps->setIsDescribedAt($ps_isDescribedAt_array); // Public Service Dataset
/*
			$ps->setKeyword($ps_key_array);
			$ps->setSector($ps_sector_array);
			$ps->setThematicArea($ps_thematicArea_array);
			$ps->setType($ps_type_array);
			$ps->setLanguage($ps_language_array);
			$ps->setStatus($ps_status);
			
			$ps->setRequires($ps_requires_array);
			$ps->setRelated($ps_related_array);
			$ps->setHasParticipation($ps_hasParticipation_array);
			$ps->setFollows($ps_follows_array);
			$ps->setSpatial($ps_spatial_array);
			$ps->setProcessingTime($ps_processingTime);
*/
                  /* 
                        $ops_enc = json_encode($ps);
                        $ops = json_decode($ops_enc,true);
                        $ops['isTypeOf'] = "PublicService";
                  */
                        $cpsvap_array[] = $ps;
                 }



/*
                 $ps_uri = $worksheet->getCell('A2')->getValue();
                 $ps_name =  $worksheet->getCell('B2')->getValue();
                 $ps_description = $worksheet->getCell('C2')->getValue();
                 $ps_ca = $worksheet->getCell('D2')->getValue();
             
                 $ps = new PS;
                 $ps->setId($ps_uri);
                 $ps->setTitle($ps_name);
                 $ps->setDescription($ps_description);
                 $ps->setCompetentAuthorityURI($ps_ca);
                
                 $ps1 = json_encode($ps);
                 $ops1 = json_decode($ps1,true);
                 $ops1['@type'] = "http://data.europa.eu/m8g/PublicService";
*/

                 $spreadsheet->setActiveSheetIndexByName('Public Organisation');
                 $worksheet = $spreadsheet->getActiveSheet();
                 $highestRow = $worksheet->getHighestRow();

 
		 for ($x = 2; $x <= $highestRow; $x++){
                        $po_id = $worksheet->getCell('A' . $x)->getValue();
                         $po_title = $worksheet->getCell('B' . $x)->getValue();
			$po_prefLabel =  $worksheet->getCell('C' . $x)->getValue();
			$po_spatial = $worksheet->getCell('D' . $x)->getValue();

                 $po =  new PO;
                 $po->setId($po_id);
		$po->setTitle($po_title);
                 $po->setPrefLabel($po_prefLabel);
		 $po->setSpatial($po_spatial);
		 

                 array_push($cpsvap_array,$po);


		 }


		// LOCATION
                 $spreadsheet->setActiveSheetIndexByName('Location');
                 $worksheet = $spreadsheet->getActiveSheet();
                 $highestRow = $worksheet->getHighestRow();

                 for ($x = 2; $x <= $highestRow; $x++){
                        $location_id = $worksheet->getCell('A' . $x)->getValue();
                        $location_title =  $worksheet->getCell('B' . $x)->getValue();
                       
                        $location =  new Location;
                        $location->setId($location_id);
                        $location->setTitle($location_title);
              
                        array_push($cpsvap_array,$location);
                 }

		// CONCEPT
                  $spreadsheet->setActiveSheetIndexByName('Concept');
                  $worksheet = $spreadsheet->getActiveSheet();
                  $highestRow = $worksheet->getHighestRow();


                  for ($x = 2; $x <= $highestRow; $x++){
                         $concept_id = $worksheet->getCell('A' . $x)->getValue();
                         $concept_prefLabel =  $worksheet->getCell('B' . $x)->getValue();

                         $concept =  new Concept;
                         $concept->setId($concept_id);
                         $concept->setPrefLabel($concept_prefLabel);

                         array_push($cpsvap_array,$concept);
		  }

		// LINGUISTICSYSTEM
                   $spreadsheet->setActiveSheetIndexByName('LinguisticSystem');
                   $worksheet = $spreadsheet->getActiveSheet();
                   $highestRow = $worksheet->getHighestRow();


                   for ($x = 2; $x <= $highestRow; $x++){
                          $linguisticSystem_id = $worksheet->getCell('A' . $x)->getValue();
                          $linguisticSystem_prefLabel =  $worksheet->getCell('B' . $x)->getValue();

                          $linguisticSystem =  new LinguisticSystem;
                          $linguisticSystem->setId($linguisticSystem_id);
                          $linguisticSystem->setPrefLabel($linguisticSystem_prefLabel);
 
                          array_push($cpsvap_array,$linguisticSystem);
                   }


                   // BUSINESS EVENT
                   $spreadsheet->setActiveSheetIndexByName('Business Event');
                   $worksheet = $spreadsheet->getActiveSheet();
                   $highestRow = $worksheet->getHighestRow();

                   for ($x = 2; $x <= $highestRow; $x++){
                       $be_id = $worksheet->getCell('A' . $x)->getValue();
                       $be_identifier =  $worksheet->getCell('B' . $x)->getValue();
                       $be_name =  $worksheet->getCell('C' . $x)->getValue();
                       $be_description = $worksheet->getCell('D' . $x)->getValue();
                       $be_type = $worksheet->getCell('E' . $x)->getValue();
                       $be_related = $worksheet->getCell('F' . $x)->getValue();

                       if (!empty($be_type)){
                        $be_type_array = array_map('trim', explode(";",$be_type));
                       }

                       if (!empty($be_related)){
                           $be_related_array = array_map('trim', explode(";",$be_related));
                       }

               
                       $be =  new BusinessEvent;
                       $be->setId($be_id);
                       $be->setIdentifier($be_identifier);
                       $be->setTitle($be_name);
                       $be->setDescription($be_description);
                       $be->setType($be_type_array);
                       $be->setRelated($be_related_array);

                       array_push($cpsvap_array,$be);
                    }

                    // LIFE EVENT
                    $spreadsheet->setActiveSheetIndexByName('Life Event');
                    $worksheet = $spreadsheet->getActiveSheet();
                    $highestRow = $worksheet->getHighestRow();

                    for ($x = 2; $x <= $highestRow; $x++){
                        $le_id = $worksheet->getCell('A' . $x)->getValue();
                        $le_identifier =  $worksheet->getCell('B' . $x)->getValue();
                        $le_name =  $worksheet->getCell('C' . $x)->getValue();
                        $le_description = $worksheet->getCell('D' . $x)->getValue();
                        $le_type = $worksheet->getCell('E' . $x)->getValue();
                        $le_related = $worksheet->getCell('F' . $x)->getValue();

                        if (!empty($be_type)){
                         $le_type_array = array_map('trim', explode(";",$le_type));
                        }

                        if (!empty($be_related)){
                            $le_related_array = array_map('trim', explode(";",$le_related));
                        }


                        $le =  new LifeEvent;
                        $le->setId($le_id);
                        $le->setIdentifier($le_identifier);
                        $le->setTitle($le_name);
                        $le->setDescription($le_description);
                        $le->setType($le_type_array);
                        $le->setRelated($le_related_array);

                        array_push($cpsvap_array,$le);
                     }


                    // CRITERION REQUIREMENT
                    $spreadsheet->setActiveSheetIndexByName('Criterion Requirement');
                    $worksheet = $spreadsheet->getActiveSheet();
                    $highestRow = $worksheet->getHighestRow();

                    for ($x = 2; $x <= $highestRow; $x++){
                        $cr_id = $worksheet->getCell('A' . $x)->getValue();
                        $cr_identifier =  $worksheet->getCell('B' . $x)->getValue();
                        $cr_name =  $worksheet->getCell('C' . $x)->getValue();
                        $cr_type = $worksheet->getCell('D' . $x)->getValue();

                        if (!empty($cr_type)){
                           $cr_type_array = array_map('trim', explode(";",$cr_type));
                        }


                        $cr =  new CriterionRequirement;
                        $cr->setId($cr_id);
                        $cr->setIdentifier($cr_identifier);
                        $cr->setTitle($cr_name);
                        $cr->setType($cr_type_array);
       
                        array_push($cpsvap_array,$cr);
                   }

                  // EVIDENCE
                  $spreadsheet->setActiveSheetIndexByName('Evidence');
                  $worksheet = $spreadsheet->getActiveSheet();
                  $highestRow = $worksheet->getHighestRow();

                 for ($x = 2; $x <= $highestRow; $x++){
                     $ev_id = $worksheet->getCell('A' . $x)->getValue();
                     $ev_identifier =  $worksheet->getCell('B' . $x)->getValue();
                     $ev_name =  $worksheet->getCell('C' . $x)->getValue();
                     $ev_description = $worksheet->getCell('D' . $x)->getValue();
                     $ev_type = $worksheet->getCell('E' . $x)->getValue();
                     $ev_related = $worksheet->getCell('F' . $x)->getValue();
                     $ev_language =  $worksheet->getCell('G' . $x)->getValue();

                      if (!empty($ev_language)){
                          $ev_language_array = array_map('trim', explode(";",$ev_language));
                      }

                      if (!empty($ev_related)){
                          $ev_related_array = array_map('trim', explode(";",$ev_related));
                      }

                      $ev =  new Evidence;
                      $ev->setId($ev_id);
                      $ev->setIdentifier($ev_identifier);
                      $ev->setTitle($ev_name);
                      $ev->setDescription($ev_description);
                      $ev->setType($ev_type);
                      $ev->setRelated($ev_related_array);
                      $ev->setLanguage($ev_language_array);

                      array_push($cpsvap_array,$ev);

                 }



                   // LEGAL RESOURCE
                   $spreadsheet->setActiveSheetIndexByName('Legal Resource');
                   $worksheet = $spreadsheet->getActiveSheet();
                   $highestRow = $worksheet->getHighestRow();

                    for ($x = 2; $x <= $highestRow; $x++){
                        $lr_id = $worksheet->getCell('A' . $x)->getValue();
			$lr_identifier = $worksheet->getCell('B' . $x)->getValue();
                        $lr_description = $worksheet->getCell('C' . $x)->getValue();
                        $lr_related = $worksheet->getCell('D' . $x)->getValue();

                        if (!empty($lr_related)){
                            $lr_related_array = array_map('trim', explode(";",$lr_related));
                        }


                        $lr =  new LegalResource;
                        $lr->setId($lr_id);
                        $lr->setDescription($lr_description);
			$lr->setIdentifier($lr_identifier);
                        $lr->setRelated($lr_related_array);

                        array_push($cpsvap_array,$lr);
                 }


                // OUTPUT
                $spreadsheet->setActiveSheetIndexByName('Output');
                $worksheet = $spreadsheet->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();

                for ($x = 2; $x <= $highestRow; $x++){
                    $output_id = $worksheet->getCell('A' . $x)->getValue();
                    $output_identifier =  $worksheet->getCell('B' . $x)->getValue();
                    $output_name =  $worksheet->getCell('C' . $x)->getValue();
                    $output_description = $worksheet->getCell('D' . $x)->getValue();
                    $output_type = $worksheet->getCell('E' . $x)->getValue();

                    if (!empty($output_type)){
                        $output_type_array = array_map('trim', explode(";",$output_type));
                    }

                    $output =  new Output;
                    $output->setId($output_id);
                    $output->setIdentifier($output_identifier);
                    $output->setTitle($output_name);
                    $output->setDescription($output_description);
                    $output->setType($output_type_array);

                    array_push($cpsvap_array,$output);
                }


                // CONTACT POINT
                $spreadsheet->setActiveSheetIndexByName('Contact Point');
                $worksheet = $spreadsheet->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();

                for ($x = 2; $x <= $highestRow; $x++){
                    $cp_id = $worksheet->getCell('A' . $x)->getValue();
		    $cp_identifier = $worksheet->getCell('B' .$x)->getValue();
                    $cp_email =  $worksheet->getCell('C' . $x)->getValue();
                    $cp_telephone =  $worksheet->getCell('D' . $x)->getValue();
                    $cp_hours_available = $worksheet->getCell('E' . $x)->getValue();
                    $cp_fax = $worksheet->getCell('F' . $x)->getValue();


                     if (!empty($cp_telephone)){
                         $cp_telephone_array = array_map('trim', explode(";",$cp_telephone));
                     }

                     $cp =  new ContactPoint;
                     $cp->setId($cp_id);
		     $cp->setIdentifier($cp_identifier);
                     $cp->setEmail($cp_email);
                     $cp->setTelephone($cp_telephone_array);
                     $cp->setHoursAvailable($cp_hours_available);
                     $cp->setFax($cp_fax);

                     array_push($cpsvap_array,$cp);
                 }



                 // CHANNEL
                 $spreadsheet->setActiveSheetIndexByName("Channel");
                 $worksheet = $spreadsheet->getActiveSheet();
                 $highestRow = $worksheet->getHighestRow();

                 for ($x = 2; $x <= $highestRow; $x++){
                     $ch_id = $worksheet->getCell('A' . $x)->getValue();
		     $ch_identifier = $worksheet->getCell('B' . $x)->getValue();
                     $ch_ownedby =  $worksheet->getCell('C' . $x)->getValue();
                     $ch_type =  $worksheet->getCell('D' . $x)->getValue();
                     $ch_has_input = $worksheet->getCell('E' . $x)->getValue();
                     $ch_opening_hours = $worksheet->getCell('F' . $x)->getValue();
                     $ch_hours_available = $worksheet->getCell('G' . $x)->getValue();


                     if (!empty($ch_ownedby)){
                          $ch_ownedby_array = array_map('trim', explode(";",$ch_ownedby));
                     }

                     if (!empty($ch_opening_hours)){
                           $ch_opening_hours_array = array_map('trim', explode(";",$ch_opening_hours));
                     }


                     $ch =  new Channel;
                     $ch->setId($ch_id);
		     $ch->setIdentifier($ch_identifier);
                     $ch->setOwnedBy($ch_ownedby_array);
                     $ch->setType($ch_type);
                     $ch->setHasInput($ch_has_input_array);
                     $ch->setOpeningHours($ch_opening_hours_array);
                     $ch->setHoursAvailable($ch_hours_available);

                     array_push($cpsvap_array,$ch);
                }



                  // COST
                  $spreadsheet->setActiveSheetIndexByName("Cost");
                  $worksheet = $spreadsheet->getActiveSheet();
                  $highestRow = $worksheet->getHighestRow();

                  for ($x = 2; $x <= $highestRow; $x++){
                      $cost_id = $worksheet->getCell('A' . $x)->getValue();
                      $cost_identifier = $worksheet->getCell('B' .$x)->getValue();
                      $cost_value =  $worksheet->getCell('C' . $x)->getValue();
                      $cost_currency =  $worksheet->getCell('D' . $x)->getValue();
                      $cost_description = $worksheet->getCell('E' . $x)->getValue();
                      $cost_is_defined_by = $worksheet->getCell('F' . $x)->getValue();
                      $cost_if_accessed_through = $worksheet->getCell('G' . $x)->getValue();


                      if (!empty($cost_is_defined_by)){
                           $cost_is_defined_by_array = array_map('trim', explode(";",$cost_is_defined_by));
                      }


                      $cost =  new Cost;
                      $cost->setId($cost_id);
		      $cost->setIdentifier($cost_identifier);
                      $cost->setValue($cost_value);
                      $cost->setCurrency($cost_currency);
                      $cost->setDescription($cost_description);
                      $cost->setIsDefinedBy($cost_is_defined_by_array);
                      $cost->setIfAccessedThrough($cost_if_accessed_through);

                      array_push($cpsvap_array,$cost);
                 }

		// PUBLICSERVICEDATASET
                   $spreadsheet->setActiveSheetIndexByName("PS Dataset");
                   $worksheet = $spreadsheet->getActiveSheet();
                   $highestRow = $worksheet->getHighestRow();

                   for ($x = 2; $x <= $highestRow; $x++){
                       $ps_dataset_id = $worksheet->getCell('A' . $x)->getValue();
                       $ps_dataset_identifier = $worksheet->getCell('B' .$x)->getValue();
                       $ps_dataset_publisher =  $worksheet->getCell('C' . $x)->getValue();
                       $ps_dataset_title =  $worksheet->getCell('D' . $x)->getValue();
                       $ps_dataset_landingPage = $worksheet->getCell('E' . $x)->getValue();
             

                       if (!empty($ps_dataset_title)){
                            $ps_dataset_title_array = array_map('trim', explode(";",$ps_dataset_title));
                       }

                       if (!empty($ps_dataset_landingPage)){
                            $ps_dataset_landingPage_array = array_map('trim', explode(";",$ps_dataset_landingPage));
                       }

                       $ps_dataset =  new PublicServiceDataset;
                       $ps_dataset->setId($ps_dataset_id);
                       $ps_dataset->setIdentifier($ps_dataset_identifier);
                       $ps_dataset->setPublisher($ps_dataset_publisher);
                       $ps_dataset->setTitle($ps_dataset_title);
                       $ps_dataset->setLandingPage($ps_dataset_landingPage);


                    array_push($cpsvap_array,$ps_dataset);

 		   }

		// AGENT
                    $spreadsheet->setActiveSheetIndexByName("Agent");
                    $worksheet = $spreadsheet->getActiveSheet();
                    $highestRow = $worksheet->getHighestRow();

                    for ($x = 2; $x <= $highestRow; $x++){
                        $agent_id = $worksheet->getCell('A' . $x)->getValue();
                        $agent_identifier = $worksheet->getCell('B' .$x)->getValue();
                        $agent_name =  $worksheet->getCell('C' . $x)->getValue();
                        $agent_plays_role =  $worksheet->getCell('D' . $x)->getValue();
                        $agent_has_address =  $worksheet->getCell('E' . $x)->getValue();

                        if (!empty($agent_playsRole)){
                             $agent_plays_role_array = array_map('trim', explode(";",$agent_plays_role));
                        }

                        $agent = new Agent;
                        $agent->setId($agent_id);
                        $agent->setIdentifier($agent_identifier);
                        $agent->setName($agent_name);
                        $agent->setPlaysRole($agent_plays_role_array);
                        $agent->setHasAddress($agent_has_address);


                     array_push($cpsvap_array,$agent);

                    }
		   


/*
                 $po1 = json_encode($po);
                 $opo1 = json_decode($po1, true);
                 $opo1['@type'] = "http://data.europa.eu/m8g/PublicOrganization";
*/
                 //"@context" => "https://raw.githubusercontent.com/catalogue-of-services-isa/CPSV-AP/master/releases/2.2.1/CPSV-AP_v2.2.1.jsonld",
                 //$file = file_get_contents(__DIR__ . '/rdf/CPSV-AP_v2.2.1.jsonld');
                 //$context =  json_decode($file, true)['@context'];
 /*
                 $ps_dataset = (object) [
                       "@type" =>  "http://data.europa.eu/m8g/PublicServiceDataset",
                       "@context" => $context,
                       'hasPart' => [$ops1]

                 ];

                 $message = json_encode($ps_dataset);
*/
                 $cpsvap_base = (object) [
                      "@base" => "http://cpsvap.semic.eu/"
                 ];

                 $cpsvap_list = (object) [
                        "@context" => array("https://raw.githubusercontent.com/catalogue-of-services-isa/CPSV-AP/master/releases/2.2.1/CPSV-AP_v2.2.1.jsonld", $cpsvap_base ),
                        "@graph" => $cpsvap_array
                 ];


                 $message = json_encode($cpsvap_list);

                 //$file = file_get_contents(__DIR__ . '/rdf/CPSV-AP_v2.2.1.jsonld');
                 
                 //$json_array[] = json_decode($file, true);
                 //$json_array[] = json_decode($message, true);

                 //$fp = fopen(__DIR__ . '/rdf/results.jsonld', 'w');
                 //fwrite($fp, $message);
                 //fclose($fp);

                 //$compacted = JsonLD::compact(__DIR__ . '/rdf/' . 'results.jsonld', __DIR__ . '/rdf/' . 'CPSV-AP_v2.2.1.jsonld');

/*
                 $graph = new EasyRdf_Graph();
                 $graph->parseFile(__DIR__ . '/rdf/results.jsonld', 'jsonld');
                 $gs = new EasyRdf_GraphStore('http://35.181.155.22/sparql-graph-crud');
                 $gs->insert($graph, 'http://test.upload/virtuoso');
                 

                 $po_list = (object) [
                        "@context" => $context,
                        "@graph" => [$opo1]
                 ];

                 $fp = fopen(__DIR__ . '/rdf/po.jsonld', 'w');
                 fwrite($fp, json_encode($po_list));
                 fclose($fp);

                 $graph = new EasyRdf_Graph();
                 $graph->parseFile(__DIR__ . '/rdf/po.jsonld', 'jsonld');
                 $gs = new EasyRdf_GraphStore('http://35.181.155.22/sparql-graph-crud');
                 $gs->insert($graph, 'http://test.upload/virtuoso');
*/
                 //$message= json_encode($myArray);
                 $response = $response->withHeader('Content-Type', 'application/ld+json');
                 $response->write($message)->withStatus(200);
              } else {
                 $response = $response->withStatus(404);
              }
           } catch (\GuzzleHttp\Exception\ConnectException $e) {
                  $response = $response->withStatus(400);
           }
           return $response;
});


function download($url, $filepath, $filename){
       $path = $filepath . $filename;
       $file_path = fopen($path,'w');
       $client = new \GuzzleHttp\Client();
       $response = $client->get($url, ['http_errors' => false, 'save_to' => $file_path]);
       return ['response_code'=>$response->getStatusCode(), 'name' => $filename];
}


function upload($path, $filename){

        $url = "http://35.181.155.22/sparql-graph-crud?graph-uri=http://test.upload/virtuoso";
        $client = new \GuzzleHttp\Client();
        $response = $client->post($url, [
                   'multipart' => [
                                     [
                                          'name'     => 'FileContents',
                                          'contents' => file_get_contents($path . $filename),
                                          'filename' => $filename
                                     ]
                                  ]
        ]);
        return ['response_code'=>$response->getStatusCode(), 'name' => $filename];
}


/**
 * GET getPublicServices
 * Summary: Get all the public services
 * Notes: Get all the public services
 * Output-Formats: [application/json]
 */
$app->GET('/v1/publicservices', function($request, $response, $args) {
            
            $endpoint = 'http://35.181.155.22/sparql?default-graph-uri=http%3A%2F%2F35.181.155.22%2Fpilot&query=';

            $query='PREFIX cpsv: <http://purl.org/vocab/cpsv#> '.
                   'CONSTRUCT {?s rdf:type cpsv:PublicService} '.
                   'WHERE { '.
                   '?s rdf:type cpsv:PublicService . '.
                   '}';
            $output = '&format=application%2Fx-json%2Bld%2Bctx';
            $url = $endpoint . urlencode($query) . $output;
            $res = harvester_get_node($url)->getBody();

            $list = json_decode($res)->{'@graph'};
            
            $myArray = array();
            foreach ($list as  $publicservice) {
                $myArray[] = $publicservice->{'@id'};
            }
            
            $message= json_encode($myArray);
            $response->write($message);
            return $response;
});

    

$app->run();
