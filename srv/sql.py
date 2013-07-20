import mysql.connector
from config import *


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
