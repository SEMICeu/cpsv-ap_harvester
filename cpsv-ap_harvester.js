/**
 * Cleans the graph indicated in the configuration file (config.ini).
 */
function clean(){	
	var btn = document.getElementById("clean");
	btn.disabled = true;
	btn.innerHTML = "Removing data";

	$.ajax({
		type: "GET",
		url: "http://cpsv-ap.semic.eu/cpsv-ap_harvester/pages/clear.php",
		data: { },
		async: false,
		success: function (response) {
			alert("The data has been deleted.");
		},
		error: function () {
			alert('There was an error!');
		},
	});
	
	btn.disabled = false;
	btn.innerHTML = "Clean data";
}

/**
 * Harvests the URLs indicated in the configuration file (config.ini).
 */
function harvest(){
	var btn = document.getElementById("harvest");
	btn.disabled = true;
	btn.innerHTML = "Harvesting";
	
	$.ajax({
		type: "GET",
		url: "http://cpsv-ap.semic.eu/cpsv-ap_harvester/pages/harvest.php",
		data: { p: '1' },
		async: false,
		success: function (response) {
			alert("The following files have been harvested:\n"+response);
		},
		error: function () {
			alert('There was an error!');
		},
	});

	btn.disabled = false;
	btn.innerHTML = "Harvest";
}

function expandPanel(){
	if($(".schedule-panel").is(':visible')){
		$(".schedule-panel").hide();
  	}
  	else {
    	$(".schedule-panel").show();
  	}
}

function schedule(){
	var btn = document.getElementById("schedule");
	btn.disabled = true;
	btn.innerHTML = "Scheduling";

	var cronExpression = $('#scheduler').cron("value");

	$.ajax({
		type: "POST",
		url: "http://cpsv-ap.semic.eu/cpsv-ap_harvester/pages/schedule.php",
		data: { cronExpression : cronExpression},
		async: false,
		success: function (response) {
			alert("The scheduling was set.");
		},
		error: function () {
			alert('There was an error!');
		},
	});

	btn.disabled = false;
	btn.innerHTML = "Confirm";

	expandPanel();
}

/**
 * Parses the text introduced by parameter, splitting it per rows.
 * #param {string} text - text to be parsed.
 * #returns {list} List with a triple per cell.
 */
function parseResult(text){
	var lines = text.split("\n");
	
	return lines;
}

/**
 * Gets all the identifiers of those elements that are typed as a determined class.
 * #param {string} text - Text that contains the triples where to look for the elements.
 * #param {string} classURI - URI of the class to look for.
 * #returns {list} List with the URIs typed as a class.
 */
function getclassURIs (text, classURI){
	//var lines = parseResult(text); //Get the different triples
	var row, i, URIs, aux="";
	var lines = text;
	
	URIs = "";
	for (i=0; i<lines.length; i++){
		row = lines[i].split(" ");
		if (row[1] == "http://www.w3.org/1999/02/22-rdf-syntax-ns#type") { 
			aux = row[2].split('\r');
			if ( aux[0] == classURI) {
				URIs = URIs + "," + row[0];
			}
		}
	}
	
	return URIs;
}

/**
 * Gets all the properties of a determined element, indicated by its URI (identifier).
 * #param {list} lines - Rows that contains the triples where to look for the properties.
 * #param {string} URI - URI (identifier) of the element to extract the properties.
 * #returns {list} List with the properties of the element per cell.
 */
function getURIproperties (lines, URI) {	
	var row, i, props;
	
	props = "";
	for (i=0; i<lines.length; i++){
		row = lines[i].split(" ");
		if (row[0] == URI) { 
			props = props + "##" + lines[i];
		}
	}
	
	return props;
}

/**
 * Removes the data existing in an HTML table.
 * #param {string} tableID - ID of the table that contains the data in the HTML page.
 */
function removeTable(tableID) {
	var tableHeaderRowCount = 1;
	var table = document.getElementById(tableID);
	var rowCount = table.rows.length;
	
	if(rowCount > 1){
		for (var i = tableHeaderRowCount; i < rowCount; i++) {
			table.deleteRow(tableHeaderRowCount);
		}
	}

}

/**
 * Inserts a row in a table of the HTML page.
 * #param {string} tableID - ID of the HTML table where to add the row.
 */
function createRow (tableID){
	var tableHeader = document.getElementById("tableHeader");
	
	if (tableHeader.hidden) tableHeader.hidden = false;
	
	var tableRef = document.getElementById(tableID); // Added in v2,to insert in the body instead of the header

	// Insert a row in the table at the last row
	var newRow   = tableRef.insertRow(tableRef.rows.length);

	// Insert a cell in the row at index 0
	var newCell  = newRow.insertCell(0);
	
	return newCell;
}

