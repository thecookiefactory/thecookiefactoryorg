import requests
from sql import *

API_ADDRESS = 'https://api.github.com/repos'


def getDlNames(sql):
    sql.crs.execute('SELECT `dl`, `link`, `id` FROM `maps` WHERE `dl` IS NOT NULL')
    return [{'dl': map[0], 'oldlink': map[1], 'mapid': map[2]} for map in sql.crs]


def getFromAPI(repo, method, param=''):
    link = API_ADDRESS + '/' + repo + '/' + method
    if param:
        link += '/' + param
    return requests.get(link, headers={'Accept': 'application/vnd.github.manifold-preview'}).json()


def insertAssetLink(sql, mapid, link):
    sql.crs.execute("UPDATE `maps` SET `link`='{l}' WHERE `id`='{i}'".format(l=link, i=mapid))


def main():
    sql = SQLConnection()
    dldata = getDlNames(sql)

    for map in dldata:
        if map['dl'].isdigit():
            map['newlink'] = 'http://steamcommunity.com/sharedfiles/filedetails/?id={workshopid}'.format(workshopid=map['dl'])
        else:
            repojson = getFromAPI(name, 'releases')
            if repojson:
                assetjson = requests.get(repojson[0]['assets_url'], headers={'Accept': 'application/vnd.github.manifold-preview'}).json()
                map['newlink'] = 'https://github.com/{repo}/releases/download/{releasename}/{assetname}'.format(repo=map['dl'], releasename=repojson[0]['name'], assetname=assetjson[0]['name'])
    for map in dldata:
        if map['oldlink'] != map['newlink']:
            insertAssetLink(sql, map['mapid'], map['newlink'])

    sql.close()


if __name__ == '__main__':
    main()
