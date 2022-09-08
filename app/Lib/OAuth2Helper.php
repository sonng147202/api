<?php
/**
 * oauth2 : https://github.com/bshaffer/oauth2-server-php
 * oauth2 php : https://bshaffer.github.io/oauth2-server-php-docs/
 */
namespace App\Lib;

use App\Models\System;
use OAuth2\AutoLoader;
use OAuth2\Storage\Pdo;
use OAuth2\Server;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\AuthorizationCode;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use OAuth2\Request as OAuth2Request;

class OAuth2Helper
{
    public static function initOauthServer()
    {
        static $server = null;
        if ($server !== null) {
            return $server;
        }
        File::requireOnce(app_path('Lib/OAuth2/Autoloader.php'));
        AutoLoader::register();
        $mysqlDatabaseName = Config::get('database.connections.mysql.database');
        $mysqlUserName = Config::get('database.connections.mysql.username');
        $mysqlPassword = Config::get('database.connections.mysql.password');
        $mysqlHostName = Config::get('database.connections.mysql.host');
        $dsn      = sprintf(
            'mysql:dbname=%s;host=%s',
            env('DB_DATABASE', $mysqlDatabaseName),
            env('DB_HOST', $mysqlHostName)
        );
        $storage = new Pdo(array('dsn' => $dsn, 'username' => $mysqlUserName, 'password' => $mysqlPassword));
        $server = new Server($storage, [
            'access_lifetime' => 3600,
            'token_bearer_header_name' => env('TOKEN_BEARER_HEADER_NAME')
        ]);
        $server->addGrantType(new ClientCredentials($storage));
        $server->addGrantType(new AuthorizationCode($storage));
        return $server;
    }

    public static function getToken()
    {
        $oauth = self::initOauthServer();
        $globalRequest = OAuth2Request::createFromGlobals();
        return $oauth->getAccessTokenData($globalRequest);
    }
}