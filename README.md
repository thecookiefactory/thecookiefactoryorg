# Installation

1. Install all dependencies listed below.
2. Run the following commands:

    git clone https://github.com/thecookiefactory/thecookiefactoryorg.git
    cd thecookiefactoryorg
    git submodule init
    git submodule update

3. Edit your `php.ini` file as follows:

    extension=php_openssl.dll
    upload_max_filesize = 128M
    post_max_size = 128M

4. Set up your database by running `mysql -u {DB_USER} -p < schema.sql`

5. Rename the files `inc/config.php.template` and `srv/config.py.template` to
   `inc/config.php` and `srv/config.py`, respectively, and fill out all fields
   set to placeholder values.

6. Schedule certain scripts to be ran regularly according to srv/README.md

# Dependencies

* Apache 2
* PHP 5.4-5.5
* MySQL 5.6
* [Python 3](http://www.python.org/download/) (Python 2 is supported as well)
  - [MySQL Connector/Python](http://dev.mysql.com/downloads/connector/python/)
  - [Requests](http://docs.python-requests.org/en/latest/)
  - [Pillow](https://pypi.python.org/pypi/Pillow/)
