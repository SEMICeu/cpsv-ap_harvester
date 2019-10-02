# -*- coding: utf-8 -*-

"""
Harvests JSON objects over HTTP and maps to CPSV-AP vocabulary
and save to a triple store

Python ver: 3.5
"""

__author__ = 'PwC EU Services'

from configparser import ConfigParser

# Configurations
config = ConfigParser()
config.read('config.ini')

endpoint_uri = config['Mandatory']['endpointURI']

print(endpoint_uri)