/**
 * Appends content in the input cell.
 * #param {cell} cell - Cell where to append the HTML.
 * #param {string} v - String to append.
 * #param {string} style - Style to give to the string.
 */
function appendContent (cell, v, style){
	var newp = document.createElement("p");
	newp.innerHTML = v;
	newp.className = style;
	cell.appendChild(newp);
	return newp;
}

/**
 * Translates the property URI to a user-readable name indicated in the mapping fields file (mapping_fields.ini).
 * #param {string} className - Value of the class from which to obtain the property name.
 * #param {string} propURI - URI of the property.
 */
function getPropertyName(className, propURI){
	$.ajax({
		type: "GET",
		url: "http://cpsv-ap.semic.eu/cpsv-ap_harvester/pages/getProperties.php",
		data: { "c":className, "p":propURI },
		async: false,
		success: function (response) {
			name = response;
		},
	});
	return name;
}

/**
 * Update the values showed in a table.
 * #param {string} tableID - ID of the HTML table to update.
 * #param {string} className - Value of the class to which the values will be included.
 * #param {string} URIs - URIs with the elements to include.
 * #param {string} data - Row data harvested.
 */
function updateTable (tableID, className, URIs, data) {
	var lines, properties, i, j, k, aux, aux2, values, URI, table, prop, propName, value, newCell, p, append = false;
	
	//lines = parseResult(data);
	lines = data;
	values = URIs.split(",");
	
	for (i=0; i<values.length; i++){
		URI = values[i];
		if (URI != "") {
			properties = "";
			properties = getURIproperties(lines, URI);
			aux = "";
			aux = properties.split("##");
			for (j=1; j<aux.length; j++){
				prop = aux[j].split(" ");
				propURI = prop[1];
				append = false;
				if(j == 1) {
					newCell = createRow (tableID);
					p = appendContent (newCell, className, "class-data");
					p.setAttribute("onclick","updateDetailData('"+URI+"', '"+className+"')");
					p.setAttribute("style", "cursor:pointer");
					appendContent (newCell, "<b>Identifier: </b>" + URI, "mydata");
				}
				//Only show one property per class
				switch (className){
					case "Channel":	{
						if (propURI == "http://data.europa.eu/m8g/hasContactPoint"){
							propName = "Contact Point";
							append = true;
						}
						break;
					}
					case "Address":	{
						if (propURI == "http://www.w3.org/ns/locn#fullAddress"){
							propName = "Full Address";
							append = true;
						}
						break;
					}
					case "Contact Point":	{
						if (propURI == "http://www.w3.org/2006/vcard/ns#hasURL"){
							propName = "URL";
							append = true;
						}
						break;
					}
					case "Cost":	{
						if (propURI == "http://purl.org/dc/terms/description"){
							propName = "Description";
							append = true;
						}
						break;
					}
					case "PeriodofTime":	{
						break;
					}
					/* case "FormalFramework":	{
						if (propURI == "http://data.europa.eu/eli/ontology#id_local"){
							propName = "Name";
							append = true;
						}
						break;
					} */
					case "Participation":	{ //
						if (propURI == "http://purl.org/dc/terms/description"){
							propName = "Description";
							append = true;
						}
						break;
					}


					default:	{
						if (propURI == "http://purl.org/dc/terms/title"){
							propName = "Name";
							append = true;
						}
						break;
					}
				}
				if (append) {
					value = "";
					for (k=2; k<prop.length; k++){
						value = value + " " + prop[k];
					}
					appendContent (newCell, "<b>" + propName + ":</b>" + value, "mydata");
				}
			}
		}
	}
}

/**
 * Get the data stored in the triple store.
 * #returns {string} - Data stored.
 */
function getStoredData (){
	var result = "", endpoint = "";	
	var xhttp = new XMLHttpRequest();
	
	xhttp.onreadystatechange = function() {
		if (xhttp.readyState == 4 && xhttp.status == 200) {
			endpoint = xhttp.responseText;
			}
	};
	xhttp.open("GET", "http://cpsv-ap.semic.eu/cpsv-ap_harvester/pages/getEndPoint.php", false); //synchronized
	xhttp.send();
	
	if (endpoint != "") {
		var xhttp2 = new XMLHttpRequest();
		xhttp2.onreadystatechange = function() {
			if (xhttp2.readyState == 4 && xhttp2.status == 200) {
				result = xhttp2.responseText;
			}
		};
		xhttp2.open("GET", "http://cpsv-ap.semic.eu/cpsv-ap_harvester/pages/show.php", false); //synchronized
		xhttp2.send();
		
	}
	
	return result;
}

/**
 * Shows in the HTML table the triples of a determined class selected by the user.
 */
