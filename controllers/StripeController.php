<?php

class CourseController extends TableController
{

 


    public function __construct(Database $database)
    {
        $this->conn = $database->get_connection();
        $this->table = "stripes";

        parent::__construct($database, $this->table);
    }
}
