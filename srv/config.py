'''
Config variables for the updater scripts
'''

import os

db_connection_string = os.getenv('CLEARDB_DATABASE_URL')
document_root = '/app/'
