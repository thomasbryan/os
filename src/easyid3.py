#! /usr/bin/python
import io
import os
import re
import sys 
import glob 
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
    else:
      print meta.pprint()
  elif(os.path.isdir(mp3)):
    path = u"%s/*.mp3"%mp3
    files = glob.glob(path)
    metas = {}
    for f in files:
      try:
        meta = EasyID3(f)
        title = meta.get('title')
        if(title is not None):
          metas[os.path.splitext(os.path.basename(f))[0]] = title[0]
      except:
        print "Missing ID3 '"+os.path.basename(f)+"'"
    with io.open(mp3+"/meta","w", encoding="utf-8") as f:
      f.write(json.dumps(metas,ensure_ascii=False))
