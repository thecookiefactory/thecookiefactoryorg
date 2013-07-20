import requests
from sql import *

API_ADDRESS = 'https://api.github.com/repos'


def getRepoNames(sql):
    sql.crs.execute('SELECT `repo` FROM `maps`')
    return [repo[0] for repo in sql.crs]


def getFromAPI(repo, method, param=''):
    link = API_ADDRESS + '/' + repo + '/' + method
    if param:
        link += '/' + param
    return requests.get(link).json()


def insertAssetLink(sql, repo, link):
    if link:
        sql.crs.execute("UPDATE `maps` SET `link`='{t}' WHERE `repo`='{s}'".format(t=link, s=repo))
    else:
        sql.crs.execute("UPDATE `maps` SET `link`=NULL WHERE `repo`='{s}'".format(s=repo))


def main():
    sql = SQLConnection()
    reponames = getRepoNames(sql)
    repodata = {}

    for name in reponames:
        repodata[name] = False
        repojson = getFromAPI(name, 'releases')
        if repojson:
            assetjson = requests.get(repojson[0]['assets_url']).json()
            repodata[name] = 'https://github.com/{repo}/releases/download/{releasename}/{assetname}'.format(repo=name, releasename=repojson[0]['name'], assetname=assetjson[0]['name'])

    for repo in repodata:
        insertAssetLink(sql, repo, repodata[repo])

    sql.close()


if __name__ == '__main__':
    main()
