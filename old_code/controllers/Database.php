<?php

class Database
{

    // Db connection

    private $host;
    private $db_name;
    private $db_user;
    private $db_pass ;
 

    public  function __construct(string $host,string $db_name, string $db_user,  string $db_pass)
    {

        $this->host = $host;
        $this->db_name = $db_name;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        
    }
 

    // Db connect 
    public function get_connection()
    {
 
      $dsn = "mysql:host={$this->host}; dbname={$this->db_name}; charset=utf8;";
     return new PDO($dsn, $this->db_user, $this->db_pass,[
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,

     ]);
 
    }
}