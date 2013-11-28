import requests
from sql import *

API_ADDRESS = 'https://api.twitch.tv/kraken/'


def getStreamNames(sql):
    sql.crs.execute('SELECT `twitchname`, `id` FROM `users` WHERE `twitchname` IS NOT NULL')
    streamdata = [{'name': twitch[0], 'userid': twitch[1]} for twitch in sql.crs]
    return streamdata


def getFromAPI(method, param=''):
    return requests.get(API_ADDRESS + '/' + method + '/' + param).json()


def insertStreamTitle(sql, userid, title):
    if title:
        sql.crs.execute("UPDATE `streams` SET `title`='{t}' WHERE `authorid`='{s}'".format(t=title, s=userid))
    else:
        sql.crs.execute("UPDATE `streams` SET `title`=NULL WHERE `authorid`='{s}'".format(s=userid))


def main():
    sql = SQLConnection()
    streamdata = getStreamNames(sql)

    for stream in streamdata:
        stream['title'] = False
        streamjson = getFromAPI('streams', stream['name'])

        if streamjson['stream']:
            stream['title'] = streamjson['stream']['channel']['status']

    for stream in streamdata:
        insertStreamTitle(sql, stream['userid'], stream['title'])

    sql.close()


if __name__ == '__main__':
    main()
