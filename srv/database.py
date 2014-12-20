import mysql.connector
try:
    from urlparse import urlsplit
except ImportError:
    from urllib.parse import urlsplit


class MySQLConnection(object):
    def __init__(self, connection_string):
        url = urlsplit(connection_string)
        self.database_config = {
            'user': url.username,
            'password': url.password,
            'host': url.hostname,
            'database': url.path[1:],
        }
        self.connection = mysql.connector.connect(**self.database_config)
        self.cursor = self.connection.cursor()

    def __exit__(self):
        self.close()

    def execute(self, query):
        print('Executing query: {0}'.format(query))
        self.cursor.execute(query)
        result = list(self.cursor)
        return result

    def commit(self):
        self.connection.commit()

    def close(self, commit=True):
        if commit:
            self.commit()
        self.cursor.close()
        self.connection.close()
