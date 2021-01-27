#!/usr/bin/env python

import os
import json
import time
import datetime

dataDir = "."
dataFile = "du.json"
dataAge = 86400 * 180 # set as number of seconds

# get the int timestamp
now = int( time.time() )

# load the data structure, or start a new one
try:
	duData = json.load( open( os.path.join( dataDir, dataFile ), "r" ), parse_int=int )
except:
	duData = {}

# there has to be a better way...  but...  force all the keys to int
duData = dict( [ (int(k), v) for k, v in duData.items() ] )

for testTS in duData.keys():
	if duData[ testTS ]["used"] == 0:
		del duData[ testTS ]

"""
for ts in range( now, now+(86400 * 1), 30 ):
	print( ts )
	du = { 'free':0,
		'total': 15,
		'used': 0,
		'1m': 0,
		'5m': 0,
		'15m': 0
	}
	duData[ ts ] = du
"""

# write the data out
json.dump( duData, open( os.path.join( dataDir, dataFile ), "w" ), indent=2 )