function showClass(){
	var btn = document.getElementById("showClass");
	btn.disabled = true;
	btn.innerHTML = "Loading";
	var endpoint="", result="", data="";
	
	var btn2 = document.getElementById("showAll");
	btn2.disabled = false;
	btn2.innerHTML = "Visualise all data";
	
	var tableHeader = document.getElementById("tableHeader");
	tableHeader.hidden = true;
	
	cleanDetailData();
	
	data = getStoredData();
	
	result = parseResult (data);
	
	removeTable("data");
	
	//get selected class
	var e = document.getElementById("classes");
	var className = e.options[e.selectedIndex].value;
	
	//show Public Services
	var URIs;
	switch (className){
		case "PS": {
			URIs = getclassURIs(result, "http://purl.org/vocab/cpsv#PublicService");
			updateTable("data", "PublicService", URIs, result);
			break;
		}
		case "BE": {
			URIs = getclassURIs(result, "http://data.europa.eu/m8g/BusinessEvent");
			updateTable("data", "BusinessEvent", URIs, result);
			break;
		}
		case "LE": {
			URIs = getclassURIs(result, "http://data.europa.eu/m8g/LifeEvent");
			updateTable("data", "LifeEvent", URIs, result);
			break;
		}
		case "Participation": {
			URIs = getclassURIs(result, "http://data.europa.eu/m8g/Participation");
			updateTable("data", "Participation", URIs, result);
			break;
		}
		case "Criterion": {
			URIs = getclassURIs(result, "http://data.europa.eu/m8g/CriterionRequirement");
			updateTable("data", "Criterion", URIs, result);
			break;
		}
		case "Evidence": {
			URIs = getclassURIs(result, "http://data.europa.eu/m8g/Evidence");
			updateTable("data", "Evidence", URIs, result);
			break;
		}
		case "Output": {
			URIs = getclassURIs(result, "http://data.europa.eu/m8g/Output");
			updateTable("data", "Output", URIs, result);
			break;
		}
		case "Channel": {
			URIs = getclassURIs(result, "http://data.europa.eu/m8g/Channel");
			updateTable("data", "Channel", URIs, result);
			break;
		}
		case "FF": {
			URIs = getclassURIs(result, "http://purl.org/vocab/cpsv#FormalFramework");
			updateTable("data", "FormalFramework", URIs, result);
			break;
		}
		case "Rule": {
			URIs = getclassURIs(result, "http://purl.org/vocab/cpsv#Rule");
			updateTable("data", "Rule", URIs, result);
			break;
		}
		case "Agent": {
			URIs = getclassURIs(result, "http://purl.org/dc/terms/Agent");
			updateTable("data", "Agent", URIs, result);
			break;
		}
		case "Contact": {
			URIs = getclassURIs(result, "http://www.w3.org/2006/vcard/ns#Contact");
			updateTable("data", "Contact", URIs, result);
			break;
		}
		case "PO": {
			URIs = getclassURIs(result, "http://data.europa.eu/m8g/PublicOrganisation");
			updateTable("data", "PublicOrganisation", URIs, result);
			break;
		}
		case "Cost": {
			URIs = getclassURIs(result, "http://data.europa.eu/m8g/Cost");
			updateTable("data", "Cost", URIs, result);
			break;
		}
		case "PT": {
			URIs = getclassURIs(result, "http://data.europa.eu/m8g/PeriodOfTime");
			updateTable("data", "PeriodofTime", URIs, result);
			break;
		}
		case "Address": {
			URIs = getclassURIs(result, "http://www.w3.org/ns/locn#Address");
			updateTable("data", "Address", URIs, result);
			break;
		}
                case "Concept": {
                         URIs = getclassURIs(result, "http://www.w3.org/2004/02/skos/core#Concept");
                         updateTable("data", "Concept", URIs, result);
                         break;
                 }


	}
	
	btn.disabled = false;
	btn.innerHTML = "Visualise class";

	alert ("The data is loaded");
	
}

/**
 * Shows in the HTML table all the harvested data, ordered by classes.
 */
