# -*- coding: utf-8 -*-
"""
Harvests JSON objects over HTTP and maps from CSPV-AP_Estonia to CPSV-AP vocabulary
and save to a triple store

Python ver: 3.5
"""
__author__ = 'PwC EU Services'

import time

from configparser import ConfigParser

from rdflib import URIRef, Literal, Namespace
from rdflib.namespace import FOAF, RDF


# Build the RDF from the JSON source data
# This function is to be called for each URL in the pool to harvest, in case that the source is in json, with the Estonian mapping
def json_to_rdf(urljson, json, g, config):
    # Creating a namespace for Public Service (PS)
    # FOAF and RDF are predefined RDFLib namespace, no need to create a new one
    cpsvap = Namespace("http://data.europa.eu/cv/")
    dct = Namespace("http://purl.org/dc/terms/")
    org = Namespace("http://www.w3.org/ns/org#")
    lang = Namespace('http://publications.europa.eu/resource/authority/language/')
    
    #Mapping
    identifier = config['PublicService']['identifier']
    ps_name = config['PublicService']['name']
    ps_description = config['PublicService']['description']
    ps_language = config['PublicService']['language']
    ps_sector = config['PublicService']['sector']
    ps_type = config['PublicService']['type']

    ru_rule = config['Rule']['ruleid']

    fo_homepage = config['FormalOrganization']['homepage']
    fo_authority = config['FormalOrganization']['authority']

    co_cost = config['Cost']['cost']
    co_expense = config['Cost']['expense']

    be_name = config['BusinessEvent']['name']

    in_related_documents = config['Input']['relatedDocuments']
    in_prediction = config['Input']['prediction']

    ou_output = config['Output']['output']

    ch_telephone = config['Channel']['telephone']
    ch_email = config['Channel']['email']
    
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
            if ps_name in keys:
                g.add([psid, dct.title, Literal(line.get(ps_name))])

            # Description
            if ps_description in keys:
                g.add((psid, dct.description, Literal(line.get(ps_description))))

            # Language
            if ps_language in keys:

                if line.get(ps_language) in ('ET', 'et'):  # ET = Estonia

                    # The object is a literal but I would prefer
                    # http://publications.europa.eu/resource/authority/language/ET
                    g.add((psid, dct.language, lang.ET))

                else:
                    # Switching to the literal from the source data
                    g.add((psid, dct.language, Literal(line.get(ps_language))))

            # Field of activity
            if ps_sector in keys:
                g.add([psid, cpsvap.sector, Literal(line.get(ps_sector))])

            if ps_type in keys:
                # indicates the kind of type
                # example: public service is a type. Waste management is the kind of service for the type
                g.add((psid, dct.type, Literal(line.get(ps_type))))

            """ Rule class """
            """ ----------- """

            # Related documents to input
            if ru_rule in keys:
                listRule = line.get(ru_rule)
                for s in listRule:
                    ruleid = URIRef(s)
                    g.add((psid, cpsvap.follows, ruleid))
                    g.add((ruleid, RDF.type, cpsvap.Rule))
                    g.add((ruleid, dct.title, Literal('Regulation')))
                    g.add((ruleid, dct.description, Literal('Regulation')))

            """ Formal Organization class """
            """ -------------------- """

            # Build the ID URI as source data does not come with a term related to an ID
            if fo_authority in keys:
                g.add((psid, cpsvap.hasCompetentAuthority, foid))
                g.add((foid, RDF.type, org.FormalOrganization))
                g.add((foid, dct.title, Literal(line.get(fo_authority))))
                # Homepage
                # Create a triple for the homepage
                if fo_homepage in keys:
                    if line.get(fo_homepage) == "":
                        g.add((foid, FOAF.homepage, URIRef('http://unknown')))
                    else:
                        g.add((foid, FOAF.homepage, URIRef(line.get(fo_homepage))))

            """ Cost class """
            """ -------------------- """

            # Payment
            if co_cost in keys:
                # Build and add the Cost ID URI
                costid = URIRef(url + '/cost/' + line[identifier])
                # costid = URIRef('http://COSTID-' + line[objectId] + '-' + line[identifier])
                g.add((psid, cpsvap.hasCost, costid))

                g.add((costid, RDF.type, cpsvap.Cost))
                g.add((costid, dct.description, Literal(line.get(co_cost))))
                g.add((costid, cpsvap.monetary_value, Literal(line.get(co_expense))))
                g.add((costid, cpsvap.currency, URIRef('http://publications.europa.eu/resource/authority/currency/EUR')))
                g.add((costid, cpsvap.idDefinedBy, foid))

            """ Business Event class """
            """ -------------------- """

            g.add((psid, dct.isPartOf, beid))
            g.add((beid, RDF.type, cpsvap.BusinessEvent))

            # Name
            if be_name in keys:
                g.add((beid, dct.title, Literal(line.get(be_name))))

            # Language
            if ps_language in keys:

                if line.get(ps_language) in ('ET', 'et'):  # ET = Estonia

                    # The object is a literal but a URI is prefered:
                    # http://publications.europa.eu/resource/authority/language/ET
                    g.add((beid, dct.language, lang.ET))

                else:
                    # Switching to the literal from the source data
                    g.add((beid, dct.language, Literal(line.get(ps_language))))

            """ Input class """
            """ ----------- """

            # Related documents to input
            if in_related_documents in keys:
                g.add((psid, cpsvap.hasInput, inputid))
                g.add((inputid, RDF.type, cpsvap.Input))
                g.add((inputid, dct.title, Literal(line.get(in_prediction))))

            """ Output class """
            """ ------------ """

            # Output
            if ou_output in keys:
                outputid = URIRef(url + '/output/' + line[identifier])
                # outputid = URIRef('http://OUTPUTID-' + line[objectId] + '-' + line[identifier])
                g.add((psid, cpsvap.produces, outputid))
                g.add((outputid, RDF.type, cpsvap.Output))
                g.add((outputid, dct.title, Literal(line.get(ou_output))))

            """ Channel class """
            """ ------------- """

            # Check for a Telephone key
            if ch_telephone in keys:
                # Create a hasChannel triple
                channeltelid = URIRef(url + '/channel/tel/' + line[identifier])
                g.add((psid, cpsvap.hasChannel, channeltelid))
                g.add((channeltelid, RDF.type, cpsvap.Channel))
                g.add((channeltelid, dct.type, Literal("Telephone")))
                g.add((channeltelid, cpsvap.ownedBy, psid))

            # Check for an E-mail
            if ch_email in keys:
                # Create a hasChannel triple
                channelemailid = URIRef(url + '/channel/email/' + line[identifier])
                # "E-mail" is not accepted as an RDFLib object because of the hyphen so we're constructing a string
                g.add((psid, cpsvap.hasChannel, channelemailid))
                g.add((channelemailid, RDF.type, cpsvap.Channel))
                g.add((channelemailid, dct.type, Literal("E-mail")))
                g.add((channelemailid, cpsvap.ownedBy, psid))
