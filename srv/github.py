import requests
from sql import *

API_ADDRESS = 'https://api.github.com/repos'


def getDlNames(sql):
    sql.crs.execute('SELECT `dl` FROM `maps`')
    return [dl[0] for dl in sql.crs]


def getFromAPI(repo, method, param=''):
    link = API_ADDRESS + '/' + repo + '/' + method
    if param:
        link += '/' + param
    return requests.get(link, headers={'X-GitHub-Media-Type': 'application/vnd.github.manifold-preview'}).json()


def insertAssetLink(sql, dl, link):
    if link:
        sql.crs.execute("UPDATE `maps` SET `link`='{t}' WHERE `dl`='{s}'".format(t=link, s=dl))
    else:
        sql.crs.execute("UPDATE `maps` SET `link`=NULL WHERE `dl`='{s}'".format(s=dl))


def main():
    sql = SQLConnection()
    dlnames = getDlNames(sql)
    dldata = {}

    for name in dlnames:
        dldata[name] = False
        if name.isdigit():
            dldata[name] = 'http://steamcommunity.com/sharedfiles/filedetails/?id={workshopid}'.format(workshopid=name)
        else:
            repojson = getFromAPI(name, 'releases')
            if repojson:
                assetjson = requests.get(repojson[0]['assets_url']).json()
                dldata[name] = 'https://github.com/{repo}/releases/download/{releasename}/{assetname}'.format(repo=name, releasename=repojson[0]['name'], assetname=assetjson[0]['name'])
    for repo in dldata:
        insertAssetLink(sql, repo, dldata[repo])

    sql.close()


if __name__ == '__main__':
    main()
