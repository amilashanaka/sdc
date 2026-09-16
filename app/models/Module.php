<?php

require_once 'Base.php';

class Module extends Base {
    protected $table = 'modules';

    public function get_all(): array
    {
        return $this->all();
    }
}