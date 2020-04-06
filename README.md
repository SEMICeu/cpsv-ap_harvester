# CPSV-AP Harvester

The Public Service Description Harvester allows public administrations to get the data in a faster and more efficient way. It eases the creation of an integrated view on public services and all related content within a specific country or region, avoiding the manual update of the public service descriptions owned by different competent authorities on multiple levels of administration (national, regional, local) on a central catalogue.

The Public Service Description Harvester allows the user to harvest machine-readable files described in RDF/XML, RDF Turtle, N-triples and a spreadsheet (converted in JSON-LD). Before harvesting, the files are validated against the CPSV-AP Validator (https://www.itb.ec.europa.eu/shacl/cpsv-ap/api/validate). The data is stored inside a triple store, giving the user the possibility to browse through all the harvested data in a unique place.

Additionally, it exposes its data through an API for external tools to fetch the harvested data, e.g. the CPSV-AP Description Editor (https://github.com/catalogue-of-services-isa/cpsv-ap_editor) imports the harvested data by connecting to this API.

## Installation instructions <a name="installation"></a>

1.	Download the source code from GitHub: 
https://github.com/catalogue-of-services-isa/CPSV-AP_harvester;
2.	Install Virtuoso as triple store;
3.	Create a local server to allocate the code. We used Xampp and Apache on that purpose;
4.	Unpack the binary distribution locally;
5.	Copy the unpacked folder in a new folder under the SERVER_HOME (Apache or any other used); and
6.	Update the JavaScript file “cpsv-ap_harvester_2.js” with the location of the PHP files by replacing all the URLs where to invoke the PHP files with the new URL of the PHP file (SERVER_HOME/HARVESTER_FOLDER/pages/). For example, “http://localhost:80/harvester/pages/clear.php” was replaced by http://CPSV-AP.semic.eu:8890/CPSV-AP_harvester/pages/clear.php when moving to the local installation to the server.


## Configuration <a name="configuration"></a>

The harvester works as a script. Its main functionality is to go through the different sources indicated in a configuration file, gather the available data and store it in a central triple store. Note that the source needs to be configured in order to grant access to the harvester to access to the files.

The configuration file has to be modified directly on the server by the tool administrator. It contains four main configuration data:
* the SPARQL endpoint of the triple store, 
* the URI of the graph where to store the harvested data,
*the URLs where the CPSV-AP files are stored on the source servers and 
* the type of data that is stored in those input files (RDF/XML, RDF Turtle, N-Triples, JSON files for the Estonian transformation or xlsx). 

The tool can harvest five different file formats:
*	RDF/XML, to be indicated as “xml” in the configuration file;
*	RDF turtle, to be indicated as “turtle” in the configuration file;
*	N-Triples, to be indicated as “nt” in the configuration file; and
*	The Estonian JSON, to be indicated as “jsonEstonia” in the configuration file; and
*   Spreadsheet, indicated as 'xlsx' and converted to 'json-ld' in the configuration file.

An example of the configuration file is provided below.

    [Mandatory]
    # SPARQL Endpoint
    endpointURI = http://localhost:8890/sparql
    # default (named) graph
    graphURI = http://localhost:8890/pilot
    # No spaces after delimeter between values
    PoolURI = http://localhost/estonia/partial.json,http://localhost/estonia2/partial.json,http://localhost/youreurope/sample-    xml.rdf,http://localhost/youreurope/sample-turtle.ttl,http://localhost/youreurope/sample-n-triples.nt, http://localhost/youreurope/sample-xlsx.xlsx
    # Each value correspond to the values provided below in the order provided
    TypeURI = jsonEstonia,jsonEstonia,xml,turtle,nt,xlsx

## Usage instructions <a name="usageInstructions"></a>

The Harvester webpage acts as the user interface for the harvester. It is linked to a JavaScript file that describes the different actions that the user can execute. Those actions are:
*	Visualise all the data stored in the triple store and browse through the data using filters;
*	Harvest the files indicated in the configuration file and store them in the triple store;
*	Clean the existing data from the triple store; and
*   Schedule the automatic harvesting of the files indicated in the configuration file and store them in the triple store.

