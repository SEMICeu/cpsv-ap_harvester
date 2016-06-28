# -*- coding: utf-8 -*-
"""
Harvests JSON objects over HTTP and maps to CPSV-AP vocabulary
and save to a triple store

Python ver: 3.5
"""

__author__ = 'PwC EU Services'

from json_mapping_estonia import json_to_rdf
import time

from configparser import ConfigParser

import requests
from SPARQLWrapper import SPARQLWrapper, POST, JSON
from rdflib import Graph
from rdflib.plugins.stores.sparqlstore import SPARQLUpdateStore
from termcolor import colored


def main():
    # Track executing time
    # start_time = time.time()
    headers = {'content-type': 'application/json'}  # HTTP header content type
    # Configurations
    config = ConfigParser()
    config.read('config.ini')
    
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

    # Set counter
    c = 0

    # Loop over all URI in the pool
    while c < len(pool_uri):
        print(pool_uri[c],type_uri[c])
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
