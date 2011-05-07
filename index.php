<?php

require_once 'lib/lib.php';

$countries = getCountries();
$diseases = getDiseases();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>ReDD Observatory</title>
<link rel="stylesheet" type="text/css" href="css/visualize.css" /> 
<link rel="stylesheet" type="text/css" href="css/visualize-light.css" />
<link rel="stylesheet" type="text/css" href="css/tipTip.css" />
<link rel="stylesheet" type="text/css" href="css/blitzer/jquery-ui-1.8.7.custom.css" />
<link rel="stylesheet" type="text/css" href="css/tablesorter/style.css" />
<script type="text/javascript" src="js/excanvas.js"></script>
<script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="js/jquery.tmpl.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.7.custom.min.js"></script>
<script type="text/javascript" src="js/visualize.jQuery.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="js/jquery.tipTip.minified.js"></script>
<script type="text/javascript" src="js/codes.js"></script>
<script type="text/javascript" src="js/main.js"></script>

<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
    // init google vis api
	google.load('visualization', '1', {'packages':['geomap']});
</script>

</head>
<body>
<!-- TEMPLATES FOR DATA -->
<script id="deathresTemplate" type="text/x-jquery-tmpl">
<table id="death-result-table" style="width:95%; height: auto;" class="tablesorter" border="0" cellpadding="0" cellspacing="1">
    <thead>
        <tr>
	        <th class="tooltip" title="Name of the country">Country</th>
            <th class="tooltip" title="Number of deaths for each disease/1000">Deaths</th>
            <th class="tooltip" title="Number of deaths attributable to all diseases/1000">Total Deaths</th>
            <th class="tooltip" title="Number of deaths for each disease divided by number of deaths attributable to all diseases">Deaths/Total Deaths</th>
            <th class="tooltip" title="Number of DALYs for each disease/1000">DALYs</th>
            <th class="tooltip" title="Numbers of DALYs attributable to all diseases/1000">Total DALYs</th>
            <th class="tooltip" title="Number of DALYs for each disease divided by number of DALYs attributable to all diseases">DALYs/Total DALYs</th>
            <th class="tooltip" title="Number of trials for each disease in each country (Weighted)">Number of Trials</th>
            <th class="tooltip" title="Total number of trials for all diseases in each country">Total number of Trials</th>
            <th class="tooltip" title="Number of trials for each disease in each country divided by total number of trials for all diseases">Trials/Total number of Trials</th>
            <th class="row tooltip" title="Trials vs. Death Index" id="col0">Trials vs. Deaths</th>
            <th class="row tooltip" title="Trials vs. DALYs Index" id="col1">Trials vs. DALYs</th>
        </tr>
    </thead>
    <tbody>
        {{each table1}}
            <tr class="${oddeven}">
                <td class="col country">${country}</td>
                <td>${incidence}</td>
                <td>${incidence_all}</td>
                <td>${incidence_percent}</td>
                <td>${daly}</td>
                <td>${incidence_all}</td>
                <td>${daly_percent}</td>
                <td><a href=".${trials_links}." class="link trial">${trials}</a></td>
                <td><a href="#" class="link alltrial">${trials_all}</a></td>
                <td>${trials_percent}</td>
                <td class="data" id="col0">${trials_incidence}</td>
                <td class="data" id="col1">${trials_daly}</td>
            </tr>
        {{/each}}
    </tbody>
</table>
</script>
<script id="dalyresTemplate" type="text/x-jquery-tmpl">
<table id="daly-result-table" style="width:95%; height: auto;" class="tablesorter" border="0" cellpadding="0" cellspacing="1">
    <thead>
        <tr>
	    <th class="tooltip" title="Name of the country">Country</th>
            <th class="tooltip" title="Number of deaths for each disease/1000">Deaths</th>
            <th class="tooltip" title="Number of deaths attributable to all diseases/1000">Total Deaths</th>
            <th class="tooltip" title="Number of deaths for each disease divided by number of deaths attributable to all diseases">Deaths/Total Deaths</th>
            <th class="tooltip" title="Number of DALYs for each disease/1000">DALYs</th>
            <th class="tooltip" title="Numbers of DALYs attributable to all diseases/1000">Total DALYs</th>
            <th class="tooltip" title="Number of DALYs for each disease divided by number of DALYs attributable to all diseases">DALYs/Total DALYs</th>
            <th class="tooltip" title="Number of publications for each disease in each country (Weighted)">Number of Publications</th>
            <th class="tooltip" title="Total number of publications for all diseases in each country">Total number of Publications</th>
            <th class="tooltip" title="Number of publications for each disease in each country divided by total number of publications for all diseases">Publications/Total num</th>
            <th class="row tooltip" title="Publications vs. Death Index" id="col0">Publications vs. Deaths</th>
            <th class="row tooltip" title="Publications vs. DALYs Index" id="col1">Publications vs. DALYs</th>
        </tr>
    </thead>
    <tbody>   
        {{each table2}}
            <tr class="${oddeven}">
                <td class="col country">${country}</td>
                <td>${incidence}</td>
                <td>${incidence_all}</td>
                <td>${incidence_percent}</td>
                <td>${daly}</td>
                <td>${incidence_all}</td>
                <td>${daly_percent}</td>
                <td><a href="#" class="link pubs">${pubs}</a></td>
                <td><a href="#" class="link allpubs">${pubs_all}</a></td>
                <td>${trials_percent}</td>
                <td class="data" id="col0">${pubs_incedence}</td>
                <td class="data" id="col1">${pubs_daly}</td>
            </tr>
        {{/each}}
    </tbody>
