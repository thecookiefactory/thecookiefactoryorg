import config
import requests
import database


DB = database.MySQLConnection(config.db_connection_string)


class Map(object):
    def __init__(self, row):
        self.id, self.external_reference, self.link = row
        self.update_queue = {}

        if '/' in self.external_reference:
            self.source = 'github'
        elif self.external_reference.isdigit():
            self.source = 'steam'
        elif not self.external_reference:
            self.source = None
        else:
            raise RuntimeError('could not infer map source from dl')

        print('Identified map #{0}\'s source as {1}'.format(self.id, self.source))

    @classmethod
    def get_all_from_db(cls):
        return [cls(row) for row in DB.execute('SELECT `id`, `dl`, `link` FROM `maps`')]

    def update(self):
        print('Updating map #{0}'.format(self.id))
        if self.source:
            update_queue = getattr(self, self.source + '_update')()
            print('Update queue for map #{0} is {1}'.format(self.id, update_queue))
            set_line = ', '.join("`{0}`='{1}'".format(*item) for item in update_queue.items())
            query = "UPDATE `maps` SET " + set_line + " WHERE `id`='{0}'".format(self.id)
            return DB.execute(query)
        else:
            return None

    def steam_update(self):
        return {
            'link': 'https://steamcommunity.com/sharedfiles/filedetails/?id={0}'.format(self.external_reference),
        }

    def github_update(self):
        url = 'https://api.github.com/repos/{0}/releases'.format(self.external_reference)
        response = requests.get(url).json()
        return {
            'link': response[0]['assets'][0]['browser_download_url'],
            'downloadcount': sum(release['assets'][0]['download_count'] for release in response if release['assets']),
            'editdate': response[0]['published_at'][:-1],
        }


def main():
    maps = Map.get_all_from_db()
    print('Loaded {0} maps from database'.format(len(maps)))

    for map in maps:
        map.update()

    DB.close()


if __name__ == '__main__':
    main()
