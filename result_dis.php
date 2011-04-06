
<?php

// calc time
$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime; 

require_once 'lib/lib.php';

// get data
$diseases = $_REQUEST['diseases'];
$country = $_REQUEST['country'];

//print_r ( $countries ); echo $disease; die;

$results = array();

// query data for each disease
foreach( $diseases as $key => $disease ){
    
    $cacheID = preg_replace("/[^a-zA-Z0-9]/", "_", $country)."_".preg_replace("/[^a-zA-Z0-9]/", "_", $disease)."_dis";

    if( ($res = $cache->load($cacheID) ) === false ) {

	    /*
             * Deaths
             */
            $query = "
             PREFIX qb: <http://purl.org/linked-data/cube#>
             PREFIX att: <http://purl.org/linked-data/sdmx/2009/attribute#>
             PREFIX eg: <http://example.com/>
             PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
             PREFIX linkedct: <http://data.linkedct.org/resource/linkedct/>
             PREFIX gho: <http://ghodata/>
             PREFIX redd: <http://redd.aksw.org:>
             SELECT  
                    ?incidence AS ?daly
             FROM <http://ghocountry.org>
             FROM <http://linkedct.org>
             FROM <http://interlinks.org>
             WHERE
             {
             ?item a qb:Observation.
             ?item gho:Country ?country .
             ?country rdfs:label \"".$country."\".
             ?item gho:Disease ?disease .
             ?disease rdfs:label \"".$disease."\" .
             ?item att:unitMeasure gho:Measure .
             ?item eg:incidence ?incidence .
             ?trial a linkedct:trials.
             ?trial linkedct:condition ?condition .
             ?condition owl:sameAs ?disease .
             ?trial linkedct:location ?location.
             ?location redd:locatedIn ?country.
             } GROUP BY ?incidence
            ";

            $res = executeQuery($query);
            $incidence = $res[0][0];


	     /*
             * Total deaths/DALY
             */
            $queryAllCauses = "
             PREFIX qb: <http://purl.org/linked-data/cube#>
             PREFIX att: <http://purl.org/linked-data/sdmx/2009/attribute#>
             PREFIX eg: <http://example.com/>
             PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
             PREFIX linkedct: <http://data.linkedct.org/resource/linkedct/>
             PREFIX gho: <http://ghodata/>
             PREFIX redd: <http://redd.aksw.org:>
             SELECT 
                    ?incidence AS ?daly
             FROM <http://ghocountry.org>
             FROM <http://linkedct.org>
             FROM <http://interlinks.org>
             WHERE
             {
             ?item a qb:Observation.
             ?item gho:Country ?country .
             ?country rdfs:label \"".$country."\".
             ?item gho:Disease ?disease .
             ?disease rdfs:label \"All Causes\" .
             ?item eg:incidence ?incidence .
             }
            ";

            $res = executeQuery($queryAllCauses);
            $incidence_all = $res[0][0];

 	    /*
             * DALY
             */
            $query = "  
             PREFIX qb: <http://purl.org/linked-data/cube#>
             PREFIX att: <http://purl.org/linked-data/sdmx/2009/attribute#>
             PREFIX eg: <http://example.com/>
             PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
             PREFIX linkedct: <http://data.linkedct.org/resource/linkedct/>
             PREFIX gho: <http://ghodata/>
             PREFIX redd: <http://redd.aksw.org:>
             SELECT  
                    ?incidence AS ?daly
             FROM <http://ghocountry.org>
             FROM <http://linkedct.org>
             FROM <http://interlinks.org>
             WHERE
             {
             ?item a qb:Observation.
             ?item gho:Country ?country .
             ?country rdfs:label \"".$country."\".
             ?item gho:Disease ?disease .
             ?disease rdfs:label \"".$disease."\" .
             ?item att:unitMeasure gho:Measure2 .
             ?item eg:incidence ?incidence .
             ?trial a linkedct:trials.
             ?trial linkedct:condition ?condition .
             ?condition owl:sameAs ?disease .
             ?trial linkedct:location ?location.
             ?location redd:locatedIn ?country.
             } GROUP BY ?incidence
            ";
            $res = executeQuery($query);
	    $daly = $res[0][0];

	    /*
             * Trials number
             */
           $queryTrials = "
            PREFIX qb: <http://purl.org/linked-data/cube#>
            PREFIX att: <http://purl.org/linked-data/sdmx/2009/attribute#>
            PREFIX eg: <http://example.com/>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
            PREFIX linkedct: <http://data.linkedct.org/resource/linkedct/>
            PREFIX gho: <http://ghodata/>
            PREFIX redd: <http://redd.aksw.org:>
            SELECT
             ?trial
             count(distinct(?disease)) AS ?condition_superclass_count
            FROM <http://linkedct.org>
            FROM <http://ghocountry.org>
            FROM <http://interlinks.org>
            WHERE
           {
           ?item a qb:Observation.
           ?item gho:Disease ?disease .
	   ?disease rdfs:label \"".$disease."\" .
           ?trial a linkedct:trials.
           ?trial linkedct:condition ?condition .
           ?condition owl:sameAs ?disease .
           ?trial linkedct:location ?location.
	   ?location linkedct:facility_address_country \"".$country."\" .
           } GROUP BY ?trial
           ";

          $res = executeQuery($queryTrials);
          $trials = 0.0;
          foreach($res as $key => $value){
            if( $value[1] == 1 ){
                $trials += $value[1];
            }else{
                $trials += (float) 1/floatval($value[1]);
            	}
	    }


	    /*
             * Total trials
             */
            $queryAllTrials = "
             PREFIX qb: <http://purl.org/linked-data/cube#>
             PREFIX att: <http://purl.org/linked-data/sdmx/2009/attribute#>
             PREFIX eg: <http://example.com/>
             PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
             PREFIX linkedct: <http://data.linkedct.org/resource/linkedct/>
             PREFIX gho: <http://ghodata/>
             PREFIX redd: <http://redd.aksw.org:>
             SELECT
                    count(distinct(?trial)) AS ?nr_of_trials
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
             }
            ";
            $res = executeQuery($queryAllTrials);
            $trialsAll = $res[0][0];

	  /*
           * Publications number
           */
          $queryPubs = "
           PREFIX qb: <http://purl.org/linked-data/cube#>
           PREFIX att: <http://purl.org/linked-data/sdmx/2009/attribute#>
           PREFIX eg: <http://example.com/>
           PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
           PREFIX linkedct: <http://data.linkedct.org/resource/linkedct/>
           PREFIX gho: <http://ghodata/>
           PREFIX redd: <http://redd.aksw.org:>
           SELECT
            ?publication 
            count(distinct(?disease)) AS ?nr_of_conditions
           FROM <http://ghocountry.org>
           FROM <http://linkedct.org>
           FROM <http://interlinks.org>
           WHERE{
            ?item a qb:Observation.
            ?item gho:Country ?country .
            ?country rdfs:label \"".$country."\".
            ?item gho:Disease ?disease .
            ?disease rdfs:label \"".$disease."\" .
            ?trial a linkedct:trials.
            ?trial linkedct:condition ?condition .
            ?condition owl:sameAs ?disease .
            ?trial linkedct:location ?location.
            ?location redd:locatedIn ?country.
            ?trial linkedct:reference ?publication.
           } GROUP BY ?publication ?disease
            ";

            $res = executeQuery($queryPubs);
            $pubs = 0.0;
            foreach($res as $key => $value){
             if( $value[1] == 1 ){
                $pubs += $value[1];
             }else{
           $pubs += (float) 1/floatval($value[1]);
            }
          }

	    /*
             * Total publications
             */

	    $queryAllPubs = "
             PREFIX qb: <http://purl.org/linked-data/cube#>
             PREFIX att: <http://purl.org/linked-data/sdmx/2009/attribute#>
             PREFIX eg: <http://example.com/>
             PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
             PREFIX linkedct: <http://data.linkedct.org/resource/linkedct/>
             PREFIX gho: <http://ghodata/>
             PREFIX redd: <http://redd.aksw.org:>
             SELECT 
                    count(distinct(?publication)) AS ?nr_of_publications
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
             }
            ";

            $res = executeQuery($queryAllPubs);
            $pubsAll = $res[0][0];

	    $res = array(
		    'disease' => $disease,
		    'incidence_all' => str_replace(",","",$incidence_all),
		    'trials_all' => $trialsAll,//str_replace(",","",$trials_all),
		    'incidence' => str_replace(",","",$incidence),
		    'daly' => str_replace(",","",$daly),
		    'trials' => $trials, //str_replace(",","",$res[1]),
		    'pubs' => $pubs,
		    'pubs_all' => $pubsAll
	    );
	    
	    $cache->save( json_encode($res), $cacheID, array('country'));
	    
	    $res = $cache->load($cacheID);
	};
	
	$res = json_decode($res);
	
	$results[] = $res;
}

