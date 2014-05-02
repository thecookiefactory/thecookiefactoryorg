import os
try:
    from urlparse import urlsplit
except ImportError:
    from urllib.parse import urlsplit

import mysql.connector

try:
    from config import *
except ImportError:
    constring = urlsplit(os.getenv('CLEARDB_DATABASE_URL'))
    dbconfig = {
        'user': constring.username,
        'password': constring.password,
        'host': constring.hostname,
        'database': c.path[1:],
    }
    docroot = '/app'

print('Initializing SQL connection.')

class SQLConnection:
    def __init__(self):
        self.open()

    def open(self):
        self.con = mysql.connector.connect(**dbconfig)
        self.crs = self.con.cursor()

    def close(self, commit=True):
        if commit:
            self.con.commit()
        self.con.close()
        self.crs.close()
