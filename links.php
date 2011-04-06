<?php

require_once 'lib/lib.php';

$country = $_REQUEST['country'];
$disease = $_REQUEST['disease'];
$action = $_REQUEST['action'];
// possible actions:
// trial
// alltrials
// pubs
// allpubs

switch($action){
    case "trial":
        // TriaLinks
        $query = "
             PREFIX qb: <http://purl.org/linked-data/cube#>
             PREFIX att: <http://purl.org/linked-data/sdmx/2009/attribute#>
             PREFIX eg: <http://example.com/>
             PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
             PREFIX linkedct: <http://data.linkedct.org/resource/linkedct/>
             PREFIX gho: <http://ghodata/>
             PREFIX redd: <http://redd.aksw.org:>
             SELECT ?trial ?title
             FROM <http://linkedct.org>
             FROM <http://ghocountry.org>
             FROM <http://interlinks.org>
             WHERE
             {
             ?item a qb:Observation.
             ?item gho:Disease ?disease .
             ?disease rdfs:label \"".$disease."\".
             ?trial a linkedct:trials.
             ?trial linkedct:condition ?condition .
             ?trial linkedct:brief_title ?title.
             ?condition owl:sameAs ?disease .
             ?trial linkedct:location ?location.
             ?location linkedct:facility_address_country \"".$country."\".
             } GROUP BY ?trial
	         ";
        break;
    case "alltrials":
        $query = "
             PREFIX qb: <http://purl.org/linked-data/cube#>
             PREFIX att: <http://purl.org/linked-data/sdmx/2009/attribute#>
             PREFIX eg: <http://example.com/>
             PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
             PREFIX linkedct: <http://data.linkedct.org/resource/linkedct/>
             PREFIX gho: <http://ghodata/>
             PREFIX redd: <http://redd.aksw.org:>
             SELECT ?trial ?title
             FROM <http://ghocountry.org>
             FROM <http://linkedct.org>
             FROM <http://interlinks.org>
             WHERE
             {
             ?item a qb:Observation.
             ?item gho:Country ?country .
             ?country rdfs:label \"".$country."\".
             ?trial a linkedct:trials.
             ?trial linkedct:brief_title ?title.
             ?trial linkedct:location ?location.
             ?location redd:locatedIn ?country.
             }
             ";
	    break;
    case "pubs":
        $query = "
             PREFIX qb: <http://purl.org/linked-data/cube#>
             PREFIX att: <http://purl.org/linked-data/sdmx/2009/attribute#>
             PREFIX eg: <http://example.com/>
             PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
             PREFIX linkedct: <http://data.linkedct.org/resource/linkedct/>
             PREFIX gho: <http://ghodata/>
             PREFIX redd: <http://redd.aksw.org:>
             SELECT ?publication ?title
             FROM <http://ghocountry.org>
             FROM <http://linkedct.org>
             FROM <http://interlinks.org>
             WHERE{
             ?item a qb:Observation.
             ?item gho:Country ?country .
             ?country rdfs:label \"".$country."\".
             ?item gho:Disease ?disease .
             ?disease rdfs:label \"".$disease."\".
             ?trial a linkedct:trials.
             ?trial linkedct:condition ?condition .
             ?condition owl:sameAs ?disease .
             ?trial linkedct:location ?location.
             ?location redd:locatedIn ?country.
             ?trial linkedct:reference ?publication.
             ?publication linkedct:citation ?title.
             } GROUP BY ?publication ?title.
             ";
        break;
    case "allpubs":
        $query = "
             PREFIX qb: <http://purl.org/linked-data/cube#>
             PREFIX att: <http://purl.org/linked-data/sdmx/2009/attribute#>
             PREFIX eg: <http://example.com/>
             PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
             PREFIX linkedct: <http://data.linkedct.org/resource/linkedct/>
             PREFIX gho: <http://ghodata/>
             PREFIX redd: <http://redd.aksw.org:>
             SELECT ?publication ?title
             FROM <http://ghocountry.org>
             FROM <http://linkedct.org>
             FROM <http://interlinks.org>
             WHERE
             {
             ?item a qb:Observation.
             ?item gho:Country ?country .
             ?country rdfs:label \"".$country."\".
             ?trial a linkedct:trials. 
             ?trial linkedct:location ?location.
             ?location redd:locatedIn ?country.
             ?trial linkedct:reference ?publication.
             ?publication linkedct:citation ?title.
             }
             ";
        break;
}

//echo $query; die;

//$cacheID = preg_replace("/[^a-zA-Z0-9]/", "_", $country)."_".preg_replace("/[^a-zA-Z0-9]/", "_", $disease)."_".preg_replace("/[^a-zA-Z0-9]/", "_", $action)."_links";

//$results = array();

//if( ($results = $cache->load($cacheID) ) === false ) {
    $results = executeQuery($query);
    
    //$cache->save( json_encode($res), $cacheID, array('links'.$action) );
//}else{
//    $results = json_decode($results);
//}

print_r($results); die;

?>

<html>
    <head>
        <title>Links</title>
    </head>
    <body>
    </body>
</html>
