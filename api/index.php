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
use GuzzleHttp\Client;

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
  * GET getLifeEventByURI
  * Summary: Find LifeEvent by URI
  * Notes: returns a single LifeEvent
  * Output-Formats: [application/json]
 */
$app->GET('/v1/lifeEventByURI', function($request, $response, $args) {
 
                 $queryParams = $request->getQueryParams();
                 $lifeEventURI = $queryParams['lifeEventURI'];

                 $endpoint = 'http://35.181.155.22/sparql?default-graph-uri=http%3A%2F%2F35.181.155.22%2Fpilot&query=';
                 $query = 'PREFIX dct:  <http://purl.org/dc/terms/> PREFIX cv: <http://data.europa.eu/m8g/>' .
                          'CONSTRUCT { ' .
                          '?s dct:identifier ?identifier . ' .
                          '?s dct:title ?title . ' .
                          '}' .
                          'WHERE { '.
                          '?s rdf:type cv:LifeEvent . ' .
                          '?s dct:identifier ?identifier . ' .
                          '?s dct:title ?title . ' .
                          'FILTER(?s = <' . $lifeEventURI . '>) ' .
                          '}' ;

                 $output = '&format=application%2Fx-json%2Bld%2Bctx';
                 $url = $endpoint . urlencode($query) . $output;

                 $res = harvester_get_node($url)->getBody();
                 $identifier = json_decode($res)->{'@graph'}[0]->identifier;
                 $title = json_decode($res)->{'@graph'}[0]->title;

                 $le = new LifeEvent;
                 $le->setId($identifier);
                 $le->setTitle($title);

                 $message= json_encode($le);
                 $response->write($message);
                 return $response;
});

/**
 * GET getContactPointByURI
 * Summary: Find ContactPoint by URI
 * Notes: returns a single ContactPoint
 * Output-Formats: [application/json]
*/
$app->GET('/v1/contactPointByURI', function($request, $response, $args) {

               $queryParams = $request->getQueryParams();
               $contactPointURI = $queryParams['contactPointURI'];

               $endpoint = 'http://35.181.155.22/sparql?default-graph-uri=http%3A%2F%2F35.181.155.22%2Fpilot&query=';
               $query = 'PREFIX dct:  <http://purl.org/dc/terms/> PREFIX schema: <https://schema.org/> '.
                        'CONSTRUCT { '.
                        '?s dct:identifier ?identifier . ' .
                        '}' .
                        'WHERE { '.
                        '?s rdf:type schema:ContactPoint . ' .
                        '?s dct:identifier ?identifier . ' .
                        'FILTER(?s = <' . $contactPointURI . '>) '.
                        '}' ;

               $output = '&format=application%2Fx-json%2Bld%2Bctx';
               $url = $endpoint . urlencode($query) . $output;

               $res = harvester_get_node($url)->getBody();
               $identifier = json_decode($res)->{'@graph'}[0]->identifier;

               $cp = new ContactPoint;
               $cp->setId($identifier);

               $message= json_encode($cp);
               $response->write($message);
               return $response;
});


/** 
 * GET getLinguisticSystemByURI
 * Summary: Find LinguisticSystem by URI
 * Notes: returns a single LinguisticSystem
 * Output-Formats: [application/json]
*/
$app->GET('/v1/linguisticSystemByURI', function($request, $response, $args) {
               $queryParams = $request->getQueryParams();
               $lsURI = $queryParams['linguisticSystemURI'];

               $endpoint = 'http://35.181.155.22/sparql?default-graph-uri=http%3A%2F%2F35.181.155.22%2Fpilot&query=';
               $query = 'PREFIX dct:  <http://purl.org/dc/terms/> PREFIX skos: <http://www.w3.org/2004/02/skos/core#>'.
                        'CONSTRUCT { '.
                        '?s skos:prefLabel ?prefLabel . ' .
                        '}' .
                        'WHERE { '.
                        '?s rdf:type dct:LinguisticSystem . '.
                        '?s skos:prefLabel ?prefLabel . ' .
                        'FILTER(?s = <' . $lsURI . '>) '.
                        '}' ;

               $output = '&format=application%2Fx-json%2Bld%2Bctx';
               $url = $endpoint . urlencode($query) . $output;

               $res = harvester_get_node($url)->getBody();
               $prefLabel = json_decode($res)->{'@graph'}[0]->prefLabel;

               $ls = new LinguisticSystem;
               $ls->setPrefLabel($prefLabel);

               $message= json_encode($ls);
               $response->write($message);
               return $response;
});

 /**
  * GET getConceptbyURI
  * Summary: Find concept by URI
  * Notes: returns a single Concept
  * Output-Formats: [application/json]
 */
 $app->GET('/v1/conceptByURI', function($request, $response, $args) {
               $queryParams = $request->getQueryParams();
               $conceptURI = $queryParams['conceptURI'];

               $endpoint = 'http://35.181.155.22/sparql?default-graph-uri=http%3A%2F%2F35.181.155.22%2Fpilot&query=';
               $query = 'PREFIX skos: <http://www.w3.org/2004/02/skos/core#>'.
                        'CONSTRUCT { '.
                        '?s skos:prefLabel ?prefLabel . ' .
                        '}' .
                        'WHERE { '.
                        '?s rdf:type skos:Concept . '.
                        '?s skos:prefLabel ?prefLabel . ' .
                        'FILTER(?s = <' . $conceptURI . '>) '.
                        '}' ;

               $output = '&format=application%2Fx-json%2Bld%2Bctx';
               $url = $endpoint . urlencode($query) . $output;

               $res = harvester_get_node($url)->getBody();
               $prefLabel = json_decode($res)->{'@graph'}[0]->prefLabel;

               $concept = new Concept;
               $concept->setPrefLabel($prefLabel);

              $message= json_encode($concept);
              $response->write($message);
              return $response;
});

