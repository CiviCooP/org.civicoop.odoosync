<?php

/**
 * Utility class
 */

class CRM_OdooContributionSync_Utils {
  
  private static $_singleton;
  
  private $connector;
  
  private $odooCurrencyIds = array();
  
  private $companies = false;
  
  private $journals = false;
  
  private $accounts = false;
  
  private $products = false;
  
  private $taxes = false;
  
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
  
  public function getAvailableCompanies() {
    if ($this->companies === false) {
      $this->companies = $this->loadOptions('res.company', 'name');
    }
   return $this->companies; 
  }
  
  public function getAvailableJournals() {
    if ($this->journals === false) {
      $this->journals = $this->loadOptions('account.journal', 'name');
    }
   return $this->journals; 
  }
  
  public function getAvailableTaxes() {
    if ($this->taxes === false) {
      $this->taxes = $this->loadOptions('account.tax', 'name');
    }
   return $this->taxes; 
  }
  
  public function getAvailableAccounts() {
    if ($this->accounts === false) {
      $this->accounts = $this->loadOptions('account.account', array('code', 'name'));
    }
   return $this->accounts; 
  }
  
  public function getAvailableProducts() {
    if ($this->products === false) {
      $this->products = $this->loadOptions('product.product', 'name');
    }
   return $this->products; 
  }
  
  private function loadOptions($resource, $name_parameter) {
    $list = array();
    $ids = $this->connector->search($resource, array());
    $objects = $this->connector->read($resource, $ids);
    foreach($objects as $object_res) {
      $object = $object_res->scalarval();
      if (is_array($name_parameter)) {
        $name = '';
        foreach($name_parameter as $name_param) {
          $name .= ' '. $object[$name_param]->scalarval();
        }
        $name = trim($name);
      } else {
        $name = $object[$name_parameter]->scalarval();
      }
      $list[$object['id']->scalarval()] = $name;
    }
    return $list;
  }
}

