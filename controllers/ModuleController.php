<?php

class ModuleController extends TableController
{
    public function __construct(Database $database)
    {
        $this->conn = $database->get_connection();
        $this->table = "modules";

        parent::__construct($database, $this->table);
    }

  
}
