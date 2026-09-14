<?php

class CourseController extends TableController
{

 


    public function __construct(Database $database)
    {
        $this->conn = $database->get_connection();
        $this->table = "courses";

        parent::__construct($database, $this->table);
    }
}
