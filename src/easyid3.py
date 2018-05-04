#! /usr/bin/python
import os
import sys 
import json 
import base64
from mutagen.easyid3 import EasyID3
if(len(sys.argv)>1):
  mp3 = base64.b64decode(sys.argv[1])
  if(os.path.isfile(mp3)):
    meta = EasyID3(mp3)
    if(len(sys.argv)>2):
      key = base64.b64decode(sys.argv[2])
      if key in EasyID3.valid_keys.keys():
        if(len(sys.argv)>3):
          val = base64.b64decode(sys.argv[3])
          meta[key] = val
          meta.save(v2_version=3)
    print meta.pprint()
