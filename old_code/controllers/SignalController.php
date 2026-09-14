<?php

class SignalController extends TableController
{

 


    public function __construct(Database $database)
    {
        $this->conn = $database->get_connection();
        $this->table = "signals";

        parent::__construct($database, $this->table);
    }
}