</table>
</script>
<script id="deathdisresTemplate" type="text/x-jquery-tmpl">
<table id="death-result-table" style="width:95%; height: auto;" class="tablesorter" border="0" cellpadding="0" cellspacing="1">
    <thead>
        <tr>
	        <th class="tooltip" title="Name of the disease">Disease</th>
            <th class="tooltip" title="Number of deaths for each disease/1000">Deaths</th>
            <th class="tooltip" title="Number of deaths attributable to all diseases/1000">Total Deaths</th>
            <th class="tooltip" title="Number of deaths for each disease divided by number of deaths attributable to all diseases">Deaths/Total Deaths</th>
            <th class="tooltip" title="Number of DALYs for each disease/1000">DALYs</th>
            <th class="tooltip" title="Numbers of DALYs attributable to all diseases/1000">Total DALYs</th>
            <th class="tooltip" title="Number of DALYs for each disease divided by number of DALYs attributable to all diseases">DALYs/Total DALYs</th>
            <th class="tooltip" title="Number of trials for each disease in each country (Weighted)">Number of Trials</th>
            <th class="tooltip" title="Total number of trials for all diseases in each country">Total number of Trials</th>
            <th class="tooltip" title="Number of trials for each disease in each country divided by total number of trials for all diseases">Trials/Total number of Trials</th>
            <th class="row tooltip" title="Trials vs. Death Index" id="col0">Trials vs. Deaths</th>
            <th class="row tooltip" title="Trials vs. DALYs Index" id="col1">Trials vs. DALYs</th>
        </tr>
    </thead>
    <tbody>   
        {{each table1}}
            <tr class="${oddeven}">
                <td class="col disease">${disease}</td>
                <td>${incidence}</td>
                <td>${incidence_all}</td>
                <td>${incidence_percent}</td>
                <td>${daly}</td>
                <td>${incidence_all}</td>
                <td>${daly_percent}</td>
                <td><a href="#" class="link trial dis">${trials}</a></td>
                <td><a href="#" class="link alltrial dis">${trials_all}</a></td>
                <td>${trials_percent}</td>
                <td class="data" id="col0">${trials_incedence}</td>
                <td class="data" id="col1">${trials_daly}</td>
            </tr>
        {{/each}}
    </tbody>
</table>
</script>
<script id="dalydisresTemplate" type="text/x-jquery-tmpl">
<table id="daly-result-table" style="width:95%; height: auto;" class="tablesorter" border="0" cellpadding="0" cellspacing="1">
    <thead>
        <tr>
	        <th class="tooltip" title="Name of the disease">Disease</th>
            <th class="tooltip" title="Number of deaths for each disease/1000">Deaths</th>
            <th class="tooltip" title="Number of deaths attributable to all diseases/1000">Total Deaths</th>
            <th class="tooltip" title="Number of deaths for each disease divided by number of deaths attributable to all diseases">Deaths/Total Deaths</th>
            <th class="tooltip" title="Number of DALYs for each disease/1000">DALYs</th>
            <th class="tooltip" title="Numbers of DALYs attributable to all diseases/1000">Total DALYs</th>
            <th class="tooltip" title="Number of DALYs for each disease divided by number of DALYs attributable to all diseases">DALYs/Total DALYs</th>
            <th class="tooltip" title="Number of publications for each disease in each country (Weighted)">Number of Publications</th>
            <th class="tooltip" title="Total number of publications for all diseases in each country">Total number of Publications</th>
            <th class="tooltip" title="Number of publications for each disease in each country divided by total number of publications for all diseases">Publications/Total num$
            <th class="row tooltip" title="Publications vs. Death Index" id="col0">Publications vs. Deaths</th>
            <th class="row tooltip" title="Publications vs. DALYs Index" id="col1">Publications vs. DALYs</th>
        </tr>
    </thead>
    <tbody>   
        {{each table2}}
            <tr class="${oddeven}">
                <td class="col disease">${disease}</td>
                <td>${incidence}</td>
                <td>${incidence_all}</td>
                <td>${incidence_percent}</td>
                <td>${daly}</td>
                <td>${incidence_all}</td>
                <td>${daly_percent}</td>
                <td><a href="#" class="link pubs dis">${pubs}</a></td>
                <td><a href="#" class="link allpubs dis">${pubs_all}</a></td>
                <td>${trials_percent}</td>
                <td class="data" id="col0">${pubs_incedence}</td>
                <td class="data" id="col1">${pubs_daly}</td>
            </tr>
        {{/each}}
    </tbody>
