# -*- coding: utf-8 -*-
__author__ = 'Steve Verschaeve, PwC EU Services'

"""
Harvests a resource pool of JSON objects presented over HTTP and adds a semantic layer mapped to the CPSV-AP vocabulary
and save to a triple store

Python ver: 3.4
"""

import time

import requests
from SPARQLWrapper import SPARQLWrapper, POST, JSON
from rdflib import URIRef, Literal, Namespace, Graph
from rdflib.namespace import FOAF, RDF
from rdflib.plugins.stores.sparqlstore import SPARQLUpdateStore
from simpleconfigparser import simpleconfigparser
from termcolor import colored

# Track executing time
start_time = time.time()
headers = {'content-type': 'application/json'}  # HTTP header content type
# Configurations
config = simpleconfigparser()
config.read('config.ini')
endpointURI = config['Mandatory']['endpointURI']
graphURI = config['Mandatory']['graphURI']
poolURI = (config['Mandatory']['poolURI']).split(',')
cleanGraph = config['Optional']['cleanGraph']
cleanGraphQuery = config['Optional']['cleanGraphQuery']
objectId = config['Generic']['objectId']
identifier = config['PublicService']['identifier']
PSName = config['PublicService']['name']
PSType = config['PublicService']['type']
PSDescription = config['PublicService']['description']
PSLanguage = config['PublicService']['language']
PSHomepage = config['PublicService']['homepage']
PSSector = config['PublicService']['sector']
ministry = config['PublicService']['ministry']
authority = config['PublicService']['authority']
department = config['PublicService']['department']
PSTelephone = config['PublicService']['telephone']
PSEmail = config['PublicService']['email']
expense = config['PublicService']['expense']
prediction = config['PublicService']['prediction']
PSCost = config['PublicService']['cost']
BEName = config['BusinessEvent']['name']
InputRelatedDocuments = config['Input']['relatedDocuments']
Output = config['Output']['output']
Person = config['Person']['person']

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
dc = Namespace("http://purl.org/dc/terms/")
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
    # Parse array of JSON objects line by line and count them (j)
    j = 0
    for line in json:
        j += 1
        # Loop through each key in the dict
        for keys in line:

            # Build the triples

            """ Public Service class """
            """ -------------------- """

            # Build the ID URI as source data does not come with a term related to an ID
            psid = URIRef('http://PSID-' + line[objectId] + '-' + line[identifier])

            # Name
            if PSName in keys:
                # Not sure of the predicate. Could also be dct:title
                g.add([psid, dc.name, Literal(line.get(identifier))])

            # Type - follows COFOG taxonomy: http://unstats.un.org/unsd/cr/registry/regcst.asp?Cl=4
            g.add((psid, RDF.type, cpsvap.PublicService))  # indicates the "term" type
            if PSType in keys:
                # indicates the kind of type
                # example: public service is a type. Waste management is the kind of service for the type
                g.add((psid, cpsvap.type, Literal(line.get(PSType))))

            # Description
            if PSDescription in keys:
                g.add((psid, dc.description, Literal(line.get(PSDescription))))

            # Identifier
            if identifier in keys:
                g.add((psid, adms.Identifier, Literal(line.get(identifier))))

            # Language
            if PSLanguage in keys:

                if line.get(PSLanguage) in ('ET', 'et'):  # ET = Estonia

                    # The object is a literal but I would prefer
                    # http://publications.europa.eu/resource/authority/language/ET
                    g.add((psid, dc.language, lang.ET))

                else:
                    # Switching to the literal from the source data
                    g.add((psid, dc.language, Literal(line.get(PSLanguage))))

            # Homepage
            # Create a triple for the homepage
            if PSHomepage in keys:
                if line.get(PSHomepage) == "":
                    g.add((psid, FOAF.homepage, URIRef('http://unknown')))
                else:
                    g.add((psid, FOAF.homepage, URIRef(line.get(PSHomepage))))

            # Field of activity
            if PSSector in keys:
                g.add([psid, cpsvap.sector, Literal(line.get(PSSector))])

            # Ministry
            if ministry in keys:
                g.add((psid, cpsvap.AgentName, Literal(line.get(ministry))))

            # Authority - Unit that defines the public service
            if authority in keys:
                g.add((psid, cpsvap.AgentName, Literal(line.get(authority))))

            # Department - Department responsable for delivering the public service
            if department in keys:
                g.add((psid, cpsvap.AgentName, Literal(line.get(department))))

            # Telephone
            if PSTelephone in keys:
                g.add((psid, cpsvap.Channel, Literal(line.get(PSTelephone))))

            # E-mail
            if PSEmail in keys:
                g.add((psid, cpsvap.Channel, Literal(line.get(PSEmail))))

            # Administrative expenses
            if expense in keys:
                g.add((psid, cpsvap.CostIdentifier, Literal(line.get(expense))))

            # Check for a homepage key to create has channel
            if PSHomepage in keys:
                # Create a hasChannel triple
                g.add((psid, cpsvap.hasChannel, chan.Homepage))

            # Prediction
            if prediction in keys:
                g.add((psid, cpsvap.HasInput, Literal(line.get(prediction))))

            # Payment
            if PSCost in keys:
                # Build and add the Cost ID URI
                costid = URIRef('http://COSTID-' + line[objectId] + '-' + line[
                    identifier])
                g.add((psid, chan.hasCost, costid))

                g.add((costid, RDF.type, cpsvap.type))
                g.add((costid, dc.description, Literal(line.get(PSCost))))

            """ Business Event class """
            """ -------------------- """

            # Build the ID URI as source data does not come with a term related to an ID
            beid = URIRef('http://BEID-' + line[objectId] + '-' + line[
                identifier])
            g.add((psid, dc.isPartOf, beid))

            # Name
            if BEName in keys:
                g.add((beid, dc.title, Literal(line.get(BEName))))

            # Language
            if PSLanguage in keys:

                if line.get(PSLanguage) in ('ET', 'et'):  # ET = Estonia

                    # The object is a literal but a URI is prefered:
                    # http://publications.europa.eu/resource/authority/language/ET
                    g.add((beid, dc.language, lang.ET))

                else:
                    # Switching to the literal from the source data
                    g.add((beid, dc.language, Literal(line.get(PSLanguage))))

            """ Input class """
            """ ----------- """

            # Related documents to input
            if InputRelatedDocuments in keys:
                inputid = URIRef('http://INPUTID-' + line[objectId] + '-' + line[identifier])
                g.add((psid, cpsvap.HasInput, inputid))
                g.add((inputid, RDF.type, cpsvap.input))
                g.add((inputid, FOAF.page, Literal(line.get(InputRelatedDocuments))))  # not sure about p

            """ Output class """
            """ ------------ """

            # Output
            if Output in keys:
                outputid = URIRef('http://OUTPUTID-' + line[objectId] + '-' + line[identifier])
                g.add((psid, cpsvap.Produces, outputid))
                g.add((outputid, RDF.type, cpsvap.output))
                g.add((outputid, FOAF.page, Literal(line.get(Output))))  # not sure about p

            """ Channel class """
            """ ------------- """

            # Check for a Telephone key
            if PSTelephone in keys:
                # Create a hasChannel triple
                g.add((psid, cpsvap.Channel, chan.Telephone))

            # Check for an E-mail
            if PSEmail in keys:
                # Create a hasChannel triple
                # "E-mail" is not accepted as an RDFLib object because of the hyphen so we're constructing a string
                mail = URIRef("http://data.europa.eu/cv/Agent#E-mail")
                g.add((psid, cpsvap.Channel, mail))

            """ Person class """
            """ ------------ """

            # Name of the owner
            if Person in keys:
                g.add((psid, agent.Name, Literal(line.get(Person))))

            """ Cost class """
            """ ---------- """
            # There is not key to be mapped but since the currency is in EUR,
            # it can be mapped to namespace sdmx:"http://purl.org/linked-data/sdmx/2009/dimension”
            g.add((psid, sdmx.Currency, Literal('EUR')))

    # Cleanup the graph instance
    g.close()

    # Print statistics
    print(colored(u'{0:s} - JSON count: {1:d}, Execution time: {2:.2f} seconds', 'green').format(poolURI[c], j, (
        time.time() - start_time)))


# Set counter
c = 0

# Loop over all URI in the pool
while c < len(poolURI):
    try:
        # Fetch the JSON data
        response = requests.get(poolURI[c], headers=headers).json()

        # Process the response
        json_to_rdf(response)

    # print(g.serialize(format='nt'))
    except ValueError as e:
        print(e)

    # Counter update
    c += 1

# Iterate over triples in store and print them out.
print('\r\nNumber of triples added: %d' % len(g))
try:
    for s, p, o in g:
        print(s, p, o)
except:
    pass

print(colored('Total execution time: %s seconds', 'yellow') % (time.time() - start_time))
