String.prototype.charPlus = function(){
    return String.fromCharCode( this.charCodeAt(0) + 1 );
}

$(document).ready(function(){
    var serverData = null;
    var req_country = null;
    var req_disease = null;

    // data visualistaion function
    var drawChart = function(data_table, placeholder){
        //var data_table = $("#result-table");
        if( data_table.length < 0 ) return;
        
        var cols = []; // countries
        var rows = []; // columns
        var data = {}; // data
        
        $(".col", data_table).each(function(index,item){
            cols.push( $(item).text() );
        });
        $(".row", data_table).each(function(index,item){
            rows.push({id:$(item).attr('id'), text:$(item).text()});
        });
        $(".data", data_table).each(function(index,item){
            if( typeof data[$(item).attr('id')] == 'undefined' ) data[$(item).attr('id')] = [];
            data[$(item).attr('id')].push( $(item).text().replace(/\..+/g,'') );
        });
        
        /*
        google vis
        */
        var item, subitem, i = 0, k = 0;
        var letter = 'a';
        var gdata = [];
        for(i = 0; i < rows.length; i++ ){
            gdata[i] = new google.visualization.DataTable();
            gdata[i].addColumn('string', '', 'Country');        
            item = rows[i];
            gdata[i].addColumn('number', item.text);//, letter );
            gdata[i].addRows( data[rows[0].id].length );
            //letter = letter.charPlus();
        }

        for(i = 0; i < cols.length; i++ ){
            item = cols[i];
            for (k = 0; k < rows.length; k++ ){
                gdata[k].setValue(i, 0, countryCodes[item]);
                subitem = data[rows[k].id][i];
                gdata[k].setValue(i, 1, parseFloat(subitem) );
            }
        }
        
        for(i = 0; i < gdata.length; i++){
            var divid = $(placeholder).attr('id');
            $(placeholder).append( $("<br/><div id='googlevis-"+divid+i+"'></div><br/>") );

            var chart = new google.visualization.GeoMap( document.getElementById("googlevis-"+divid+i) );
            chart.draw(gdata[i], {});
        }
        
        /*
         * Table vis
         */
        
        var table = "<table id=\"restab\" style=\"display:block;\" >\
        <caption>Trial vs. Death</caption>\
        <thead>%HEAD%</thead>\
        <tbody>%BODY%</tbody>\
        </table>";
        
        var i = 0;
        
        // draw head
        var head = "<tr><td>&nbsp;</td>";
        for(i = 0; i < cols.length; i++){
            head += "<th scope=\"col\">"+cols[i]+"</th>";
        };
        head += "</tr>";
        
        // draw data
        var body = "";        
        var r = 0;
        for(r = 0; r < rows.length; r++){
            body += "<tr><th score=\"row\">"+rows[r].text+"</th>";
            for(i = 0; i < cols.length; i++){
                body += "<td>"+data[rows[r].id][i]+"</td>";
            }
            body += "</tr>";
        }
        body += "</tr>";
        
        table = table.replace("%HEAD%", head);
        table = table.replace("%BODY%", body);
        
        $('body').append( $(table) );
        $("#restab").visualize({height:200}).appendTo(placeholder).trigger('visualizeRefresh');
        
        $("#restab").remove();
    };
    
    var clearData = function(){
        $("#countries-tab > #results").empty();
        $("#diseases-tab > #results").empty();
        $("#time").empty();
        $(".visualize").remove();
    };
    
    // request
    $("#doreq").click(function(){
        // clear old stuff
        clearData();
        // show loader
        $("#loader").slideDown(200);
        
        // set request
        var req = {};
        // get disease
        req_disease = $("#q_disease > option:selected").val();
        req_country = null;
        req.disease = req_disease;
        // get countries
        req.countries = [];
        $("#countries > input:checked").each(function(i, item){
            req.countries.push( $(item).attr('about') );
        });
        
        $.post("result.php", req, function(data){
            $("#loader").slideUp();
            // store data
            serverData = data;
            // draw tables
            $("#deathresTemplate").tmpl(data).appendTo("#countries-tab > #results");
            $("#dalyresTemplate").tmpl(data).appendTo("#diseases-tab > #results");
            // draw time
            $("#time").append( "Request was executed in "+data.time+" seconds." );
            // sorters
            $("#death-result-table").tablesorter(); 
            $("#daly-result-table").tablesorter(); 
            // draw charts
            drawChart( $("#death-result-table"), $("#visual-country") );
            drawChart( $("#daly-result-table"), "#visual-disease" );
            // tooltip test
        	$(".tooltip").tipTip({defaultPosition:"top"});
        }, 'json');
    });
    
    // disease request
    $("#doreq-disease").click(function(){
        // clear old stuff
        clearData();
        // show loader
        $("#loader").slideDown(200);
        
        // set request
        var req = {};
        // get disease
        req_country = $("#country_dis > option:selected").val();
        req.country = req_country;
        req_disease = null;
        // get countries
        req.diseases = [];
        $("#diseases_dis > input:checked").each(function(i, item){
            req.diseases.push( $(item).attr('about') );
        });
        
        $.post("result_dis.php", req, function(data){
            $("#loader").slideUp();
            // store data
            serverData = data;
            // draw tables
            $("#deathdisresTemplate").tmpl(data).appendTo("#countries-tab > #results");
            $("#dalydisresTemplate").tmpl(data).appendTo("#diseases-tab > #results");
            // draw time
            $("#time").append( "Request was executed in "+data.time+" seconds." );            
            // sorters
            $("#death-result-table").tablesorter(); 
            $("#daly-result-table").tablesorter(); 
            // draw charts
            drawChart( $("#death-result-table"), $("#visual-country") );
            drawChart( $("#daly-result-table"), "#visual-disease" );
            // tooltip test
        	$(".tooltip").tipTip({defaultPosition:"top"});
        }, 'json'); 
    });
    
    // refresh visual stuff
    $("#results").bind('tabsshow', function(){
        $('.visualize').trigger('visualizeRefresh');
    });
    
    $(".link").live("click", function(){
        if( $(this).hasClass("trial") ){
            // get country
            var key = $(this).hasClass("dis")?"disease":"country";
            var itm = $("."+key, $(this).parent().parent()).text();
            // find data
            var data = serverData.table1;
            for(var i = 0; i < data.length; i++){
                if( data[i][key] == itm ){
                    if( req_country == null ){
                        window.open('links.php?country='+itm+'&disease='+req_disease+'&action=trial');
                    }else if( req_disease == null ){
                        window.open('links.php?country='+req_country+'&disease='+itm+'&action=trial');
                    }
                    break;
                }
            }
        }else if( $(this).hasClass("alltrial") ){
            // get country
            var key = $(this).hasClass("dis")?"disease":"country";
            var itm = $("."+key, $(this).parent().parent()).text();
            // find data
            var data = serverData.table1;
            for(var i = 0; i < data.length; i++){
                if( data[i][key] == itm ){
                    if( req_country == null ){
                        window.open('links.php?country='+itm+'&disease='+req_disease+'&action=alltrials');
                    }else if( req_disease == null ){
                        window.open('links.php?country='+req_country+'&disease='+itm+'&action=alltrials');
                    }
                    break;
                }
            }
        }else if( $(this).hasClass("pubs") ){
            // get country
            var key = $(this).hasClass("dis")?"disease":"country";
            var itm = $("."+key, $(this).parent().parent()).text();
            // find data
            var data = serverData.table2;
            for(var i = 0; i < data.length; i++){
                if( data[i][key] == itm ){
                    if( req_country == null ){
                        window.open('links.php?country='+itm+'&disease='+req_disease+'&action=pubs');
                    }else if( req_disease == null ){
                        window.open('links.php?country='+req_country+'&disease='+itm+'&action=pubs');
                    }
                    break;
                }
            }
        }else if( $(this).hasClass("allpubs") ){
            // get country
            var key = $(this).hasClass("dis")?"disease":"country";
            var itm = $("."+key, $(this).parent().parent()).text();
            // find data
            var data = serverData.table2;
            for(var i = 0; i < data.length; i++){
                if( data[i][key] == itm ){
                    if( req_country == null ){
                        window.open('links.php?country='+itm+'&disease='+req_disease+'&action=allpubs');
                    }else if( req_disease == null ){
                        window.open('links.php?country='+req_country+'&disease='+itm+'&action=allpubs');
                    }
                    break;
                }
            }
        }
        return false;
    });
    
    // paper example
    $("#expaper").click(function(){
        // select tab
        $("#tabs").tabs("select", 0);
        // select needed stuff
        $("#q_disease > option[about*='Tuberculosis']").attr("selected", true);
        $("#countries > input[about*='Argentina']").attr("checked", true);
        $("#countries > input[about*='Brazil']").attr("checked", true);
        $("#countries > input[about*='China']").attr("checked", true);
        $("#countries > input[about*='Colombia']").attr("checked", true);
        $("#countries > input[about*='Egypt']").attr("checked", true);
        $("#countries > input[about*='India']").attr("checked", true);
        $("#countries > input[about*='Iran']").attr("checked", true);
        $("#countries > input[about*='Mexico']").attr("checked", true);
        $("#countries > input[about*='Peru']").attr("checked", true);
        $("#countries > input[about*='Russia']").attr("checked", true);
        $("#countries > input[about*='South Africa']").attr("checked", true);
        $("#countries > input[about*='Thailand']").attr("checked", true);
        $("#countries > input[about*='United States']").attr("checked", true);
    });
    
    $("#exindia").click(function(){
        // select tab
        $("#tabs").tabs("select", 1);
        // select needed stuff
        $("#country_dis > option[about*='India']").attr("selected", true);
        $("#diseases_dis > input[about*='Tuberculosis']").attr("checked", true);
	    $("#diseases_dis > input[about*='Schizophrenia']").attr("checked", true);
	    $("#diseases_dis > input[about*='Parkinson disease']").attr("checked", true);
	    $("#diseases_dis > input[about*='Breast cancer']").attr("checked", true);
	    $("#diseases_dis > input[about*='Leukaemia']").attr("checked", true);
	    $("#diseases_dis > input[about*='Diabetes mellitus']").attr("checked", true);
	    $("#diseases_dis > input[about*='Bladder cancer']").attr("checked", true);
	    $("#diseases_dis > input[about*='Hypertensive heart disease']").attr("checked", true);
	    $("#diseases_dis > input[about*='Stomach cancer']").attr("checked", true);
    });
    
    $("#expand-example").click(function(){
        $("#examples-btns").toggle();
        return false;
    });
    
    // init tabs
	$('#tabs').tabs();
	$('#results').tabs();
	
	// tooltip test
	$(".tooltip").tipTip({defaultPosition:"top"});
});