/**
  * GET getChannelbyURI
  * Summary: Find channel by URI
  * Notes: returns a single Channel
  * Output-Formats: [application/json]
*/
$app->GET('/v1/channelByURI', function($request, $response, $args) {
              $queryParams = $request->getQueryParams();
              $channelURI = $queryParams['channelURI'];

              $endpoint = 'http://35.181.155.22/sparql?default-graph-uri=http%3A%2F%2F35.181.155.22%2Fpilot&query=';
              $query = 'PREFIX cpsv: <http://purl.org/vocab/cpsv#> PREFIX cv: <http://data.europa.eu/m8g/>   PREFIX dct:  <http://purl.org/dc/terms/> '.
                       'CONSTRUCT { '.
                       '?s dct:identifier ?identifier . ' .
                       '?s dct:type ?conceptURI . ' .
                       '?s cpsv:hasInput ?hasInput . ' .
                       '}' .
                       'WHERE { '.
                       '?s rdf:type cv:Channel . ' .
                       '?s dct:identifier ?identifier . ' .
                       'OPTIONAL {?s dct:type ?conceptURI} . ' .
                       'OPTIONAL {?s cpsv:hasInput ?hasInput} . ' .
                       'FILTER(?s = <' . $channelURI . '>) '.
                       '}' ;

              $output = '&format=application%2Fx-json%2Bld%2Bctx';
              $url = $endpoint . urlencode($query) . $output;

              $res = harvester_get_node($url)->getBody();
              $identifier = json_decode($res)->{'@graph'}[0]->identifier;

              $concept ='';
              if(isset(json_decode($res)->{'@graph'}[0]->type)) {
                 $concept = json_decode($res)->{'@graph'}[0]->type;
              }

              $evidence ='';
              if(isset(json_decode($res)->{'@graph'}[0]->hasInput)) {
                  $evidence = json_decode($res)->{'@graph'}[0]->hasInput;
              }

              $channel = new Channel;
              $channel->setId($identifier);


              if (! empty($concept)){
                $channel->setTypeURI($concept);
              }

              if (! empty($evidence)){
                 $channel->setEvidenceURI($evidence);
              }

             $message= json_encode($channel);
             $response->write($message);
             return $response;
});




/** 
 * GET getEvidencebyURI
 * Summary: Find evidence by URI
 * Notes: returns a single Evidence
 * Output-Formats: [application/json]

*/
$app->GET('/v1/evidenceByURI', function($request, $response, $args) {

             $queryParams = $request->getQueryParams();
             $evidenceURI = $queryParams['evidenceURI'];

             $endpoint = 'http://35.181.155.22/sparql?default-graph-uri=http%3A%2F%2F35.181.155.22%2Fpilot&query=';
             $query = ' PREFIX cv: <http://data.europa.eu/m8g/>   PREFIX dct:  <http://purl.org/dc/terms/> '.
                      'CONSTRUCT { '.
                      '?s dct:title ?title . ' .
		      '?s dct:identifier ?identifier . ' .
                      '}' .
                     'WHERE { '.
                      '?s rdf:type cv:Evidence . '.
                      '?s dct:title ?title . ' .
		      '?s dct:identifier ?identifier . ' .
                      'FILTER(?s = <' . $evidenceURI . '>) '.
                      '}' ;

             $output = '&format=application%2Fx-json%2Bld%2Bctx';
             $url = $endpoint . urlencode($query) . $output;

             $res = harvester_get_node($url)->getBody();
             $title = json_decode($res)->{'@graph'}[0]->title;
 	     $identifier = json_decode($res)->{'@graph'}[0]->identifier;


             $evidence = new Evidence;
             $evidence->setTitle($title);
	     $evidence->setId($identifier);
	    

             $message= json_encode($evidence);
             $response->write($message);
              return $response;
 });




