<?php

/**
 * Utility class
 */

class CRM_OdooContributionSync_Utils {
  
  private static $_singleton;
  
  private $connector;
  
  private $odooCurrencyIds = array();
  
  private function __construct() {
    $this->connector = CRM_Odoosync_Connector::singleton();
  }
  
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_OdooContributionSync_Utils();
    }
    return self::$_singleton;
  }
  
  /**
   * Returns the Odoo Currency ID for a given code (e.g. EUR)
   * 
   * If the Odoo currency ID is not found the function will return false
   * 
   * @param string $code
   * @return int|false
   */
  public function getOdooCurrencyIdByCode($code) {
    //check if currency id exist in cache
    //if not try to retrieve it from Odoo
    if (!isset($this->odooCurrencyIds[$code])) {
      $keys = array(
        new xmlrpcval(array(
          new xmlrpcval('name', 'string'),
          new xmlrpcval('=', 'string'),
          new xmlrpcval($code, 'string'),
            ), "array"),
      );

      $ids = $this->connector->search('res.currency', $keys);
      foreach ($ids as $id_element) {
        $this->odooCurrencyIds[$code] = $id_element->scalarval();
        break;
      }
      if (!isset($this->odooCurrencyIds[$code])) {
        $this->odooCurrencyIds[$code] = false;
      }
    }
    
    
    return $this->odooCurrencyIds[$code];
  }

}

