import requests
import mysql.connector
from config import *

API_ADDRESS = 'https://api.twitch.tv/kraken/'
CON = mysql.connector.connect(**dbconfig)
CRS = CON.cursor()

def getStreamNames():
  CRS.execute('SELECT `twitch` FROM `streams`')
  return [twitch[0] for twitch in CRS]

def getFromAPI(method, param=''):
  return requests.get(API_ADDRESS + '/' + method + '/' + param).json()

def insertStreamTitle(stream, title):
  if title:
    CRS.execute("UPDATE `streams` SET `title`='{t}' WHERE `twitch`='{s}'".format(t=title, s=stream))
  else:
    CRS.execute("UPDATE `streams` SET `title`=NULL WHERE `twitch`='{s}'".format(s=stream))

def main()
  streamnames = getStreamNames()
  streamdata = {}

  for name in streamnames:
    streamdata[name] = False
    streamjson = getFromAPI('streams', name)

    if streamjson['stream']:
      streamdata[name] = streamjson['stream']['channel']['status']

  for stream in streamdata:
    insertStreamTitle(stream, streamdata[stream])

  CON.commit()

if __name__ == '__main__':
  main()

CRS.close()
CON.close()
