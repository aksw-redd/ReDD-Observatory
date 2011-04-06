<?php 

require_once 'cache.php';

// ODBC connection
$con = null;

// init connection to virtuoso
function initConnection(){
	global $con;
	$con = odbc_connect("ONTOWIKIGHO", "dba", "alaiCh4u");
}

// query virtuoso
function executeQuery($sparqlQuery, $graphUri = null){
	global $con;

	// build Virtuoso/PL query
	if( $graphUri == null ){
		$virtuosoPl = 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparqlQuery . '\', NULL, 0)';
	}else{
		$virtuosoPl = 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparqlQuery . '\', \'' . $graphUri . '\', 0)';
	}

	// get result
	$odbcResult = odbc_exec($con, $virtuosoPl);

	// get number of fields (columns)
	$numFields = odbc_num_fields($odbcResult);

	// the result will be stored in here
	$resultArray = array();

	while (odbc_fetch_row($odbcResult)) {
	    $resultRowNamed = array();

	    // for all columns
	    for ($i = 1; $i <= $numFields; ++$i) {
		$fieldName = odbc_field_name($odbcResult, $i);
		$fieldType = odbc_field_type($odbcResult, $i);
		$value     = '';

		if (substr($fieldType, 0, 4) == 'LONG') {
		    // LONG VARCHAR or LONG VARBINARY
		    // get the field value in parts
		    while ($segment = odbc_result($odbcResult, $i)) {
		        $value .= (string)$segment;
		    }
		} else {
		    // get the field value normally
		    $value = odbc_result($odbcResult, $i);
		}

		if (null !== $field) {
		    // add only requested field
		    if ($fieldName == $field) {
		        $resultRowNamed = $value;
		    }
		} else {
		    // add all fields
		    if ($columnsAsKeys) {
		        $resultRowNamed[$fieldName] = $value;
		    } else {
		        $resultRowNamed[] = $value;
		    }
		}
	    }

	    // add row to result array
	    array_push($resultArray, $resultRowNamed);
	}

	return $resultArray;
}

// query for countries
function getCountries(){
    global $cache;

    if( ($countries = $cache->load('countries_list')) === false ) {
	    $graphUri = "http://ghocountry.org";
	    $sparqlQuery = "
		      SELECT DISTINCT ?country ?name
		      WHERE { 
			    ?country a ?uri .
		            ?country <http://www.w3.org/2000/01/rdf-schema#label> ?name . 
		            FILTER ( regex(?uri, \"Country$\") )
		      } ORDER BY ?name ";

	    $countries = executeQuery($sparqlQuery, $graphUri);

	    $cache->save( json_encode($countries), 'countries_list', array('countries')) ;
	}else{
	    $countries = json_decode($countries);
	}
	
	return $countries;
}

// query for diseases
function getDiseases(){
    global $cache;

    if( ($diseases = $cache->load('disease_list')) === false ) {
	    $graphUri = "http://ghocountry.org";
	    $sparqlQuery = "
		      SELECT DISTINCT ?disease ?name
		      WHERE { ?disease a ?uri .
                              ?disease <http://www.w3.org/2000/01/rdf-schema#label> ?name . 
                              FILTER ( regex(?uri, \"Disease$\") )
		      } ORDER BY ?name ";

	    $diseases = executeQuery($sparqlQuery, $graphUri);
	    
	    $cache->save( json_encode($diseases), 'disease_list', array('diseases')) ;
	}else{
	    $diseases = json_decode($diseases);
	}
	
	return $diseases;
}

// do init connection
initConnection();
?>
