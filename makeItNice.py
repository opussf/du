#!/usr/bin/env python

import os
import json
import time

dataDir = "."
dataFile = "du.json"

# load the data structure, or start a new one
try:
	duData = json.load( open( os.path.join( dataDir, dataFile ), "r" ), parse_int=int )
except:
	duData = {}

# there has to be a better way...  but...  force all the keys to int
duData = dict( [ (int(k), v) for k, v in duData.items() ] )

for k in duData.keys():
	if duData[k]['free'] == 0:
		del duData[k]

minFree = None
minFreeKey = None
for k, v in duData.items():
	if not minFree:
		minFree = v["free"]
		minFreeKey = k
	elif v["free"] < minFree:
		minFree = v["free"]
		minFreeKey = k

print "minFreeKey: %s" % ( minFreeKey, ) 
del duData[minFreeKey]

# write the data out
json.dump( duData, open( os.path.join( dataDir, dataFile ), "w" ), indent=2 )




"""
from optparse import OptionParser

        parser = OptionParser()
        parser.add_option( "-r", "--runtime", action="store", dest="runtime", type="string", default="3600",
                        help="how long to run for. 1h, 60m, 3600s, 3600 are all the same." )
        parser.add_option( "-i", "--interval", action="store", dest="interval", type="int", default=500000,
                        help="how often to report progress." )

        (options, args) = parser.parse_args()

        findem = FindEm( options.__dict__ )
        findem.main()
"""
