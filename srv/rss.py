from sql import *
import PyRSS2Gen
from datetime import datetime

rssdata = {'title': 'thecookiefactory news',
           'link': 'http://thecookiefactory.org',
           'description': 'The latest news from thecookiefactory.',
           'language': 'en-us',
           'copyright': 'Copyright (C) {year} thecookiefactory.org'.format(year=datetime.now().year)}

rss = PyRSS2Gen.RSS2(**rssdata)

rss.write_xml(open("rss.xml", "w"))
