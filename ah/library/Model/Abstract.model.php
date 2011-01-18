<?php

abstract class Model_Abstract
{
    protected
        $Connection,    // Database_Connection
        $Statement,     // PDOStatement
        $Fields;        // Ah_Params

    protected
        $_my_class_name,
        $_database_name,
        $_primary_key,
        $_defined_field,
        $_validate_rule;

    public function __construct($dsn_or_connection, $connection_id = '__default__')
    {
        if ( $dsn_or_connection instanceof Database_Connection )
        {
            $this->Connection = $dsn_or_connection;
        }
        else
        {
            $this->Connection = Database_Connection::get($dsn_or_connection, $connection_id);
        }
        return $this;
    }

        public static function __callStatic($method, $args)
        {
            // dynamic query
        }
        public function __call($method, $args)
        {
            // dynamic query
        }
        public function __set($prop, $value)
        {

        }
        public function __get($prop)
        {

        }

    /**
     * find ( like as SELECT )
     *
     * @param  mixed string|int|array
     * @return void
     */
    public function find($id)
    {
        return new $this->_my_class_name($this->Connection);
    }

        public function find_query($template, $placeholder = array())
        {
            $this->Statement = $this->Connection->prepare($template);
        }
        public function find_first()
        {

        }
        public function find_last()
        {

        }
        public function find_all($sql)
        {

        }

    /**
     * create ( like a INSERT )
     *
     * @param array $params
     * @return void
     */
    public function create($params)
    {

    }

    /**
     * update ( like a UPDATE )
     *
     * @param int $id
     * @param array $params
     * @return void
     */
    public function update($id, $params)
    {

    }

    /**
     * delete ( like a DELETE )
     *
     * @param int $id
     * @return void
     */
    public function delete($id)
    {

    }

    /**
     * query
     *
     * @param array $params
     * @return
     */
    public function query($params = array())
    {
        $this->Statement->execute($params);
        return $this->Statement;
    }

    /**
     * params
     *
     * @param array $params
     * @return void
     */
    public function params($params)
    {
        $this->Fields = new Ah_Params($this->_defined_field, $params);
    }

    /**
     * validate
     *
     * @param array $params
     * @return boolean
     */
    public function validate()
    {
        $this->Fields->validate($this->_validate_rule, Ah_Validator::getInstance());
        return $this->Fields->isValidAll();
    }
}