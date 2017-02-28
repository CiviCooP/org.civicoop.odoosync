<?php

final class CRM_Odoosync_Config_OdooParameters {
  
  private static $_instance;
  
  private $url;
  
  private $databasename;
  
  private $username;
  
  private $password;
  
  private $view_partner_url;
  
  private function __construct() {
    /**
     * Read the settings from civicrm.settings.php
     *
     * global $odoo_settings
     * $odoo_settings['url'] = 'http://your.odoo:8069/xmlrpc/';
     * $odoo_settings['databasename'] = 'databasename';
     * $odoo_settings['username'] = 'username';
     * $odoo_settings['password'] - 'password';
     * $odoo_settings['view_partner_url'] = 'http://your.odoo:8069/?db=databasename#id={partner_id}&view_type=form&model=res.partner&action=569';
     * $odoo_settings['view_invoice_url'] = 'http://your.odoo:8069/?db=databasename#id={partner_id}&view_type=form&model=res.invoice&action=456';
     */
    global $odoo_settings;
    $this->url = $odoo_settings['url'];
    $this->databasename = $odoo_settings['databasename'];
    $this->username = $odoo_settings['username'];
    $this->password = $odoo_settings['password'];
    $this->view_partner_url = $odoo_settings['view_partner_url'];
    $this->view_invoice_url = $odoo_settings['view_invoice_url'];
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
