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
  
  private $openBookYears = array();
  
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
  
  /**
   * Returns true if the book year exist in odoo and wether the book year is open
   * 
   * @param int $year
   * @return int|false
   */
  public function isBookYearAvailable($year) {
    //check if currency id exist in cache
    //if not try to retrieve it from Odoo
    if (!isset($this->openBookYears[$year])) {
      $keys = array(
        new xmlrpcval(array(
          new xmlrpcval('name', 'string'),
          new xmlrpcval('=', 'string'),
          new xmlrpcval($year, 'string'),
            ), "array"),
          new xmlrpcval(array(
          new xmlrpcval('state', 'string'),
          new xmlrpcval('=', 'string'),
          new xmlrpcval('draft', 'string'),
            ), "array"),
      );

      $ids = $this->connector->search('account.fiscalyear', $keys);
      foreach ($ids as $id_element) {
        $this->openBookYears[$year] = true;
        break;
      }
      if (!isset($this->openBookYears[$year])) {
        $this->openBookYears[$year] = false;
      }
    }
    
    return $this->openBookYears[$year];
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
  
  public function getCreditJournalId() {
    $keys = array(
        new xmlrpcval(array(
          new xmlrpcval('type', 'string'),
          new xmlrpcval('=', 'string'),
          new xmlrpcval('sale_refund', 'string'),
            ), "array"),
      );
    $ids = $this->connector->search('account.journal', $keys);
    $id = reset($ids);
    if ($id) {
      return $id->scalarval();
    }
    
    return false;
  }
  
  public function getMoveLineToAccount($account_id, $move_id) {
    $keys = array(
        new xmlrpcval(array(
          new xmlrpcval('move_id', 'string'),
          new xmlrpcval('=', 'string'),
          new xmlrpcval($move_id, 'int'),
        ), "array"),
        new xmlrpcval(array(
          new xmlrpcval('account_id', 'string'),
          new xmlrpcval('=', 'string'),
          new xmlrpcval($account_id, 'int'),
        ), "array"),
      );
    $debtor_move_line_ids = $this->connector->search('account.move.line', $keys);
    if ($debtor_move_line_ids === false) {
      return false;
    }
    $debtor_move_line_id = reset($debtor_move_line_ids);
    return $debtor_move_line_id->scalarval();
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

