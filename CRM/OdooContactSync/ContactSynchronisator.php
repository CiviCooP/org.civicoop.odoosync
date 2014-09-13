<?php

class CRM_OdooContactSync_ContactSynchronisator extends CRM_Odoosync_Model_ObjectSynchronisator {
  
  protected $_contactCache = array();
  
  public function isThisItemSyncable(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $contact = $this->getContact($sync_entity->getEntityId());
    
    //do not sync households
    if ($contact['contact_type'] == 'Household') {
      return false;
    }
    
    return true;
  }
  
  public function existsInCivi(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    try {
      $contact = civicrm_api3('Contact', 'getsingle', array('id' => $sync_entity->getEntityId()));
    } catch (CiviCRM_API3_Exception $ex) {
      return false;
    }
    return true;
  }
  
  
  /**
   * Insert a new Contact into Odoo
   * 
   * @param CRM_Odoosync_Model_OdooEntity $sync_entity
   * @return type
   * @throws Exception
   */
  public function performInsert(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $contact = $this->getContact($sync_entity->getEntityId());
    $parameters = $this->getOdooParameters($contact, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'create');
    $odoo_id = $this->connector->create($this->getOdooResourceType(), $parameters);
    if ($odoo_id) {
      return $odoo_id;
    }
    throw new Exception('Could not insert contact into Odoo');
  }
  
  /**
   * Update an existing contact in Odoo
   * 
   * @param type $odoo_id
   * @param CRM_Odoosync_Model_OdooEntity $sync_entit
   */
  public function performUpdate($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $contact = $this->getContact($sync_entity->getEntityId());
    $parameters = $this->getOdooParameters($contact, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'write');
    if ($this->connector->write($this->getOdooResourceType(), $odoo_id, $parameters)) {
      return $odoo_id;
    }
    throw new Exception('Could not update contact into Odoo');
  }
  
  public function getSyncData(\CRM_Odoosync_Model_OdooEntity $sync_entity, $odoo_id) {
    $contact = $this->getContact($sync_entity->getEntityId());
    $parameters = $this->getOdooParameters($contact, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'write');
    return $parameters;
  }
  
  /**
   * Delete contact from Odoo
   * 
   * @param type $odoo_id
   * @param CRM_Odoosync_Model_OdooEntity $sync_entity
   */
  public function performDelete($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    if ($this->connector->unlink($this->getOdooResourceType(), $odoo_id)) {
      return -1;
    }
    throw new Exception('Could not delete contact from Odoo');
  }
  
  public function findOdooId(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    return false;
  }
  
  /**
   * Returns the name of the Odoo resource e.g. res.partner
   * 
   * @return string
   */
  public function getOdooResourceType() {
    return 'res.partner';
  }
  
  /**
   * Returns the parameters to update/insert an Odoo object
   * 
   * @param type $contact
   * @return \xmlrpcval
   */
  protected function getOdooParameters($contact, $entity, $entity_id, $action) {
    $parameters = array(
      'display_name' => new xmlrpcval($contact['display_name'], 'string'),
      'name' => new xmlrpcval($contact['display_name'], 'string'),
      'title' => new xmlrpcval($contact['prefix'], 'string'),
      'is_company' => new xmlrpcval($contact['contact_type'] != 'Individual' ? true : false, 'boolean'),
    );
    
    $this->alterOdooParameters($parameters, $this->getOdooResourceType(), $entity, $entity_id, $action);
    
    return $parameters;
  }
 
  protected function getContact($contactId) {
    if (!isset($this->_contactCache[$contactId])) {
      $this->_contactCache[$contactId] = civicrm_api3('Contact', 'getsingle', array('id' => $contactId));
    }
    
    return $this->_contactCache[$contactId];
  }
  
}


