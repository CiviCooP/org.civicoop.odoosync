<?php

class CRM_OdooContactSync_PartnerLink {
  
  protected $contact_id;
  
  protected $config;
  
  protected $odoo_id;
  
  public function __construct($contact_id) {
    $this->config = CRM_Odoosync_Config_OdooParameters::singleton();
    $this->contact_id = $contact_id;
    
    //find odoo id
    $this->odoo_id = false;
    $sql = "SELECT `odoo_id`  FROM `civicrm_odoo_entity` WHERE `entity` = %1  AND `entity_id`  = %2";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
      1 => array('civicrm_contact', 'String'),
      2 => array($contact_id, 'Integer'),
    ));
    
    if ($dao->fetch()) {
      $this->odoo_id = $dao->odoo_id ? $dao->odoo_id : false;
    } 

  }
  
  public function contactIsPartner() {
    if ($this->odoo_id) {
      return true;
    } 
    return false;
  }
  
  public function getLink() {
    $url = $this->config->getUrl();
    $url = str_replace("/xmlrpc/", "", $url);
    $url .= "#id=".$this->odoo_id."&view_type=form&model=res.partner&menu_id=79&action=62";
    return $url;
  }
  
} 

