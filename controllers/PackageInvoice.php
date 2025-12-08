<?php

class PackageInvoice extends TableController

{
       public function __construct(Database $database)
    {
        $this->conn = $database->get_connection();
        $this->table = "package_invoices";

        parent::__construct($database, $this->table);
    }

}