function showAll () {
	var btn = document.getElementById("showAll");
	btn.disabled = true;
	btn.innerHTML = "Loading";
	var endpoint="", result="", data="";
	
	var btn2 = document.getElementById("showClass");
	btn2.disabled = false;
	btn2.innerHTML = "Visualise class";
	
	var tableHeader = document.getElementById("tableHeader");
	tableHeader.hidden = true;
	
	cleanDetailData();
		
	data = getStoredData();
	
	result = parseResult(data);
	
	removeTable("data");
	
	var URIs;
	
	//show the Public Services
	URIs = getclassURIs(result, "http://purl.org/vocab/cpsv#PublicService");
	updateTable("data", "PublicService", URIs, result);
	
	//show the Business Events
	URIs = getclassURIs(result, "http://data.europa.eu/m8g/BusinessEvent");
	updateTable("data", "BusinessEvent", URIs, result);

	//show the Life Events
	URIs = getclassURIs(result, "http://data.europa.eu/m8g/LifeEvent");
	updateTable("data", "LifeEvent", URIs, result);
	
	//show the Participations
	URIs = getclassURIs(result, "http://data.europa.eu/m8g/Participation"); //
	updateTable("data", "Participation", URIs, result);
	
	//show the Criteria
	URIs = getclassURIs(result, "http://data.europa.eu/m8g/CriterionRequirement"); //
	updateTable("data", "Criterion", URIs, result);
	
	//show the Evidences
	URIs = getclassURIs(result, "http://data.europa.eu/m8g/Evidence");
	updateTable("data", "Evidence", URIs, result);

	//show the Outputs
	URIs = getclassURIs(result, "http://data.europa.eu/m8g/Output"); //
	updateTable("data", "Output", URIs, result);

	//show the Channels
	URIs = getclassURIs(result, "http://data.europa.eu/m8g/Channel"); //
	updateTable("data", "Channel", URIs, result);

	//show the Formal Frameworks
	URIs = getclassURIs(result, "http://purl.org/vocab/cpsv#FormalFramework"); //
	updateTable("data", "FormalFramework", URIs, result);

	//show the Rules
	URIs = getclassURIs(result, "http://purl.org/vocab/cpsv#Rule"); //
	updateTable("data", "Rule", URIs, result);

	//show the Agents
	URIs = getclassURIs(result, "http://purl.org/dc/terms/Agent"); //
	updateTable("data", "Agent", URIs, result);

	//show the Contacts
	URIs = getclassURIs(result, "http://www.w3.org/2006/vcard/ns#Contact"); //
	updateTable("data", "Contact", URIs, result);

	//show the Public Organisations
	URIs = getclassURIs(result, "http://data.europa.eu/m8g/PublicOrganisation"); //
	updateTable("data", "PublicOrganisation", URIs, result);

	//show the Costs
	URIs = getclassURIs(result, "http://data.europa.eu/m8g/Cost"); //
	updateTable("data", "Cost", URIs, result);

	//show the Period of Time
	URIs = getclassURIs(result, "http://data.europa.eu/m8g/PeriodOfTime"); //
	updateTable("data", "PeriodofTime", URIs, result);

	//show the Address
	URIs = getclassURIs(result, "http://www.w3.org/ns/locn#Address"); //
	updateTable("data", "Address", URIs, result);
	
        //show the Concept
        URIs =  getclassURIs(result, "http://www.w3.org/2004/02/skos/core#Concept"); //
        updateTable("data", "Concept", URIs, result);

	btn.disabled = false;
	btn.innerHTML = "Visualise all data";
	
	alert ("The data is loaded");
}

/**
 * Cleans the data showed on the right.
 */
function cleanDetailData () {
	var div = document.getElementById("detailResult");
	div.innerHTML = "";
	
	var newp = document.createElement("p");
	newp.innerHTML = "";
	newp.setAttribute("id", "detailDataTitle");
	div.appendChild(newp);
	
	newp = document.createElement("p");
	newp.innerHTML = "";
	newp.setAttribute("id", "detailDataBody");
	div.appendChild(newp);
	
}

/**
 * Gets the properties of a URI.
 * #param {string} URI - Value of the subject from which to obtain the properties.
 * #returns - List of properties (propType propValue).
 */
function getTriplesURI(URI, classType){
	var props="";
	
	$.ajax({
		type: "GET",
		url: "http://cpsv-ap.semic.eu/cpsv-ap_harvester/pages/getTriplesURI.php",
		data: { "URI":URI, "class":classType },
		async: false,
		success: function (response) {
			props = response;
		},
	});
	return props;
}

/**
 * Update the data on the right with the detail data.
 * #param {string} URI - URI of the element of which to show the data.
 * #param {string} classType - Type of the class of the URI (Public Service, Business Event...)
 */
function updateDetailData (URI, classType) {
	var triples = getTriplesURI(URI, classType);
	
	cleanDetailData();
	
	var div = document.getElementById("detailResult");
	
	var title = document.getElementById("detailDataTitle");
	title.innerHTML = classType;
	title.className = "title";
	
	var body = document.getElementById("detailDataBody");
	body.innerHTML = "<b>Identifier: </b>" + URI;
	body.className = "mydata";
	
	var properties = triples.split("@#");
	var i, j, prop, propType, propValue, cad="";
	for (i=0; i<properties.length; i++){
		prop = properties[i].split("##");
		propType = prop[0];
		propValue = prop[1];
		newp = document.createElement("p");
		newp.innerHTML = "<b>" + propType+ ": </b>" + propValue;
		newp.className = "mydata";
		div.appendChild(newp);
	}
	
}

