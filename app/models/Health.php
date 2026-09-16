<?php

require_once 'Base.php';

class Health extends Base {
    protected $table = 'health';

    public function get_all(): array
    {
        return $this->all();
    }
}