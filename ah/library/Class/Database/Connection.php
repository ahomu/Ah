<?php

class Database_Connection
{
    private static $_connections;

    /**
     * get
     *
     * @param array $dsn
     * @param string $connection_id
     * @return object $_connections[$connection_id]
     */
    public static function get($dsn, $connection_id = '__default__')
    {
        if ( !isset(self::$_connections[$connection_id]) )
        {
            self::$_connections[$connection_id] = new PDO($dsn['dsn'], $dsn['user'], $dsn['pass'], $dsn['option']);
            self::$_connections[$connection_id]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$_connections[$connection_id];
    }
}