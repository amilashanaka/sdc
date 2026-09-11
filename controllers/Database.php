<?php

class Database
{
    private ?PDO $connection = null;

    private string $host;
    private string $db_name;
    private string $db_user;
    private string $db_pass;
    private int $port;

    public function __construct(string $host, string $db_name, string $db_user, string $db_pass, int $port = 3306)
    {
        $this->host = $host;
        $this->db_name = $db_name;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->port = $port;
    }

    public function get_connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $host = ($this->host === 'localhost') ? '127.0.0.1' : $this->host;
        $portSuffix = ($this->port === 3306) ? '' : ";port={$this->port}";
        $dsn = "mysql:host={$host}{$portSuffix};dbname={$this->db_name};charset=utf8mb4;";

        $this->connection = new PDO($dsn, $this->db_user, $this->db_pass, [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        return $this->connection;
    }
}