$table1 = array();
$table2 = array();

foreach($results as $index => $data){
    $inc_p = $data->incidence / $data->incidence_all;
    $inc_d = $data->daly / $data->incidence_all;
    $tr_p = $data->trials / $data->trials_all;
    $pub_p = floatval($data->pubs) / floatval($data->pubs_all);
    $oddeven = ($index%2 == 0)?"even":"odd";    

    $res = array(
        'oddeven' => $oddeven,
        'disease' => $data->disease,
        'incidence' => sprintf("%01.1f", $data->incidence),
        'incidence_all' => sprintf("%01.0f", $data->incidence_all),
        'incidence_percent' => sprintf("%01.4f", $inc_p),
        'daly' => sprintf("%01.0f", $data->daly),
        'daly_percent' => sprintf("%01.4f", $inc_d),
        'trials' => sprintf("%01.2f", $data->trials),
        'trials_all' => sprintf("%01.2f", $data->trials_all),
        'trials_percent' => sprintf("%01.4f", $tr_p),
        'trials_incedence' => sprintf("%01.4f", $tr_p/$inc_p ),
        'trials_daly' => sprintf("%01.4f", $tr_p/$inc_d )
    );
    
    $table1[] = $res;
}


foreach($results as $index => $data){
    //echo $data->incidence; die;
    $inc_p = $data->incidence / $data->incidence_all;
    $inc_d = $data->daly / $data->incidence_all;
    $tr_p = $data->trials / $data->trials_all;
    $pub_p = floatval($data->pubs) / floatval($data->pubs_all);
    $oddeven = ($index%2 == 0)?"even":"odd";
    
    $res = array(
        'oddeven' => $oddeven,
        'disease' => $data->disease,
        'incidence' => sprintf("%01.1f", $data->incidence),
        'incidence_all' => sprintf("%01.0f", $data->incidence_all),
        'incidence_percent' => sprintf("%01.4f", $inc_p),
        'daly' => sprintf("%01.0f", $data->daly),
        'daly_percent' => sprintf("%01.4f", $inc_d),
        'pubs' => sprintf("%01.2f", $data->pubs),
        'pubs_all' => sprintf("%01.0f", $data->pubs_all),
        'trials_percent' => sprintf("%01.4f", $tr_p),
        'pubs_incedence' => sprintf("%01.4f", $pub_p/$inc_p ),
        'pubs_daly' => sprintf("%01.4f", $pub_p/$inc_d )
    );
    
    $table2[] = $res;
}

// output exec time
$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$endtime = $mtime; 
$totaltime = ($endtime - $starttime); 

$out_res = array(
    'table1' => $table1,
    'table2' => $table2,
    'time' => sprintf("%01.3f", $totaltime)
);

echo json_encode( $out_res );

?>
