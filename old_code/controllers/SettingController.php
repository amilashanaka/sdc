<?php
class SettingController extends TableController
{
    public function __construct(Database $database)
    {
        $this->conn = $database->get_connection();
        $this->table = "settings";
        parent::__construct($database, $this->table);
    }

    public function getSettings($element)
    {
        // Get data once and check if it exists
        $settingsData = $this->get_by_id(1);
        if (!$settingsData || !isset($settingsData['data'])) {
            return null;
        }
        
        $data = $settingsData['data'];
        
        // Default values for each field
        $defaults = [
            'f1' => 'System Name',
            'f2' => 'App Full Name',
            'f3' => 'Phone Number',
            'f4' => 'Email',
            'f5' => 'Address',
            'f6' => 'Facebook',
            'f7' => 'Twitter',
            'f8' => 'Instagram',
            'f9' => 'Telegram',
            'f10' => 'LinkedIn',
            'img1' => 'favicon',
            'img2' => 'Header Logo',
            'img3' => 'Footer Logo',
            'img4' => 'Backend Logo',
            'img5' => 'Backend Nav Logo',
        ];
        
        // Check if the element exists in data, if not return default or empty string
        if (isset($data[$element])) {
            return $data[$element];
        } else {
            // Return default value if exists, otherwise empty string
            return isset($defaults[$element]) ? $defaults[$element] : '';
        }
    }
}