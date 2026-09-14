<?php

class PaymentController extends TableController
{

 


    public function __construct(Database $database)
    {
        $this->conn = $database->get_connection();
        $this->table = "payments";

        parent::__construct($database, $this->table);
    }
}
