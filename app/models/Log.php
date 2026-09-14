<?php

require_once __DIR__ . '/Base.php';

class Log extends Base
{
    protected $table = 'logs';

    public function get_all(): array
    {
        return $this->all();
    }
}
