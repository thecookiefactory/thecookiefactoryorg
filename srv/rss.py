from sql import *
import PyRSS2Gen
from datetime import datetime


def getArticleRows(sql):
    sql.crs.execute('SELECT * FROM `news` WHERE `live` = 1 LIMIT 0, 20')
    return [row for row in sql.crs]


def parseArticleRow(row):
    articledata = {'title': row[1],
                   'description': row[2],
                   'link': 'http://thecookiefactory.org/?p=news&id={articleid}'.format(articleid=row[0]),
                   'comments': 'http://thecookiefactory.org/?p=news&id={articleid}#comments'.format(articleid=row[0]),
                   'pubDate': datetime.fromtimestamp(row[3]),
                   'guid': PyRSS2Gen.Guid('http://thecookiefactory.org/?p=news&id={articleid}'.format(articleid=row[0]))
                  }

    return PyRSS2Gen.RSSItem(**articledata)


def main():
    sql = SQLConnection()

    rssdata = {'title': 'thecookiefactory news',
               'link': 'http://thecookiefactory.org',
               'description': 'The latest news from thecookiefactory.',
               'language': 'en-us',
               'copyright': 'Copyright (C) {year} thecookiefactory.org'.format(year=datetime.now().year),
               'webMaster': 'underyx@thecookiefactory.org (Bence Nagy)',
               'lastBuildDate': datetime.now(),
               'items': [parseArticleRow(row) for row in getArticleRows(sql)]
               }

    PyRSS2Gen.RSS2(**rssdata).write_xml(open(docroot + "rss.xml", "w"))

    sql.close()

if __name__ == '__main__':
    main()
