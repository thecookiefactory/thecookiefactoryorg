# Installation

1. Install all dependencies listed below.
2. Rename the file `config.py.template` to `config.py`
3. Set your connection details in `config.py`

# Dependencies

* [Python 3](http://www.python.org/download/) (Python 2 is supported as well)
  - [MySQL Connector/Python](http://dev.mysql.com/downloads/connector/python/)
  - [Requests](http://docs.python-requests.org/en/latest/)
  - [Pillow](https://pypi.python.org/pypi/Pillow/)

# Usage

## github.py

**Should be ran hourly.**

Updates `maps.link` values based on either the Steam Workshop ID or Github repo
name stored in `maps.dl`.

## twitch.py

**Should be ran every five minutes.**

Updates `streams.title` values with the stream titles retrieved from Twitch API
for every online stream with a `streams.twitchname` specified in the database.
If the stream is offline, `streams.title` is set to NULL.

## rss.py

**Should be ran on demand upon changes to the `news` table.**

Updates `/rss.xml` to contain the latest 20 articles based on data
in the `news` table.

## webp.py

**Should be ran on demand upon image uploads.**

Takes a path to an image file as an argument, converts that to WebP format, and
saves the resulting image next to the input file with the same filename.

## sql.py

**Should not be ran directly.**

Defines the SQLConnection class used by github.py, twitch.py and rss.py for
manipulating the database.

## config.py

**Should not be ran directly.**

Contains configuration data for the SQLConnection class.