/**
 * GET getLocationByURI
 * Summary: Find location by URI
 * Notes: Returns a single location
 * Output-Formats: [application/json]
 */
    /**
     * @OA\Get(
     *     path="/v1/locationbyURI",
     *     description="Find location by URI",
     *     operationId="getLocationByURI",
     *     @OA\Parameter(
     *         description="URI of the location to provide",
     *         in="query",
     *         name="locationURI",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/definitions/Location")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     )
     * )
     */

$app->GET('/v1/locationByURI', function($request, $response, $args) {
            
            $queryParams = $request->getQueryParams();
            $locationURI = $queryParams['locationURI'];
            
            $endpoint = 'http://35.181.155.22/sparql?default-graph-uri=http%3A%2F%2F35.181.155.22%2Fpilot&query=';
            $query = 'PREFIX dct:  <http://purl.org/dc/terms/> '.
                     'CONSTRUCT { '.
                     '?s dct:title ?title . '.
                     '}' .
                     'WHERE { '.
                     '?s rdf:type dct:Location . '.
                     '?s dct:title ?title . '.
                     'FILTER(?s = <' . $locationURI . '>) '.
                     '}' ;

            $output = '&format=application%2Fx-json%2Bld%2Bctx';
            $url = $endpoint . urlencode($query) . $output;

            $res = harvester_get_node($url)->getBody();
            $title = json_decode($res)->{'@graph'}[0]->title;

            $location = new Location;
            $location->setTitle($title);

            $message= json_encode($location);
            $response->write($message);
             return $response;
});


/**
 * GET getPublicOrganizationByURI
 * Summary: Find public organization by URI
 * Notes: Returns a single public organization
 * Output-Formats: [application/json]
 */
$app->GET('/v1/publicOrganizationByURI', function($request, $response, $args) {
            
            $queryParams = $request->getQueryParams();
            $publicOrganizationURI = $queryParams['publicOrganizationURI'];
            
            $endpoint = 'http://35.181.155.22/sparql?default-graph-uri=http%3A%2F%2F35.181.155.22%2Fpilot&query=';
            $query = 'PREFIX cv: <http://data.europa.eu/m8g/> PREFIX dct: <http://purl.org/dc/terms/> PREFIX skos: <http://www.w3.org/2004/02/skos/core#> '.
                     'CONSTRUCT { '.
                     '?s skos:prefLabel ?label . '.
                     '?s dct:spatial ?spatial . '.
                     '?s dct:identifier ?identifier .'.
                     '} '.
                     'WHERE { '.
                     '?s rdf:type cv:PublicOrganisation . '.
                     '?s skos:prefLabel ?label . '.
                     '?s dct:spatial ?spatial . '.
                     '?s dct:identifier ?identifier . ' .
                     'FILTER(?s = <'. $publicOrganizationURI . '>)' .
                     '} ' ;


            $output = '&format=application%2Fx-json%2Bld%2Bctx';
            $url = $endpoint . urlencode($query) . $output;
 
            $res = harvester_get_node($url)->getBody();
            $label = json_decode($res)->{'@graph'}[0]->prefLabel;
            $spatial = json_decode($res)->{'@graph'}[0]->spatial;
            $identifier = json_decode($res)->{'@graph'}[0]->identifier;

            $po = new PO;
            $po->setLabel($label);
            $po->setSpatialURI($spatial);
            $po->setId($identifier);
            $message= json_encode($po);

            $response->write($message);
            return $response;
});

/**
 * GET getPublicServiceByURI
 * Summary: Find public service by URI
 * Notes: Returns a single public service
 * Output-Formats: [application/json]
 */
