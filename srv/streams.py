import requests
import database
import config

API_ADDRESS = 'https://api.twitch.tv/kraken/'
DB = database.MySQLConnection(config.db_connection_string)


def get_stream_names():
    streamdata = [
        {'name': twitch[0], 'userid': twitch[1]}
        for twitch in DB.execute('SELECT `twitchname`, `id` FROM `users` WHERE `twitchname` IS NOT NULL')
    ]
    return streamdata


def get_from_api(method, param=''):
    return requests.get(API_ADDRESS + '/' + method + '/' + param).json()


def insert_stream_title(userid, title):
    if title:
        DB.execute("UPDATE `streams` SET `title`='{t}' WHERE `authorid`='{s}'".format(t=title, s=userid))
    else:
        DB.execute("UPDATE `streams` SET `title`=NULL WHERE `authorid`='{s}'".format(s=userid))


def main():

    streamdata = get_stream_names()

    for stream in streamdata:
        stream['title'] = False
        streamjson = get_from_api('streams', stream['name'])

        if streamjson['stream']:
            stream['title'] = streamjson['stream']['channel']['status']

    for stream in streamdata:
        insert_stream_title(stream['userid'], stream['title'])

    DB.close()


if __name__ == '__main__':
    main()
