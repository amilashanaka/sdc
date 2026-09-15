<?php 

require_once 'Base.php';

class Error extends Base {
    public $id;
    public $message;

    public function __construct($id, $message) {
        $this->id = $id;
        $this->message = $message;
    }

    public function __toString() {
        return $this->message;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'message' => $this->message,
        ];
    }
}