import requests
from sql import *

API_ADDRESS = 'https://api.twitch.tv/kraken/'


def getStreamNames(sql):
    sql.crs.execute('SELECT `twitch` FROM `streams`')
    return [twitch[0] for twitch in sql.crs]


def getFromAPI(method, param=''):
    return requests.get(API_ADDRESS + '/' + method + '/' + param).json()


def insertStreamTitle(sql, stream, title):
    if title:
        sql.crs.execute("UPDATE `streams` SET `title`='{t}' WHERE `twitch`='{s}'".format(t=title, s=stream))
    else:
        sql.crs.execute("UPDATE `streams` SET `title`=NULL WHERE `twitch`='{s}'".format(s=stream))


def main():
    sql = SQLConnection()
    streamnames = getStreamNames(sql)
    streamdata = {}

    for name in streamnames:
        streamdata[name] = False
        streamjson = getFromAPI('streams', name)

        if streamjson['stream']:
            streamdata[name] = streamjson['stream']['channel']['status']

    for stream in streamdata:
        insertStreamTitle(sql, stream, streamdata[stream])

    sql.close()


if __name__ == '__main__':
    main()
