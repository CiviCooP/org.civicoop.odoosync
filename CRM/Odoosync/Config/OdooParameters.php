<?php

final class CRM_Odoosync_Config_OdooParameters {
  
  private static $_instance;
  
  private $url;
  
  private $databasename;
  
  private $username;
  
  private $password;
  
  private $view_partner_url;
  
  private function __construct() {
    $this->url = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'url');
    $this->databasename = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'databasename');
    $this->username = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'username');
    $this->password = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'password');
    $this->view_partner_url = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'view_partner_url');
    $this->view_invoice_url = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'view_invoice_url');
  }
  
  /**
   * 
   * @return CRM_Odoosync_Config_OdooParameters
   */
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
  
  public function getViewPartnerUrl($partner_id) {
    return str_replace("{partner_id}", $partner_id, $this->view_partner_url);
  }

  public function getViewInvoiceUrl($invoice_id, $partner_id='') {
    $url = $this->view_invoice_url;
    $url = str_replace("{partner_id}", $partner_id, $url);
    $url = str_replace("{invoice_id}", $invoice_id, $url);
    return $url;
  }
  
  
  
  
}
