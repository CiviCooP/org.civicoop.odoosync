<?php

final class CRM_Odoosync_Config_OdooParameters {
  
  private static $_instance;
  
  private function __construct() {
    
  }
  
  public static function singleton() {
    if (!self::$_instance) {
      self::$_instance = new CRM_Odoosync_Config_OdooParameters();
    }
    return self::$_instance;
  }
  
}
