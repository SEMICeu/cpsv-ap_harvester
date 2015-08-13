# -*- coding: utf-8 -*-
__author__ = 'Steve Verschaeve, PwC EU Services'

"""
Harvests a resource pool of JSON objects presented over HTTP and adds a semantic layer mapped to the CPSV-AP vocabulary
and save to a triple store

Python ver: 3.4
"""

import requests
from SPARQLWrapper import SPARQLWrapper, POST, JSON
from rdflib import URIRef, Literal, Namespace, Graph
from rdflib.namespace import FOAF, RDF
from rdflib.plugins.stores.sparqlstore import SPARQLUpdateStore
from simpleconfigparser import simpleconfigparser

# Configurations
config = simpleconfigparser()
config.read('config.ini')
endpointURI = config['Mandatory']['endpointURI']
graphURI = config['Mandatory']['graphURI']
poolURI = (config['Mandatory']['poolURI']).split(',')
cleanGraph = config['Optional']['cleanGraph']
cleanGraphQuery = config['Optional']['cleanGraphQuery']

# Set up endpoint and access to triple store
sparql = SPARQLWrapper(endpointURI)
sparql.setReturnFormat(JSON)
sparql.setMethod(POST)
store = SPARQLUpdateStore(endpointURI, endpointURI)

# Specify the (named) graph we're working with
sparql.addDefaultGraph(graphURI)

# Create an in memory graph
g = Graph(store, identifier=graphURI)

# Cleanup the existing triples
if cleanGraph == 1:
	sparql.setQuery(cleanGraphQuery)
	sparql.query().convert()

# Creating a namespace for Public Service (PS)
# FOAF and RDF are predefined RDFLib namespace, no need to create a new one
cpsvap = Namespace("http://data.europa.eu/cv/")
dct = Namespace("http://purl.org/dc/terms/")
adms = Namespace("http://www.w3.org/ns/adms#")
lang = Namespace('http://publications.europa.eu/resource/authority/language/')

# Separate namespace for channel, cost and agent as RDFLib does not accept a # as part of an predicate
# Alternative is to construct the predicate as p=<namespace> + '#' + attribute
chan = Namespace("http://data.europa.eu/cv/Channel#")
cost = Namespace("http://data.europa.eu/cv/Cost#")
agent = Namespace("http://data.europa.eu/cv/Agent#")
sdmx = Namespace("http://purl.org/linked-data/sdmx/2009/dimension")


