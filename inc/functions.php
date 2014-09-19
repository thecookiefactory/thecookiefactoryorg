<?php

if (!isset($r_c)) header('Location: /notfound.php');

$config_file = str_repeat('../', $r_c) . 'inc/config.php';

if (file_exists($config_file)) {

    require_once $config_file;

} else { // heroku settings

    $url = parse_url(getenv('CLEARDB_DATABASE_URL'));

    $config['db'] = array(
        'host' => $url['host'],
        'username' => $url['user'],
        'password' => $url['pass'],
        'dbname' => substr($url['path'], 1),
        'charset' => 'utf8'
    );

    $config['apikey'] = getenv('STEAM_API_KEY');

    $config['domain'] = getenv('TCF_DOMAIN');

    $config['python'] = array(
        'rss' => 'python /app/srv/rss.py',
        'updater' => 'python /app/srv/updater.py'
    );

    $config['updater_ip_whitelist'] = explode(
        ' ', getenv('UPDATER_IP_WHITELIST')
    );

    $config['s3'] = array('key' => getenv('AWS_ACCESS_KEY_ID'),
                          'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
                          'bucket' => getenv('S3_BUCKET'));

}

try {

    $con = new PDO('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['dbname'] . ';charset=utf8', $config['db']['username'], $config['db']['password']);
    $con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    die('Could not connect to the database.');

}

function strip($x, $uses_markdown = false) {

    global $con;

    $x = trim($x);

    if (!$uses_markdown) {

        $x = htmlspecialchars($x, ENT_QUOTES, 'UTF-8');

    }

    return $x;

}

function tformat($x) {

    return nl2br($x, false);

}

function validField($x) {

    return (strip($x) != '' && strip($x) != null);

}

function cookieh() {

    return str_shuffle(hash('sha256', microtime()));

}

function twigInit() {

    global $r_c;

    require_once str_repeat('../', $r_c) . 'vendor/autoload.php';

    Twig_Autoloader::register();

    $loader = new Twig_Loader_Filesystem(str_repeat('../', $r_c) . 'templates');
    $twig = new Twig_Environment($loader);

    return $twig;

}

function canonical() {

    if (isset($_GET['p'])) {

        if (isset($_GET['id'])) {

            return '<link rel=\'canonical\' href=\'http://thecookiefactory.org/' . $_GET['p'] . '/' . $_GET['id'] . '\'>';

        } else {

            return '<link rel=\'canonical\' href=\'http://thecookiefactory.org/' . $_GET['p'] . '/\'>';

        }

    } else {

        return '<link rel=\'canonical\' href=\'http://thecookiefactory.org/\'>';

    }

}