$app->GET('/v1/publicServiceByURI', function($request, $response, $args) {

          $queryParams = $request->getQueryParams();
          $publicServiceURI = $queryParams['publicServiceURI'];

          $endpoint = 'http://35.181.155.22/sparql?default-graph-uri=http%3A%2F%2F35.181.155.22%2Fpilot&query=';

          $query = 'PREFIX cpsv: <http://purl.org/vocab/cpsv#> PREFIX cv: <http://data.europa.eu/m8g/> PREFIX dct:  <http://purl.org/dc/terms/> ' .
                   'CONSTRUCT { ' .
                   '?s dct:title ?title . ' .
                   '?s dct:description ?description . ' .
                   '?s dct:identifier ?identifier . ' .
                   '?s cv:hasCompetentAuthority ?competentAuthority . ' .
		   '?s cpsv:hasInput ?hasInput . ' .
                   '?s cv:hasChannel ?hasChannel . ' .
                   '?s dct:type ?type . ' .
                   '?s cv:sector ?sector . ' .
                   '?s dct:language ?language . ' .
                   '?s cv:hasContactPoint ?hasContactPoint . ' .
                   '?s cv:isGroupedBy ?isGroupedBy . ' .
                   '} ' .
                   'WHERE { ' .
                   '?s rdf:type cpsv:PublicService . ' .
                   '?s dct:title ?title . ' .
                   '?s dct:description ?description . ' .
                   '?s dct:identifier ?identifier . ' .
                   '?s cv:hasCompetentAuthority ?competentAuthority . ' .
		   'OPTIONAL{?s cpsv:hasInput ?hasInput .} ' .
                   'OPTIONAL{?s cv:hasChannel ?hasChannel .} ' .
                   'OPTIONAL{?s dct:type ?type .} ' .
                   'OPTIONAL{?s cv:sector ?sector .} ' .
                   'OPTIONAL{?s dct:language ?language .} ' .
                   'OPTIONAL{?s cv:hasContactPoint ?hasContactPoint .} ' .
                   'OPTIONAL{?s cv:isGroupedBy ?isGroupedBy .} ' .
                   'FILTER(?s = <' . $publicServiceURI . '>) ' .
                   '}';
   
           $output = '&format=application%2Fx-json%2Bld%2Bctx';
           $url = $endpoint . urlencode($query) . $output;

           $res = harvester_get_node($url)->getBody();
           $identifier = json_decode($res)->{'@graph'}[0]->identifier;
           $title = json_decode($res)->{'@graph'}[0]->title;
           $description = json_decode($res)->{'@graph'}[0]->description;
           $competentAuthority = json_decode($res)->{'@graph'}[0]->hasCompetentAuthority;

           $evidence ='';
           if(isset(json_decode($res)->{'@graph'}[0]->hasInput)) {
	      $evidence = json_decode($res)->{'@graph'}[0]->hasInput;
           }
	   
           $channel ='';
           if(isset(json_decode($res)->{'@graph'}[0]->hasChannel)) {
             $channel = json_decode($res)->{'@graph'}[0]->hasChannel;
           }

           $type ='';
           if(isset(json_decode($res)->{'@graph'}[0]->type)) {
             $type = json_decode($res)->{'@graph'}[0]->type;
           }

           $sector ='';
           if(isset(json_decode($res)->{'@graph'}[0]->sector)) {
               $sector = json_decode($res)->{'@graph'}[0]->sector;
           }

           $language ='';
           if(isset(json_decode($res)->{'@graph'}[0]->language)) {
               $language = json_decode($res)->{'@graph'}[0]->language;
            }

           $contactPoint ='';
           if(isset(json_decode($res)->{'@graph'}[0]->hasContactPoint)) {
              $contactPoint = json_decode($res)->{'@graph'}[0]->hasContactPoint;
           }


           $event ='';
           if(isset(json_decode($res)->{'@graph'}[0]->isGroupedBy)) {
              $event = json_decode($res)->{'@graph'}[0]->isGroupedBy;
           }

           $ps = new PS;
           $ps->setId($identifier);
           $ps->setTitle($title);
           $ps->setDescription($description);
           $ps->setCompetentAuthorityURI($competentAuthority);
           if (! empty($evidence)){
              $ps->setEvidenceURI($evidence);
           }
           if (! empty($channel)){
              $ps->setChannelURI($channel);
           }
           if (! empty($type)){
               $ps->setTypeURI($type);
           }
           if (! empty($sector)){
               $ps->setSectorURI($sector);
           }
           if (! empty($language)){
                $ps->setLanguageURI($language);
           }
           if (! empty($contactPoint)){
                $ps->setContactPointURI($contactPoint);
           }
           if (! empty($event)){
                $ps->setEventURI($event);
           }

           $message= json_encode($ps);
           $response->write($message);

           return $response;
});

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