</table>
</script>
<script id="dialogTemplate" type="text/x-jquery-tmpl">
    <li><a href="${$item.data[0]}" target="_blank">${$item.data[1]}</a></li>
</script>

<div id="linksdialog" title="Links" style="overflow:auto;"><ul id="dialogdata"></ul></div>

<div id="examples" class="examples">
    Examples (<a href="#" id="expand-example">toggle</a>)
    <div style="display:none;" id="examples-btns">
        <a href="#" id="expaper">Tuberculosis</a><br/>
        <a href="#" id="exindia">India</a><br/>
    </div>
</div>

<div class="logo"><img src="images/aksw-logo.png" style="max-height:100px;" /></a></div>

<center><div class="title"><a href="http://aksw.org/Projects/ReDDObservatory" target="_blank" style="color:white;">ReDD Observatory</a></div></center>
<table style="width:100%; margin-left: 0px;" border="0" cellpadding="0" cellspacing="0" id="config">
  <tr>
    <td class="leftbar">
        <div id="tabs">
		    <ul>
			    <li><a href="#country-tab" class="tooltip" title="Disparity for several diseases in one country">Countries Profile</a></li>
			    <li><a href="#disease-tab" id="dis-tab-btn" class="tooltip" title="Disparity for one disease in several countries">Diseases Profile</a></li>
		    </ul>
		    <div id="country-tab">
		        <strong>Select disease:</strong>
                <!-- <div style="overflow:auto;height:200px; width:200px;border:1px solid #336699;padding-left:5px" id="q_disease"> -->
                <div>
                    <select id="q_disease" name="q_disease" style="width:100%">
                    <?php 
                    if(isset($diseases)){
                        foreach($diseases as $key => $disease){
                            echo "<option about='".$disease[1]."'>".$disease[1]."</option>";
                        }
                    }
                    ?>
                    </select>
                </div>
                <!-- </div> -->
                
                <strong>Select countries:</strong>
                <div class="listbox" id="countries">
                    <?php 
                    if(isset($countries)){
                        foreach($countries as $key => $country){
                            echo "<input type='checkbox' about='".$country[1]."'>".$country[1]."<br/>";
                        }
                    }
                    ?>
                </div>
                <div style="text-align:center; width:100%">
                    <button id="doreq" style="width:100px">OK</button>
                </div>
		    </div>
		    <div id="disease-tab">
                <strong>Select country:</strong>
			    <!-- <div style="overflow:auto;height:200px; width:200px;border:1px solid #336699;padding-left:5px" id="q_disease"> -->
                <div>
                    <select id="country_dis" name="country" style="width:100%">
                    <?php 
                    if(isset($countries)){
                        foreach($countries as $key => $country){
                            echo "<option about='".$country[1]."'>".$country[1]."</option>";
                        }
                    }
                    ?>
                    </select>
                </div>
                <!-- </div> -->

                <strong>Select diseases:</strong>
			    <div class="listbox" id="diseases_dis">
                    <?php 
                    if(isset($diseases)){
                        foreach($diseases as $key => $disease){
                            echo "<input type='checkbox' about='".$disease[1]."'>".$disease[1]."<br/>";
                        }
                    }
                    ?>
                </div>

                <div style="text-align:center; width:100%">
                    <button id="doreq-disease" style="width:100px">OK</button>
                </div>

		    </div>
	    </div>
    </td>
    <td style="text-align: left; vertical-align: top;">
        <div id="results">
            <ul>
			    <li><a href="#countries-tab" class="tooltip" title="Disparity values based on Trials">Trials</a></li>
			    <li><a href="#diseases-tab" class="tooltip" title="Disparity values based on Publications">Publications</a></li>
		    </ul>
		    <div id="countries-tab">
		        <div id="results">No results yet.</div>
		        <div id="visual-country"></div>
		    </div>
		    <div id="diseases-tab">
		        <div id="results">No results yet.</div>
		        <div id="visual-disease"></div>
		    </div>
        </div>
        <div id="time"></div>
        <div id="loader" style="text-align:center; width:100%; display:none;">
            <img src="images/spinner.gif" style="margin: 10px;"><br/><b>Loading..</b>
        </div>
    </td>
  </tr>
</table>
</body>
</html>

