<?php

class CRM_OdooContactSync_PhoneSynchronisator extends CRM_Odoosync_Model_ObjectSynchronisator {
  
  protected $_phoneCache = array();
  
  public function isThisItemSyncable(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $phone = $this->getPhone($sync_entity->getEntityId());
    $field = $this->detemerineOdooFieldForPhone($phone);
    
    //only sync phone types
    if ($field) {
      return true;
    }
    
    //if phone is not syncable, remove existing phone from Odoo
    if (!empty($sync_entity->getOdooId()) && $sync_entity->getOdooId() > 0) {
      $this->removePhoneFromOdooPartner($sync_entity->getOdooId(), $sync_entity, $phone);
    }
    
    return false;
  }
  
  protected function removePhoneFromOdooPartner($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity, $phone) {   
    $parameters = $this->getOdooParameters($phone, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'clear');
    if (!empty($sync_entity->getOdooField()) && !isset($parameters[$sync_entity->getOdooField()])) {
      $parameters[$sync_entity->getOdooField()] = new xmlrpcval('', 'string');
    }
    
    if ($this->connector->write($this->getOdooResourceType(), $odoo_id, $parameters)) {
      return $odoo_id;
    }
  }
  
  public function existsInCivi(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    try {
      $phone = civicrm_api3('Phone', 'getsingle', array('id' => $sync_entity->getEntityId()));
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
    //an insert is impossible because we only sync primary phones
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
    $phone = $this->getPhone($sync_entity->getEntityId());
    $field = $this->detemerineOdooFieldForPhone($phone);
    $parameters = $this->getOdooParameters($phone, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'write');
    if ($this->connector->write($this->getOdooResourceType(), $odoo_id, $parameters)) {
      $sync_entity->setOdooField($field);
      return $odoo_id;
    }
    throw new Exception("Could not update partner in Odoo");
  }
  
  public function getSyncData(\CRM_Odoosync_Model_OdooEntity $sync_entity, $odoo_id) {
    $phone = $this->getPhone($sync_entity->getEntityId());
    $parameters = $this->getOdooParameters($phone, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'write');;
    return $parameters;
  }
  
  /**
   * Delete contact from Odoo
   * 
   * @param type $odoo_id
   * @param CRM_Odoosync_Model_OdooEntity $sync_entity
   */
  public function performDelete($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $objPhone = new CRM_Core_BAO_Phone();
    $phone = array();
    CRM_Core_DAO::storeValues($objPhone, $phone);
    $this->removePhoneFromOdooPartner($odoo_id, $sync_entity, $phone);
  }
  
  /**
   * If the email is a valid phone type retrieve the odoo of the contact
   * 
   * In odoo we store only certain phone types at partner level because there is no such thing as a phone entity in Odoo
   * 
   * @param CRM_Odoosync_Model_OdooEntity $sync_entity
   * @return boolean
   */
  public function findOdooId(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    try {
      $phone = $this->getPhone($sync_entity->getEntityId());
      $contact_id = $phone['contact_id'];
      $field = $this->detemerineOdooFieldForPhone($phone);
    
      //look up the partner id if phone is from a valid type
      if ($field) {
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
  protected function getOdooParameters($phone, $entity, $entity_id, $action) {
    $parameters = array();
    $field = $this->detemerineOdooFieldForPhone($phone);
    if ($field) {
      $parameters[$field] = new xmlrpcval($phone['phone'], 'string');
    }
    
    $this->alterOdooParameters($parameters, $this->getOdooResourceType(), $entity, $entity_id, $action);
    
    return $parameters;
  }
  
  protected function phoneTypeToOdooField() {
    return array(
      'Phone' => 'phone',
      ts('Phone') => 'phone',
      'Mobile' => 'mobile',
      ts('Mobile') => 'mobile',
      'Fax' => 'fax',
      ts('Fax') => 'fax',
    );
  }
  
  protected function detemerineOdooFieldForPhone($phone) {
    $translations = $this->phoneTypeToOdooField();
    $phoneType = CRM_Core_OptionGroup::getValue('phone_type', $phone['phone_type_id'], 'value', 'String', 'name');
    if (isset($translations[$phoneType])) {
      return $translations[$phoneType];
    }    
    return false;
  }
 
  protected function getPhone($entity_id) {
    if (!isset($this->_phoneCache[$entity_id])) {
      $this->_phoneCache[$entity_id] = civicrm_api3('Phone', 'getsingle', array('id' => $entity_id));
    }
    
    return $this->_phoneCache[$entity_id];
  }
  
}


