# -*- coding: utf-8 -*-
"""
Harvests JSON objects over HTTP and maps to CPSV-AP vocabulary
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


def main():
    # Track executing time
    start_time = time.time()
    headers = {'content-type': 'application/json'}  # HTTP header content type
    # Configurations
    config = simpleconfigparser()
    config.read('config.ini')
    endpoint_uri = config['Mandatory']['endpointURI']
    graph_uri = config['Mandatory']['graphURI']
    pool_uri = (config['Mandatory']['poolURI']).split(',')
    cleanGraph = config['Optional']['cleanGraph']
    cleanGraphQuery = config['Optional']['cleanGraphQuery']

    identifier = config['PublicService']['identifier']
    PSName = config['PublicService']['name']
    PSDescription = config['PublicService']['description']
    PSLanguage = config['PublicService']['language']
    PSSector = config['PublicService']['sector']
    PSType = config['PublicService']['type']

    RURule = config['Rule']['ruleid']

    FOHomepage = config['FormalOrganization']['homepage']
    FOauthority = config['FormalOrganization']['authority']

    COCost = config['Cost']['cost']
    COexpense = config['Cost']['expense']

    BEName = config['BusinessEvent']['name']

    INRelatedDocuments = config['Input']['relatedDocuments']
    INprediction = config['Input']['prediction']

    OUOutput = config['Output']['output']

    CHTelephone = config['Channel']['telephone']
    CHEmail = config['Channel']['email']

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
    if cleanGraph == 1:
        sparql.setQuery(cleanGraphQuery)
        sparql.query().convert()

    # Creating a namespace for Public Service (PS)
    # FOAF and RDF are predefined RDFLib namespace, no need to create a new one
    cpsvap = Namespace("http://data.europa.eu/cv/")
    dct = Namespace("http://purl.org/dc/terms/")
    org = Namespace("http://www.w3.org/ns/org#")
    lang = Namespace('http://publications.europa.eu/resource/authority/language/')

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
                # psid = URIRef('http://PSID-' + line[objectId] + '-' + line[identifier])
                psid = URIRef(url + '/ps/' + line[identifier])
                foid = URIRef(url + '/fo/' + line[identifier])
                inputid = URIRef(url + '/input/' + line[identifier])
                beid = URIRef(url + '/be/' + line[identifier])

                # Type - follows COFOG taxonomy: http://unstats.un.org/unsd/cr/registry/regcst.asp?Cl=4
                g.add((psid, RDF.type, cpsvap.PublicService))  # indicates the "term" type

                # Name
                if PSName in keys:
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

                """ Rule class """
                """ ----------- """

                # Related documents to input
                if RURule in keys:
                    listRule = line.get(RURule)
                    for s in listRule:
                        ruleid = URIRef(s)
                        g.add((psid, cpsvap.follows, ruleid))
                        g.add((ruleid, RDF.type, cpsvap.Rule))
                        g.add((ruleid, dct.title, Literal('Regulation')))
                        g.add((ruleid, dct.description, Literal('Regulation')))

                """ Formal Organization class """
                """ -------------------- """

                # Build the ID URI as source data does not come with a term related to an ID
                if FOauthority in keys:
                    g.add((psid, cpsvap.hasCompetentAuthority, foid))
                    g.add((foid, RDF.type, org.FormalOrganization))
                    g.add((foid, dct.title, Literal(line.get(FOauthority))))
                    # Homepage
                    # Create a triple for the homepage
                    if FOHomepage in keys:
                        if line.get(FOHomepage) == "":
                            g.add((foid, FOAF.homepage, URIRef('http://unknown')))
                        else:
                            g.add((foid, FOAF.homepage, URIRef(line.get(FOHomepage))))

                """ Cost class """
                """ -------------------- """

                # Payment
                if COCost in keys:
                    # Build and add the Cost ID URI
                    costid = URIRef(url + '/cost/' + line[identifier])
                    # costid = URIRef('http://COSTID-' + line[objectId] + '-' + line[identifier])
                    g.add((psid, cpsvap.hasCost, costid))

                    g.add((costid, RDF.type, cpsvap.Cost))
                    g.add((costid, dct.description, Literal(line.get(COCost))))
                    g.add((costid, cpsvap.monetary_value, Literal(line.get(COexpense))))
                    g.add((costid, cpsvap.currency, URIRef('http://publications.europa.eu/resource/authority/currency/EUR')))
                    g.add((costid, cpsvap.idDefinedBy, foid))

                """ Business Event class """
                """ -------------------- """

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
                if INRelatedDocuments in keys:
                    g.add((psid, cpsvap.hasInput, inputid))
                    g.add((inputid, RDF.type, cpsvap.Input))
                    g.add((inputid, dct.title, Literal(line.get(INprediction))))

                """ Output class """
                """ ------------ """

                # Output
                if OUOutput in keys:
                    outputid = URIRef(url + '/output/' + line[identifier])
                    # outputid = URIRef('http://OUTPUTID-' + line[objectId] + '-' + line[identifier])
                    g.add((psid, cpsvap.produces, outputid))
                    g.add((outputid, RDF.type, cpsvap.Output))
                    g.add((outputid, dct.title, Literal(line.get(OUOutput))))

                """ Channel class """
                """ ------------- """

                # Check for a Telephone key
                if CHTelephone in keys:
                    # Create a hasChannel triple
                    channeltelid = URIRef(url + '/channel/tel/' + line[identifier])
                    g.add((psid, cpsvap.hasChannel, channeltelid))
                    g.add((channeltelid, RDF.type, cpsvap.Channel))
                    g.add((channeltelid, dct.type, Literal("Telephone")))
                    g.add((channeltelid, cpsvap.ownedBy, psid))

                # Check for an E-mail
                if CHEmail in keys:
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
        print(colored(u'{0:s} - JSON string count: {1:d}, Execution time: {2:.2f} seconds', 'green').format(pool_uri[c], j, (
            time.time() - start_time)))

    # Set counter
    c = 0

    # Loop over all URI in the pool
    while c < len(pool_uri):
        try:
            # Fetch the JSON data
            response = requests.get(pool_uri[c], headers=headers).json()

            # Process the response
            json_to_rdf(pool_uri[c], response)

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

if __name__ == '__main__':
    main()
