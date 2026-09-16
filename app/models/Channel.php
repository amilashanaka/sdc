<?php

require_once 'Base.php';

class Channel extends Base {
    protected $table = 'channels';

    public function get_all(): array
    {
        return $this->all();
    }
}