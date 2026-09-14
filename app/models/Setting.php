<?php

require_once 'Base.php';

class Setting extends Base
{
    protected $table = 'settings';

    public function getSettings(string $key): ?string
    {
        $value = $this->$key;
        return $value === null ? null : (string) $value;
    }
}