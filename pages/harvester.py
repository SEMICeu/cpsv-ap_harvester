# -*- coding: utf-8 -*-
"""
Harvests JSON objects over HTTP and maps to CPSV-AP vocabulary
and save to a triple store

Python ver: 3.5
"""

__author__ = 'PwC EU Services'

from json_mapping_estonia import json_to_rdf
import time
import json
import urllib.parse
import base64

from configparser import ConfigParser

import requests
from SPARQLWrapper import SPARQLWrapper, POST, JSON
from rdflib import Graph, ConjunctiveGraph
from rdflib.plugins.stores.sparqlstore import SPARQLUpdateStore
from termcolor import colored


def main():
    # Track executing time
    # start_time = time.time()
    headers = {'content-type': 'application/json'}  # HTTP header content type
    # Configurations
    config = ConfigParser()
    config.read('config_3.ini')
    
    endpoint_uri = config['Mandatory']['endpointURI']
    graph_uri = config['Mandatory']['graphURI']
    pool_uri = (config['Mandatory']['poolURI']).split(',')
    type_uri = (config['Mandatory']['typeURI']).split(',')

    # Set up endpoint and access to triple store
    sparql = SPARQLWrapper(endpoint_uri)
    sparql.setReturnFormat(JSON)
    sparql.setMethod(POST)
    store = SPARQLUpdateStore(endpoint_uri, endpoint_uri)

    # Specify the (named) graph we're working with
    sparql.addDefaultGraph(graph_uri)

    # Create an in memory graph
    g = Graph(store, identifier=graph_uri)

    # Build the RDF from the JSON source data
    # This function is to be called for each URL in the pool to harvest, in case that the source is in json, with the Estonian mapping
    def rdf(urlrdf, f):
        input = Graph()
        input.open("store2", create=True)
        input.parse(urlrdf, format=f)

        for s, p, o in input:
            g.add((s, p, o))

        input.close()

    def rdf_data(rdfobject, f):
        input = ConjunctiveGraph()
        input.open("store2", create=True)
        input.parse(data=rdfobject, format=f)

        #print(input.serialize(format='json-ld', auto_compact=True, indent=4))

        for s, p, o in input:
            g.add((s, p, o))

        input.close()


    # Set counter
    c = 0

    # Loop over all URI in the pool
    while c < len(pool_uri):
        #print(pool_uri[c],type_uri[c])

        if type_uri[c] == 'xlsx':
            url = "http://cpsv-ap.semic.eu/cpsv-ap_harvester/intapi/v1/importSpreadsheetFromURL?spreadsheetURL=" + urllib.parse.quote(pool_uri[c])
            text_json = requests.get(url).text
            my_json= json.loads(text_json)
            type_uri[c] = 'json-ld'

	#validation
        url = "https://www.itb.ec.europa.eu/shacl/cpsv-ap/api/validate"
        if type_uri[c] == 'xml':
            myobj = { "contentSyntax": "application/rdf+xml", "contentToValidate": pool_uri[c], "embeddingMethod": "URL", "reportSyntax": "application/ld+json" }
        if type_uri[c] == 'turtle':
            myobj = { "contentSyntax": "text/turtle", "contentToValidate": pool_uri[c], "embeddingMethod": "URL", "reportSyntax": "application/ld+json" }
        if type_uri[c] == 'nt':
            myobj = { "contentSyntax": "application/n-triples", "contentToValidate": pool_uri[c], "embeddingMethod": "URL", "reportSyntax": "application/ld+json" }
        if type_uri[c] == 'json-ld':
            data = base64.urlsafe_b64encode(text_json.encode("utf-8")).decode('utf-8')
            myobj = { "contentSyntax": "application/ld+json", "contentToValidate": data, "embeddingMethod": "BASE64", "reportSyntax": "application/ld+json" }

#myobj = { "contentSyntax": "application/ld+json", "contentToValidate": pool_uri[c], "embeddingMethod": "BASE64", "reportSyntax": "application/ld+json" }
        #data = base64.urlsafe_b64encode(myobj).encode()).decode()

        result_text_json = requests.post(url, json=myobj).text
        my_json= json.loads(result_text_json)
        #print(result_text_json)
        #print(my_json.get("sh:conforms"))
        if(my_json.get("sh:conforms") or type_uri[c] == 'jsonEstonia'):
        #if 1:
        #print(d2)
        #print(d2['id'])

        #print("with:colon is equal to {d['sh-conforms']}")
        #print({'sh-conforms'}.format(**d))
        #if (json_obj[0]["sh:conforms"] == true):
        #   print(pool_uri[c] + "is conform")
           print("* " + pool_uri[c] + " is conform to CPSV-AP and it is harvested")
           if type_uri[c] == 'jsonEstonia':
               try:
                   # Fetch the JSON data
                   response = requests.get(pool_uri[c], headers=headers).json()

                   # Process the response
                   configJSON = ConfigParser()
                   configJSON.read('mapping_estonia.ini')
                   json_to_rdf(pool_uri[c], response, g, configJSON)

               except ValueError as e:
                   print(e)

           if type_uri[c] == 'xml' or type_uri[c] == 'turtle' or type_uri[c] == 'nt':
               rdf(pool_uri[c], type_uri[c])

           if type_uri[c] == 'json-ld':
               #print(text_json)
               rdf_data(text_json,type_uri[c])
        else:
            print("* " + pool_uri[c] + " is not conform to CPSV-AP and it is not harvested")

        # Counter update
        c += 1

    # Iterate over triples in store and print them out.
    print('\r\nNumber of triples added: %d' % len(g))

    # Cleanup the graph instance
    g.close()

    #for s, p, o in g:
    #    print(s, p, o)

    # print(colored('Total execution time: %s seconds', 'yellow') % (time.time() - start_time))

if __name__ == '__main__':
    main()
