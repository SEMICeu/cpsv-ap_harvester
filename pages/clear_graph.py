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


def clear():
	# Configurations
	config = ConfigParser()
	config.read('config.ini')

	endpoint_uri = config['Mandatory']['endpointURI']
	graph_uri = config['Mandatory']['graphURI']

	clean_graph_query = "CLEAR GRAPH <"+graph_uri+">"

	# Set up endpoint and access to triple store
	sparql = SPARQLWrapper(endpoint_uri)
	sparql.setReturnFormat(JSON)
	sparql.setMethod(POST)
	store = SPARQLUpdateStore(endpoint_uri, endpoint_uri)

	# Specify the (named) graph we're working with
	sparql.addDefaultGraph(graph_uri)

	# Create an in memory graph
	g = Graph(store, identifier=graph_uri)

	# Cleanup the existing triples
	sparql.setQuery(clean_graph_query)
	sparql.query().convert()

	# Cleanup the graph instance
	g.close()

if __name__ == '__main__':
    clear()