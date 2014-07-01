<?php

class CRM_OdooContactSync_ContactSynchronisator extends CRM_Odoosync_Model_ObjectSynchronisator {
  
  public function performInsert(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $contact = civicrm_api3('Contact', 'getsingle', array('id' => $sync_entity->getEntityId()));
    
    $parameters = array(
      'name' => new xmlrpcval($contact['display_name'], 'string'),
    );
    
    $odoo_id = $this->connector->create('res.partner', $parameters);
    if ($odoo_id) {
      return $odoo_id;
    }
    throw new Exception('Could not insert contact into Odoo');
  }
  
  public function performUpdate($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entit) {
    
  }
  
  public function performDelete($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    
  }
  
  public function findOdooId(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    return false;
  }
  
  public function existsInOdoo($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    return false;
  }
  
}


