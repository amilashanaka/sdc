<?php

class LogController extends TableController
{
    public function __construct(Database $database)
    {
        $this->conn = $database->get_connection();
        $this->table = "logs";

        parent::__construct($database, $this->table);
    }
 
}
