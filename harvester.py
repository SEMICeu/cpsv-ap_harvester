# -*- coding: utf-8 -*-
"""
Harvests a resource pool of JSON objects presented over HTTP and adds a semantic layer mapped to the CPSV-AP vocabulary
and save to a triple store

Python ver: 3.4
"""
__author__ = 'Steve Verschaeve, PwC EU Services'

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
dct = Namespace("http://purl.org/dc/terms/")
org = Namespace("http://www.w3.org/ns/org#")
lang = Namespace('http://publications.europa.eu/resource/authority/language/')

# Separate namespace for channel, cost and agent as RDFLib does not accept a # as part of an predicate
# Alternative is to construct the predicate as p=<namespace> + '#' + attribute
chan = Namespace("http://data.europa.eu/cv/Channel#")
cost = Namespace("http://data.europa.eu/cv/Cost#")
agent = Namespace("http://data.europa.eu/cv/Agent#")
sdmx = Namespace("http://purl.org/linked-data/sdmx/2009/dimension")


# Build the RDF from the JSON source data
# This function is to be called for each URL in the pool to harvest
def json_to_rdf(urljson, json):
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
            url = urljson.rpartition('/')[0]
            #psid = URIRef('http://PSID-' + line[objectId] + '-' + line[identifier])
            psid = URIRef(url + '/ps/' + line[identifier])

            # Type - follows COFOG taxonomy: http://unstats.un.org/unsd/cr/registry/regcst.asp?Cl=4
            g.add((psid, RDF.type, cpsvap.PublicService))  # indicates the "term" type

            # Name
            if PSName in keys:
                # Not sure of the predicate. Could also be dct:title
                g.add([psid, dct.title, Literal(line.get(PSName))])

            # Description
            if PSDescription in keys:
                g.add((psid, dct.description, Literal(line.get(PSDescription))))

            # Language
            if PSLanguage in keys:

                if line.get(PSLanguage) in ('ET', 'et'):  # ET = Estonia

                    # The object is a literal but I would prefer
                    # http://publications.europa.eu/resource/authority/language/ET
                    g.add((psid, dct.language, lang.ET))

                else:
                    # Switching to the literal from the source data
                    g.add((psid, dct.language, Literal(line.get(PSLanguage))))

            # Field of activity
            if PSSector in keys:
                g.add([psid, cpsvap.sector, Literal(line.get(PSSector))])

            if PSType in keys:
                # indicates the kind of type
                # example: public service is a type. Waste management is the kind of service for the type
                g.add((psid, dct.type, Literal(line.get(PSType))))

            # Identifier
            #if identifier in keys:
            #   g.add((psid, adms.Identifier, Literal(line.get(identifier))))

            # Homepage
            # Create a triple for the homepage
            #if PSHomepage in keys:
            #    if line.get(PSHomepage) == "":
            #        g.add((psid, FOAF.homepage, URIRef('http://unknown')))
            #    else:
            #        g.add((psid, FOAF.homepage, URIRef(line.get(PSHomepage))))

            # Ministry
            #if ministry in keys:
            #    g.add((psid, cpsvap.AgentName, Literal(line.get(ministry))))

            # Authority - Unit that defines the public service
            #if authority in keys:
             #   g.add((psid, cpsvap.AgentName, Literal(line.get(authority))))

            # Department - Department responsable for delivering the public service
            #if department in keys:
            #    g.add((psid, cpsvap.AgentName, Literal(line.get(department))))

            # Telephone
            #if PSTelephone in keys:
            #    g.add((psid, cpsvap.Channel, Literal(line.get(PSTelephone))))

            # E-mail
            #if PSEmail in keys:
            #    g.add((psid, cpsvap.Channel, Literal(line.get(PSEmail))))

            # Administrative expenses
            #if expense in keys:
            #    g.add((psid, cpsvap.CostIdentifier, Literal(line.get(expense))))

            # Check for a homepage key to create has channel
            #if PSHomepage in keys:
            #    # Create a hasChannel triple
            #    g.add((psid, cpsvap.hasChannel, chan.Homepage))

            # Prediction
            if prediction in keys:
                g.add((psid, cpsvap.HasInput, Literal(line.get(prediction))))

            """ Formal Organization class """
            """ -------------------- """

            # Build the ID URI as source data does not come with a term related to an ID
            foid = URIRef(url + '/fo/' + line[identifier])
            g.add((psid, cpsvap.hasCompetentAuthority, foid))
            g.add((foid, RDF.type, org.FormalOrganization))

            g.add((foid, dct.title, Literal(line.get(authority))))

            # Homepage
            # Create a triple for the homepage
            if PSHomepage in keys:
                if line.get(PSHomepage) == "":
                    g.add((foid, FOAF.homepage, URIRef('http://unknown')))
                else:
                    g.add((foid, FOAF.homepage, URIRef(line.get(PSHomepage))))

            """ Cost class """
            """ -------------------- """

            # Payment
            if PSCost in keys:
                # Build and add the Cost ID URI
                costid = URIRef(url + '/cost/' + line[identifier])
                #costid = URIRef('http://COSTID-' + line[objectId] + '-' + line[identifier])
                g.add((psid, cpsvap.hasCost, costid))

                g.add((costid, RDF.type, cpsvap.Cost))
                g.add((costid, dct.description, Literal(line.get(PSCost))))
                g.add((costid, cpsvap.monetary_value, Literal(line.get(expense))))
                g.add((costid, cpsvap.currency, URIRef('http://publications.europa.eu/resource/authority/currency/EUR')))
                g.add((costid, cpsvap.idDefinedBy, foid))

            """ Business Event class """
            """ -------------------- """

            # Build the ID URI as source data does not come with a term related to an ID
            beid = URIRef(url + '/be/' + line[identifier])
            #beid = URIRef('http://BEID-' + line[objectId] + '-' + line[identifier])
            g.add((psid, dct.isPartOf, beid))
            g.add((beid, RDF.type, cpsvap.BusinessEvent))

            # Name
            if BEName in keys:
                g.add((beid, dct.title, Literal(line.get(BEName))))

            # Language
            if PSLanguage in keys:

                if line.get(PSLanguage) in ('ET', 'et'):  # ET = Estonia

                    # The object is a literal but a URI is prefered:
                    # http://publications.europa.eu/resource/authority/language/ET
                    g.add((beid, dct.language, lang.ET))

                else:
                    # Switching to the literal from the source data
                    g.add((beid, dct.language, Literal(line.get(PSLanguage))))

            """ Input class """
            """ ----------- """

            # Related documents to input
            if InputRelatedDocuments in keys:
                inputid = URIRef(url + '/input/' + line[identifier])
                #inputid = URIRef('http://INPUTID-' + line[objectId] + '-' + line[identifier])
                g.add((psid, cpsvap.hasInput, inputid))
                g.add((inputid, RDF.type, cpsvap.Input))
                g.add((inputid, dct.title, Literal(line.get(prediction))))

            """ Output class """
            """ ------------ """

            # Output
            if Output in keys:
                outputid = URIRef(url + '/output/' + line[identifier])
                #outputid = URIRef('http://OUTPUTID-' + line[objectId] + '-' + line[identifier])
                g.add((psid, cpsvap.produces, outputid))
                g.add((outputid, RDF.type, cpsvap.Output))
                g.add((outputid, dct.title, Literal(line.get(Output))))

            """ Channel class """
            """ ------------- """

            # Check for a Telephone key
            if PSTelephone in keys:
                # Create a hasChannel triple
                channeltelid = URIRef(url + '/channel/tel/' + line[identifier])
                g.add((psid, cpsvap.hasChannel, channeltelid))
                g.add((channeltelid, RDF.type, cpsvap.Channel))
                g.add((channeltelid, dct.type, Literal("Telephone")))
                g.add((channeltelid, cpsvap.ownedBy, psid))

            # Check for an E-mail
            if PSEmail in keys:
                # Create a hasChannel triple
                channelemailid = URIRef(url + '/channel/email/' + line[identifier])
                # "E-mail" is not accepted as an RDFLib object because of the hyphen so we're constructing a string
                g.add((psid, cpsvap.hasChannel, channelemailid))
                g.add((channelemailid, RDF.type, cpsvap.Channel))
                g.add((channelemailid, dct.type, Literal("E-mail")))
                g.add((channelemailid, cpsvap.ownedBy, psid))

    # Cleanup the graph instance
    g.close()

    # Print statistics
    print(colored(u'{0:s} - JSON string count: {1:d}, Execution time: {2:.2f} seconds', 'green').format(poolURI[c], j, (
        time.time() - start_time)))


# Set counter
c = 0

# Loop over all URI in the pool
while c < len(poolURI):
    try:
        # Fetch the JSON data
        response = requests.get(poolURI[c], headers=headers).json()

        # Process the response
        json_to_rdf(poolURI[c], response)

    # print(g.serialize(format='nt'))
    except ValueError as e:
        print(e)

    # Counter update
    c += 1

# Iterate over triples in store and print them out.
print('\r\nNumber of triples added: %d' % len(g))

for s, p, o in g:
    print(s, p, o)

print(colored('Total execution time: %s seconds', 'yellow') % (time.time() - start_time))
