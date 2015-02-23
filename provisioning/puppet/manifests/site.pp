class { '::mysql::server':
  package_name => 'mysql-server-5.6',
}

mysql::db { 'tcf':
  user     => 'tcf',
  password => 'tcf',
  host     => 'localhost',
  grant    => ['ALL'],
  sql      => '/vagrant/schema.sql',
}

class { '::mysql::bindings':
  python_enable     => true,
}

file { 'config.php':
  path    => '/vagrant/inc/config.php',
  content => '<?php

if (!isset($r_c)) header("Location: /notfound.php");

$config["db"] = array("host" => "localhost", "username" => "tcf", "password" => "tcf", "dbname" => "tcf", "charset" => "utf8");
$config["apikey"] = null;
$config["domain"] = null;
$config["python"] = array("rss" => "python /vagrant/srv/rss.py", "updater" => "python /vagrant/srv/updater.py");
$config["s3"] = array("key" => null, "secret" => null, "bucket" => null);',
}

file { 'config.py':
  path    => '/vagrant/srv/config.py',
  content => 'db_connection_string = "mysql://tcf:tcf@localhost/tcf"
document_root = "/vagrant/',
}

class { 'nginx': }

nginx::resource::vhost { 'localhost':
  www_root          => '/vagrant',
}

nginx::resource::location { "/vagrant":
  ensure          => present,
  vhost           => "localhost",
  www_root        => "/vagrant",
  location        => '~ \.php$',
  index_files     => ['index.php', 'index.html', 'index.htm'],
  fastcgi         => "unix:/var/run/php5-fpm.sock",
}

class { 'composer':
  command_name => 'composer',
  target_dir   => '/usr/local/bin'
}

exec { '/usr/local/bin/composer install':
  cwd         => '/vagrant',
  environment => 'HOME=/home/vagrant',
  require     => [Class[composer], Package[php5], Package[php5-curl], Package[git]],
}

package { ['php5', 'php5-curl', 'git', 'php5-fpm']:
  ensure => present,
}

class { 'python' :
  version => 'system',
  pip     => true,
  dev     => true,
}

python::requirements { '/vagrant/requirements.txt': }
