<?php namespace App\Support;

use Gdoo\Calendar\Sabre\Connector;

class DAV
{
    public static function caldav($uri)
    {
        // Backends
        $authBackend = new Connector\Auth();
        $calendarBackend = new Connector\Share\CalDAV();
        $principalBackend = new Connector\Principal();

        // Directory structure
        $tree = array(
            new \Sabre\CalDAV\Principal\Collection($principalBackend),
            new \Sabre\CalDAV\CalendarRootNode($principalBackend, $calendarBackend),
        );

        $base_url = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/'));

        $server = new \Sabre\DAV\Server($tree);

        $server->setBaseUri($base_url.'/'.$uri);

        // Server Plugins
        $server->addPlugin(new \Sabre\DAV\Auth\Plugin($authBackend, 'SabreDAV'));
        $server->addPlugin(new \Sabre\CalDAV\Plugin());
        $server->addPlugin(new \Sabre\DAVACL\Plugin());
        $server->addPlugin(new \Sabre\CalDAV\SharingPlugin());
        // Support for html frontend
        $server->addPlugin(new \Sabre\DAV\Browser\Plugin(false));

        $server->debugExceptions = false;
        $server->exec();
        exit;
    }
}
