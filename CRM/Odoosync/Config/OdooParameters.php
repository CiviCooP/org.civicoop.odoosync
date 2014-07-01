<?php

final class CRM_Odoosync_Config_OdooParameters {
  
  private static $_instance;
  
  private $url;
  
  private $databasename;
  
  private $username;
  
  private $password;
  
  private function __construct() {
    $this->url = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'url');
    $this->databasename = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'databasename');
    $this->username = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'username');
    $this->password = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'password');
  }
  
  public static function singleton() {
    if (!self::$_instance) {
      self::$_instance = new CRM_Odoosync_Config_OdooParameters();
    }
    return self::$_instance;
  }
  
  public function getDatabasename() {
    return $this->databasename;
  }
  
  public function getUsername() {
    return $this->username;
  }
  
  public function getPassword() {
    return $this->password;
  }
  
  public function getUrl() {
    return $this->url;
  }
  
  
  
  
}
