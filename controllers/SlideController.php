<?php

class SlideController extends TableController
{




    public function __construct(Database $database)
    {


        $this->set_connection($database->get_connection());
        $this->set_table("slides");
        $this->set_primary_key("id");
        // $this->set_foreign_keys(['user','course']);
        parent::__construct($database, $this->table);
    }
}