# Build the RDF from the JSON source data
# This function is to be called for each URL in the pool to harvest
def json_to_rdf(json):
	# Parse array of JSON objects line by line
	for line in json:

		# Loop through each key in the dict
		for keys in line:

			# Build the triples

			""" Public Service class """
			""" -------------------- """

			# Build the ID URI as source data does not come with a term related to an ID
			psid = URIRef('http://PSID-' + line[(config['Generic']['objectId'])] + '-' + line[
				(config['PublicService']['identifier'])])

			# Name
			if (config['PublicService']['name']) in keys:
				g.add([psid, dct.name,  # Not sure of the predicate. Could also be dct:title
					   Literal(line.get(config['PublicService']['identifier']))])

			# Type - follows COFOG taxonomy: http://unstats.un.org/unsd/cr/registry/regcst.asp?Cl=4
			g.add((psid, RDF.type, cpsvap.PublicService))  # indicates the "term" type
			if (config['PublicService']['type']) in keys:
				# indicates the kind of type
				# example: public service is a type. Waste management is the kind of service for the type
				g.add((psid, cpsvap.type, Literal(line.get(config['PublicService']['type']))))

			# Description
			if (config['PublicService']['description']) in keys:
				g.add((psid, dct.description, Literal(line.get(config['PublicService']['description']))))

			# Identifier
			if (config['PublicService']['identifier']) in keys:
				g.add((psid, adms.Identifier, Literal(line.get(config['PublicService']['identifier']))))

			# Language
			if (config['PublicService']['language']) in keys:

				if line.get(config['PublicService']['language']) in ('ET', 'et'):  # ET = Estonia

					# The object is a literal but I would prefer http://publications.europa.eu/resource/authority/language/ET
					g.add((psid, dct.language, lang.ET))

				else:
					# Switching to the literal from the source data
					g.add((psid, dct.language, Literal(line.get(config['PublicService']['language']))))

			# Homepage
			# Create a triple for the homepage
			if (config['PublicService']['homepage']) in keys:
				if line.get(config['PublicService']['homepage']) == "":
					g.add((psid, FOAF.homepage, URIRef('http://unknown')))
				else:
					g.add((psid, FOAF.homepage, URIRef(line.get(config['PublicService']['homepage']))))

			# Field of activity
			if (config['PublicService']['sector']) in keys:
				g.add([psid, cpsvap.sector, Literal(line.get(config['PublicService']['sector']))])

			# Ministry
			if (config['PublicService']['ministry']) in keys:
				g.add((psid, cpsvap.AgentName, Literal(line.get(config['PublicService']['ministry']))))

			# Authority - Unit that defines the public service
			if (config['PublicService']['authority']) in keys:
				g.add((psid, cpsvap.AgentName, Literal(line.get(config['PublicService']['authority']))))

			# Department - Department responsable for delivering the public service
			if (config['PublicService']['department']) in keys:
				g.add((psid, cpsvap.AgentName, Literal(line.get(config['PublicService']['department']))))

			# Telephone
			if (config['PublicService']['telephone']) in keys:
				g.add((psid, cpsvap.Channel, Literal(line.get(config['PublicService']['telephone']))))

			# E-mail
			if (config['PublicService']['email']) in keys:
				g.add((psid, cpsvap.Channel, Literal(line.get(config['PublicService']['email']))))

			# Administrative expenses
			if (config['PublicService']['expense']) in keys:
				g.add((psid, cpsvap.CostIdentifier, Literal(line.get(config['PublicService']['expense']))))

			# Check for a homepage key to create has channel
			if (config['PublicService']['homepage']) in keys:
				# Create a hasChannel triple
				g.add((psid, cpsvap.hasChannel, chan.Homepage))

			# Prediction
			if (config['PublicService']['prediction']) in keys:
				g.add((psid, cpsvap.HasInput, Literal(line.get(config['PublicService']['prediction']))))

			# Payment
			if (config['PublicService']['cost']) in keys:
				# Build and add the Cost ID URI
				costid = URIRef('http://COSTID-' + line[(config['Generic']['objectId'])] + '-' + line[
					(config['PublicService']['identifier'])])
				g.add((psid, chan.hasCost, costid))

				g.add((costid, RDF.type, cpsvap.type))
				g.add((costid, dct.description, Literal(line.get('makse'))))

			""" Business Event class """
			""" -------------------- """

			# Build the ID URI as source data does not come with a term related to an ID
			beid = URIRef('http://BEID-' + line[(config['Generic']['objectID'])] + '-' + line[
				(config['PublicService']['Identifier'])])
			g.add((psid, dct.isPartOf, beid))

			# Name
			if (config['BusinessEvent']['name']) in keys:
				g.add((beid, dct.title, Literal(line.get(config['BusinessEvent']['name']))))

			# Language
			if (config['PublicService']['language']) in keys:

				if line.get(config['PublicService']['language']) in ('ET', 'et'):  # ET = Estonia

					# The object is a literal but a URI is prefered: http://publications.europa.eu/resource/authority/language/ET
					g.add((beid, dct.language, lang.ET))

				else:
					# Switching to the literal from the source data
					g.add((beid, dct.language, Literal(line.get(config['PublicService']['language']))))

			""" Input class """
			""" ----------- """

			# Related documents to input
			if (config['Input']['relatedDocuments']) in keys:
				inputid = URIRef('http://INPUTID-' + line[(config['Generic']['objectId'])] + '-' + line[
					(config['PublicService']['identifier'])])
				g.add((psid, cpsvap.HasInput, inputid))
				g.add((inputid, RDF.type, cpsvap.input))
				g.add((inputid, FOAF.page, Literal(line.get(config['Input']['relatedDocuments']))))  # not sure about p

			""" Output class """
			""" ------------ """

			# Output
			if (config['Output']['output']) in keys:
				outputid = URIRef('http://OUTPUTID-' + line[(config['Generic']['objectId'])] + '-' + line[
					(config['PublicService']['identifier'])])
				g.add((psid, cpsvap.Produces, outputid))
				g.add((outputid, RDF.type, cpsvap.output))
				g.add((outputid, FOAF.page, Literal(line.get(config['Ouptut']['output']))))  # not sure about p

			""" Channel class """
			""" ------------- """

			# Check for a Telephone key
			if (config['PublicService']['telephone']) in keys:
				# Create a hasChannel triple
				g.add((psid, cpsvap.Channel, chan.Telephone))

			# Check for an E-mail
			if (config['PublicService']['email']) in keys:
				# Create a hasChannel triple
				# "E-mail" is not accepted as an RDFLib object because of the hyphen so we're constructing a string
				mail = URIRef("http://data.europa.eu/cv/Agent#E-mail")
				g.add((psid, cpsvap.Channel, mail))

			""" Person class """
			""" ------------ """

			# Name of the owner
			if (config['Person']['person']) in keys:
				g.add((psid, agent.Name, Literal(line.get(config['Person']['person']))))

			""" Cost class """
			""" ---------- """
			# There is not key to be mapped but since the currency is in EUR,
			# it can be mapped to namespace sdmx:"http://purl.org/linked-data/sdmx/2009/dimension”
			g.add((psid, sdmx.Currency, Literal('EUR')))

		# Cleanup the graph instance
		g.close()


# Set counter
c = 0

# Loop over all URI in the pool
while c < len(poolURI):
	try:
		# Fetch the JSON data
		response = requests.get(poolURI[c]).json()

		# Process the response
		json_to_rdf(response)

	except ValueError as e:
		print(e)

	# Counter update
	c += 1

# Iterate over triples in store and print them out.
print("--- printing raw triples ---")

for s, p, o in g:
	print(s, p, o)
