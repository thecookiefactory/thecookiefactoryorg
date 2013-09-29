import requests
from sql import *

API_ADDRESS = 'https://api.github.com/repos'


def getDlNames(sql):
    sql.crs.execute('SELECT `dl`, `link` FROM `maps`')
    return [(dl[0],dl[1]) for dl in sql.crs]


def getFromAPI(repo, method, param=''):
    link = API_ADDRESS + '/' + repo + '/' + method
    if param:
        link += '/' + param
    return requests.get(link, headers={'Accept': 'application/vnd.github.manifold-preview'}).json()


def insertAssetLink(sql, dl, link):
    if link:
        sql.crs.execute("UPDATE `maps` SET `link`='{t}' WHERE `dl`='{s}'".format(t=link, s=dl))
    else:
        sql.crs.execute("UPDATE `maps` SET `link`=NULL WHERE `dl`='{s}'".format(s=dl))


def main():
    sql = SQLConnection()
    dlnames = getDlNames(sql)
    dldata = {}

    for name, link in dlnames:
        dldata[name] = False
        if name.isdigit():
            dldata[name] = 'http://steamcommunity.com/sharedfiles/filedetails/?id={workshopid}'.format(workshopid=name)
        else:
            repojson = getFromAPI(name, 'releases')
            if repojson:
                assetjson = requests.get(repojson[0]['assets_url'], headers={'Accept': 'application/vnd.github.manifold-preview'}).json()
                dldata[name] = 'https://github.com/{repo}/releases/download/{releasename}/{assetname}'.format(repo=name, releasename=repojson[0]['name'], assetname=assetjson[0]['name'])
    for repo in dldata:
        if dldata[repo] is not link:
         insertAssetLink(sql, repo, dldata[repo])

    sql.close()


if __name__ == '__main__':
    main()
