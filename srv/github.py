import requests
from datetime import datetime
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


def insertAssetLink(sql, map):
    sql.crs.execute("UPDATE `maps` SET `link`='{l}', `downloadcount`='{d}', `editdate`='{e}' WHERE `id`='{i}'".format(l=map['newlink'], i=map['mapid'], d=map.get('dlcount'), e=map.get('date'))


def main():
    sql = SQLConnection()
    dldata = getDlNames(sql)

    for map in dldata:
        if map['dl'].isdigit():
            map['newlink'] = 'http://steamcommunity.com/sharedfiles/filedetails/?id={workshopid}'.format(workshopid=map['dl'])
        else:
            repojson = getFromAPI(map['dl'], 'releases')
            if repojson:
                assetjson = requests.get(repojson[0]['assets_url'], headers={'Accept': 'application/vnd.github.manifold-preview'}).json()
                map['dlcount'] = sum([release['assets'][0]['download_count'] for release in repojson if len(release['assets'])])
                map['newlink'] = 'https://github.com/{repo}/releases/download/{releasename}/{assetname}'.format(repo=map['dl'], releasename=repojson[0]['name'], assetname=assetjson[0]['name'])
                map['date'] = repojson[0]['published_at'][:-1]
    for map in dldata:
        insertAssetLink(sql, map)

    sql.close()


if __name__ == '__main__':
    main()
