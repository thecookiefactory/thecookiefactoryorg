CREATE TABLE IF NOT EXISTS `custompages` (
  `id` TINYINT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(128) NOT NULL,
  `text` TEXT NOT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editdate` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `live` BIT NOT NULL DEFAULT '0',
  `stringid` VARCHAR(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `forumcategories` (
  `id` TINYINT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(6) NOT NULL,
  `longname` VARCHAR(32) NOT NULL,
  `hexcode` CHAR(6) NOT NULL,
  `hoverhexcode` CHAR(6) NOT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `forumposts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `text` TEXT NOT NULL,
  `authorid` INT NOT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editdate` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `threadid` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `forumthreads` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(256) NOT NULL,
  `text` TEXT NOT NULL,
  `authorid` INT NOT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editdate` TIMESTAMP NULL DEFAULT NULL,
  `lastdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `forumcategory` TINYINT NOT NULL,
  `mapid` TINYINT DEFAULT NULL,
  `newsid` SMALLINT DEFAULT NULL,
  `closed` BIT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `games` (
  `id` TINYINT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL,
  `steamid` INT DEFAULT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `maps` (
  `id` TINYINT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `text` TEXT NOT NULL,
  `authorid` INT NOT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dl` VARCHAR(128) DEFAULT NULL,
  `extension` VARCHAR(4) NOT NULL,
  `comments` BIT NOT NULL,
  `gameid` TINYINT NOT NULL,
  `link` VARCHAR(256) DEFAULT NULL,
  `downloadcount` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `news` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(256) NOT NULL,
  `text` TEXT NOT NULL,
  `authorid` INT NOT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editorid` INT NOT NULL,
  `editdate` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `comments` BIT NOT NULL,
  `live` BIT NOT NULL,
  `stringid` VARCHAR(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `pictures` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `text` VARCHAR(128) NOT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `filename` VARCHAR(256) NOT NULL,
  `mapid` TINYINT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `streams` (
  `id` TINYINT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(128) DEFAULT NULL,
  `text` TEXT,
  `authorid` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(10) NOT NULL,
  `steamid` CHAR(17) NOT NULL,
  `admin` BIT NOT NULL DEFAULT 0,
  `cookieh` CHAR(64) NOT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `twitchname` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
