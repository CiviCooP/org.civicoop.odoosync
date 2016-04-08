<?php

class CRM_OdooContactSync_EmailSynchronisator extends CRM_Odoosync_Model_ObjectSynchronisator {
  
  protected $_emailCache = array();
  
  public function isThisItemSyncable(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $email = $this->getEmail($sync_entity->getEntityId());
    $odoo_partner_id = $sync_entity->findOdooIdByEntity('civicrm_contact', $email['contact_id']);
    if ($odoo_partner_id <= 0) {
      return false;
    }

    //only sync primary addresses
    if ($email['is_primary']) {
      return true;
    }
    
    //email address is not syncable, remove existing email from Odoo
    if (!empty($sync_entity->getOdooId()) && $sync_entity->getOdooId() > 0) {
      $this->removeEmailFromOdooPartner($sync_entity->getOdooId(), $sync_entity, $email);
    }
    
    return false;
  }
  
  protected function removeEmailFromOdooPartner($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity, $email) {
    $ignoreFields = array('is_primary', 'id');
    foreach($email as $field => $val) {
      if (in_array($field, $ignoreFields)) {
        continue;
      }
      $email[$field] = '';
    }
    
    $parameters = $this->getOdooParameters($email, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'clear');
    if ($this->connector->write($this->getOdooResourceType(), $odoo_id, $parameters)) {
      return $odoo_id;
    }
  }
  
  public function existsInCivi(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    try {
      $email = civicrm_api3('Email', 'getsingle', array('id' => $sync_entity->getEntityId()));
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
    //an insert is impossible because we only sync primary addresses
    //and store them at the partner entity in Odoo
    //throw new Exception('It is imposible to insert an address into Odoo');
    return -1; //a -1 ID means that the entity does not exist in Odoo
  }
  
  /**
   * Update an existing contact in Odoo
   * 
   * @param type $odoo_id
   * @param CRM_Odoosync_Model_OdooEntity $sync_entit
   */
  public function performUpdate($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $email = $this->getEmail($sync_entity->getEntityId());
    $parameters = $this->getOdooParameters($email, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'write');
    if ($this->connector->write($this->getOdooResourceType(), $odoo_id, $parameters)) {
      return $odoo_id;
    }
    throw new Exception("Could not update partner in Odoo");
  }
  
  public function getSyncData(\CRM_Odoosync_Model_OdooEntity $sync_entity, $odoo_id) {
    $email = $this->getEmail($sync_entity->getEntityId());
    $parameters = $this->getOdooParameters($email, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'write');
    return $parameters;
  }
  
  /**
   * Delete contact from Odoo
   * 
   * @param type $odoo_id
   * @param CRM_Odoosync_Model_OdooEntity $sync_entity
   */
  public function performDelete($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $objEmail = new CRM_Core_BAO_Email();
    $email = array();
    CRM_Core_DAO::storeValues($objEmail, $email);
    $this->removeEmailFromOdooPartner($odoo_id, $sync_entity, $email);
  }
  
  /**
   * If the email is a primary email retrieve the odoo of the contact
   * 
   * In odoo we store the primary email at partner level because there is no such thing as an email entity in Odoo
   * 
   * @param CRM_Odoosync_Model_OdooEntity $sync_entity
   * @return boolean
   */
  public function findOdooId(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    try {
      $email = $this->getEmail($sync_entity->getEntityId());
      $contact_id = $email['contact_id'];
    
      //look up the partner id if address is primary
      if ($email['is_primary']) {
        $odoo_id = $sync_entity->findOdooIdByEntity('civicrm_contact', $contact_id);
        return $odoo_id;
      }    
    } catch (Exception $e) {
      //do nothing
    }
    
    if ($sync_entity->getOdooId()) {
      return $sync_entity->getOdooId();
    }
    
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
  protected function getOdooParameters($email, $entity, $entity_id, $action) {
    $parameters = array(
      'email' => new xmlrpcval($email['email'], 'string'),
    );
    
    $this->alterOdooParameters($parameters, $this->getOdooResourceType(), $entity, $entity_id, $action);
    
    return $parameters;
  }
 
  protected function getEmail($entity_id) {
    if (!isset($this->_emailCache[$entity_id])) {
      $this->_emailCache[$entity_id] = civicrm_api3('Email', 'getsingle', array('id' => $entity_id));
    }
    
    return $this->_emailCache[$entity_id];
  }
  
}


