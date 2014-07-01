<?php

abstract class CRM_Odoosync_Model_ObjectSynchronisator {
  
  abstract public function performInsert(CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  abstract public function performUpdate($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entit);
  
  abstract public function performDelete($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  abstract public function findOdooId(CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  abstract public function existsInOdoo($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  protected $connector;
  
  public function __construct() {
    $this->connector = CRM_Odoosync_Connector::singleton();
  }
  
}

