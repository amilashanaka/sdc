<?php

require_once __DIR__ . '/Base.php';

class Company extends Base
{
    protected $table = 'companies';

    public function get_all(): array
    {
        return $this->all();
    }
}
