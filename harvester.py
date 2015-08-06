# -*- coding: utf-8 -*-
from SPARQLWrapper import SPARQLWrapper, POST, JSON
from rdflib import Graph
from semantics import context
from itertools import repeat
import requests, copy, json, re
from collections import OrderedDict
from rdflib.plugin import register, Serializer

# Register the JSON-LD plugin for the RDFLib parser
register('json-ld', Serializer, 'rdflib_jsonld', 'JsonLDSerializer')

# Settings - Mandatory
endpointURI = 'http://localhost:8890/sparql'  # URL to sparql endpoint
graphURI = 'http://localhost:8890/pilot'  # Named graph in the triple store
poolURI = ('http://localhost/harvester/part1.json', 'http://localhost/harvester/part2.json')  # Pool of URI to harvest

"""
part1 + part2 = json from https://www.riigiteenused.ee/api/et/all
The json from https://www.riigiteenused.ee/api/et/all is split up to demonstrate the use of harvesting multiple URI although
it is not mandatory.
There is an overlap of content between part1 and part2 for the sake of testing duplicate items(key-value pairs)

Important: https://www.riigiteenused.ee/api/et/all holds mal formed URIs and is therefore not validated in the process to
generate links
"""

cleanGraphQuery = """CLEAR GRAPH <http://localhost:8890/pilot>""" # Executed against the engine to delete the triples from the graph

# Settings - Optional
cleanGraph = 1  # 1=drop all triples; 0=do nothing
headers = {'content-type': 'application/json'}  # HTTP header content type

# Set an endpoint
sparql = SPARQLWrapper(endpointURI)
sparql.setReturnFormat(JSON)
sparql.setMethod(POST)

# Specify the (named) graph we're working with
sparql.addDefaultGraph(graphURI)

# Cleanup the existing triples
if cleanGraph == 1:
	sparql.setQuery(cleanGraphQuery)
	sparql.query().convert()

def json_to_jsonld(data):
	"""
	Public Server ID and Business Event ID have no matches between the JSON data and CPSV-AP vocabulary.
	Therefore we generate the keys and add them to the JSON data.
	"""

	# Parse line by line - split the large array of JSON objects per line
	for line in response:
			# Get v for k objectId and identifikaator and build strings for subjects PS and BE
			# both objectId and identifikaator are used to build the ID strings for PS and BE.
			ps = 'http://PSID-' + line['objectId'] + '-' + line['identifikaator'] # The Public Service ID = PSID
			be = 'http://BEID-' + line['objectId'] + '-' + line['identifikaator'] # The Business Event ID = BEID

			"""
			A dict is not indexed and is therefore not ordered which makes it hard to move or replace keys.
			Items (key-value pairs) will be put at the proper location within the string by removing them first and
			appending them again as the last position is something we can work with
			"""

			# Copy v for k eluarisyndmus to be reused later in the process
			# eluarisyndmus = name of the business event
			eluarisyndmus = line['eluarisyndmus']

			# Copy k,v into new dict so we can change the items (items cannot be changed within the dictionary unless
			#they get copied into a new dict)
			n = copy.deepcopy(line)

			# Remove the eluarisyndmus k
			n.pop('eluarisyndmus')

			# Add the generated PS ID key
			# By removing the eluarisyndmus key, we can put the PS ID at the position where the eluarisyndmus was before
			n['@id'] = ps

			# Move the PS ID k to the beginning - last=false means were not moving the key to the end which means it
			# gets moved to the beginning of the string
			o = OrderedDict(n)
			o.move_to_end('@id', last=False)  # Python +3.2 - only works with an orderedDict

			# Add the generated BE ID key
			"""
			We can't use @id as it has been added before for the PS ID. However, the term @is is needed as it
			resolves the key to a URI. The @idBE is a temp key and will be dealed with using regex + replace later
			in the process
			"""
			o['@idBE'] = be

			# Add the eluarisyndmus key again which we removed earlier
			o['eluarisyndmus'] = eluarisyndmus

			# Create a new in memory graph for the triples
			g = Graph()

			# Manipulate the JSON string
			"""
			Both the artificial PS and BE IDs have been added to the JSON string because they were not present in the
			original JSON data. Except for the Business Event Name, no other BE properties are present in the JSON string.
			This means we will have to generate a key-value pair to act as a predicate which looks like
			k -> "@beid" and v -> the "be" variable defined earlier. This new key gets mapped in the context to
			"beid":{"@id": "dct:type"} which means a type of an event.
			All this has to be put in a seperate arry inside the JSON string prefixed by the "@graph" term.
			Example:
			...
				"beid": "http://BEID-ec3472b3-645c-428a-862f-4d1dc1beaea7-TJA-043",
  				"@graph": [
    						{
      							"@id": "http://BEID-ec3472b3-645c-428a-862f-4d1dc1beaea7-TJA-043",
      							"eluarisyndmus": ""
    						}
  				]

				The first part "beid" is used to create the predicate
				The "@id" part inside the array is used to resolve the BE ID URI

			instance m manipulates the JSON string so it has the new structure
			"""
			m = re.sub('"@idBE"', '","@graph": [{"@id"', re.sub('"@idBE"', '"@beid":"' + be + '"@idBE"', json.dumps(o))+ "]}")

			# parse the JSON through the RDFLib library zipping with the semantic layer (context)
			# and serialize & decoding to UTF-8it to N-Triples
			# Define and initialize the query to save the triples into the graph on the store
			# Context is retrieved from "from semantics import context"
			q = 'INSERT DATA { %s }' % g.parse(data=m, format='json-ld', context=context).serialize(format='nt').decode('utf-8')
			sparql.setQuery(q)

			# Add to the triple store
			sparql.query().convert()

			# Cleanup the graph instance
			g.close()

# Get the number of URI to harvest
nPools = len(poolURI)

# Set counter
c = 0

# Loop over all URI in the pool
for i in repeat(None, nPools):
	try:
		# Fetch the JSON data
		response = requests.get(poolURI[c], headers=headers).json()

		# Process the response
		json_to_jsonld(response)

	# Catch the bad URLs
	except ValueError as e:
		print(e)

# Counter update
c += 1
