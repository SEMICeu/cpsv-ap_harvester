# Semantics in json format to be added
context = {
"@context": {
				"foaf" : "http://xmlns.com/foaf/0.1/",
                "cpsvap" : "http://data.europa.eu/cv/",
                "dct" : "http://purl.org/dc/terms/",
                "adms" : "http://www.w3.org/ns/adms#",


				"keel":				{"@id": "dct:language"					   },
                "nimetus": 			{"@id": "dct:title"                        },
                "eluarisyndmus":   	{"@id": "dct:title"                        },
                "identifikaator":  	{"@id": "adms:Identifier"                  },
				"veebiaadress": 	{"@id": "foaf:homepage"  , "@type" : "@id" },
				"kirjeldus":      	{"@id": "dct:description"                  },
                "sihtgrupp":      	{"@id": "dct:description"                  },
                "tegevusvaldkond":  {"@id": "cpsvap:sector"                    },
                "ministeerium":    	{"@id": "cpsvap:Agent#Name"                },
                "allasutus":      	{"@id": "cpsvap:Agent#Name"                },
                "osakondyksus":    	{"@id": "cpsvap:Agent#Name"                },
                "omanikunimi":      {"@id": "cpsvap:Agent#Name"                },
                "makse":         	{"@id": "cpsvap:hasCost"                   },
                "eeltingimus":      {"@id": "cpsvap:HasInput"                  },
                "jareltingimus":   	{"@id": "cpsvap:HasOutput"                 },
                "seotuddokumendid": {"@id": "cpsvap:HasInput"                  },
                "omanikutelefon":   {"@id": "cpsvap:Channel"                   },
                "omanikuemail":     {"@id": "cpsvap:Channel"                   },
                "halduskulu":     	{"@id": "cpsvap:Cost#Identifier"           },
                "beid":				{"@id": "dct:type"}
	}

}
"""
translation ={
#---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
#| JSON key               | Translation                     | Mapping according to Mihkel            | Mapping according to XSLT		 | Mapping according to me               						 |
#---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
{"keel":              	    "language                        -> PublicService.Language                 -> dct:language						ok															"},
{"nimetus":           	    "name of PS                      -> PublicService.Name                     -> dct:title							ok															"},
{"eluarisyndmus":     	    "name of business event          -> BusinessEvent.Name                     -> dct:title							ok															"},
{"identifikaator":    	    "ID of the PS                    -> PublicService.Identifier               -> adms:Identifier					ok															"},
{"veebiaadress":      	    "web address of the PS           -> PublicService.Description              -> dct:description					foaf:homepage												"},
{"kirjeldus":         	    "description of the PS           -> PublicService.Description              -> dct:description					ok															"},
{"sihtgrupp":        	    "target audience of the PS       -> PublicService.Description              -> dct:description					ok															"},
{"tegevusvaldkond":   	    "field of activity of PS         -> PublicService.Type                     -> dct:type							ok															"},
{"ministeerium":      	    "ministry                        -> Agent.Name                             -> cva:AgentName						Not sure. What about foaf:name or cpsvap:Agent#Name?		"},
{"allasutus":         	    "authority                       -> Agent.Name                             -> cva:AgentName						Not sure. What about foaf:name or cpsvap:Agent#Name?		"},
{"osakondyksus":      	    "department                      -> Agent.Name                             -> cva:AgentName						Not sure. What about foaf:name or cpsvap:Agent#Name?		"},
{"omanikunimi":       	    "refers to a person              -> Person.Name (sub class of Agent)       -> cva:AgentName						Not sure. What about foaf:name or cpsvap:Agent#Name?		"},
{"makse":             	    "payment                         -> PublicService.HasCost                  -> cva:PublicServiceHasCost			cpsvap:hasCost												"},
{"eeltingimus":       	    "prediction                      -> Input.Name                             -> dct:title							cpsvap:Input												"},
{"jareltingimus":     	    "output of PS                    -> Output.Name                            -> dct:title							cpsvap:Output												"},
{"seotuddokumendid":  	    "related documents               -> Input.RelatedDocumentation             -> foaf:page							Not sure. What about cpsvap:Input   						"},
{"omanikutelefon":    	    "telephone                       -> Agent.Identifier                       -> foaf:phone						Not sure													"},
{"omanikuemail":      	    "e-mail                          -> Agent.Identifier                       -> foaf:mbox							Not sure													"},
{"halduskulu":	      	    "administrative expenses         -> Cost.Identifier                        -> cpsvap:hasCost					cpsvap:Cost#Identifier										"}
}
"""
