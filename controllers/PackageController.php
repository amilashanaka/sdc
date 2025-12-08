<?php

class PackageController extends TableController
{

 


    public function __construct(Database $database)
    {
        $this->conn = $database->get_connection();
        $this->table = "packages";

        parent::__construct($database, $this->table);
    }
}
