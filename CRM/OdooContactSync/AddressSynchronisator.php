<?php

class CRM_OdooContactSync_AddressSynchronisator extends CRM_Odoosync_Model_ObjectSynchronisator {
  
  protected $_addressCache = array();
  
  public function isThisItemSyncable(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $address = $this->getAddress($sync_entity->getEntityId());
    //only sync primary addresses
    if ($address['is_primary']) {
      return true;
    }
    
    //adress is not syncable, clear address of partner if item is already synced intoo Odoo    
    $this->clearAddressInOdoo($sync_entity, $address);
    
    return false;
  }
  
  
  protected function clearAddressInOdoo(CRM_Odoosync_Model_OdooEntity $sync_entity, $address) {    
    //adress is not syncable, clear address of partner if item is already synced intoo Odoo
    if (!empty($sync_entity->getOdooId()) && $sync_entity->getOdooId() > 0) {
      $ignoreFields = array('is_primary', 'id');
      foreach($address as $field => $val) {
        if (in_array($field, $ignoreFields)) {
          continue;
        }
        $address[$field] = '';
      }
      
      $odoo_id = $sync_entity->getOdooId();
      $parameters = $this->getOdooParameters($address, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'clear');
      if (!$this->connector->write($this->getOdooResourceType(), $odoo_id, $parameters)) {
        throw new Exception('Could not clear address in Odoo');
      }
    }    
  }
  
  public function existsInCivi(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    try {
      $address = civicrm_api3('Address', 'getsingle', array('id' => $sync_entity->getEntityId()));
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
    $address = $this->getAddress($sync_entity->getEntityId());
    $parameters = $this->getOdooParameters($address, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'write');
    if ($this->connector->write($this->getOdooResourceType(), $odoo_id, $parameters)) {
      return $odoo_id;
    }
    throw new Exception("Could not update partner in Odoo");
  }
  
  public function getSyncData(\CRM_Odoosync_Model_OdooEntity $sync_entity, $odoo_id) {
    $address = $this->getAddress($sync_entity->getEntityId());
    $parameters = $this->getOdooParameters($address, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'write');
    return $parameters;
  }
  
  /**
   * Delete contact from Odoo
   * 
   * @param type $odoo_id
   * @param CRM_Odoosync_Model_OdooEntity $sync_entity
   */
  public function performDelete($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $objAdress = new CRM_Core_BAO_Address();
    $address = array();
    CRM_Core_DAO::storeValues($objAdress, $address);
    $this->clearAddressInOdoo($sync_entity, $address);
  }
  
  /**
   * If the address is a primary address retrieve the odoo of the contact
   * 
   * In odoo we store the primary address at partner level because there is no such thing as an address entity in Odoo
   * 
   * @param CRM_Odoosync_Model_OdooEntity $sync_entity
   * @return boolean
   */
  public function findOdooId(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $address = $this->getAddress($sync_entity->getEntityId());
    $contact_id = $address['contact_id'];
    
    //look up the partner id if address is primary
    if ($address['is_primary']) {
      $odoo_id = $sync_entity->findOdooIdByEntity('civicrm_contact', $contact_id);
      return $odoo_id;
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
  protected function getOdooParameters($address, $entity, $entity_id, $action) {
    $parameters = array(
      'street' => new xmlrpcval($address['street_address'], 'string'),
      'city' => new xmlrpcval($address['city'], 'string'),
      'zip' => new xmlrpcval($address['postal_code'], 'string'),
      'street2' => new xmlrpcval($address['supplemental_address_1'], 'string'),
    );
    
    if (!empty($address['country_id'])) {
      $country_id = $this->findOdooCountryId($address['country_id']);
      $parameters['country_id'] = new xmlrpcval($country_id, 'int');
    }
    
    if (!empty($address['state_province_id']) && !empty($address['country_id'])) {
      $state_id = $this->findOdooStateId($address['state_province_id'], $address['country_id']);
      $parameters['state_id'] = new xmlrpcval($state_id, 'int');
    }
    
    $this->alterOdooParameters($parameters, $this->getOdooResourceType(), $entity, $entity_id, $action);
    
    return $parameters;
  }
 
  protected function getAddress($entity_id) {
    if (!isset($this->_addressCache[$entity_id])) {
      $this->_addressCache[$entity_id] = civicrm_api3('Address', 'getsingle', array('id' => $entity_id));
      if (!empty($this->_addressCache[$entity_id]['master_id'])) {
        $master_address = $this->getAddress($this->_addressCache[$entity_id]['master_id']);
        $master_address['contact_id'] = $this->_addressCache[$entity_id]['contact_id'];
        $master_address['id'] = $this->_addressCache[$entity_id]['id'];
        $this->_addressCache[$entity_id] = $master_address;
      }
    }
    
    return $this->_addressCache[$entity_id];
  }
  
  /**
   * Returns the Odoo country ID for a civi country
   * 
   * @param type $civi_country_id
   */
  protected function findOdooCountryId($civi_country_id) {
    static $cache = array();
    if (!isset($cache[$civi_country_id])) {
      $cache[$civi_country_id] = false; //when no country is found we should return false
      $isoCode = CRM_Core_PseudoConstant::countryIsoCode($civi_country_id);
    
      //find country by its iso code
      $searchKey = array(
        new xmlrpcval(array(
          new xmlrpcval('code', 'string'),
          new xmlrpcval('=', 'string'),
          new xmlrpcval($isoCode, 'string'),
        ), "array")
      ); 

      $country_ids = $this->connector->search('res.country', $searchKey);
      foreach($country_ids as $id_element) {
        $id = $id_element->scalarval();
        $cache[$civi_country_id] = $id;
        break;
      }
    }
    
    return $cache[$civi_country_id];
  }
  
    /**
   * Returns the Odoo country ID for a civi country
   * 
   * @param type $civi_country_id
   */
  protected function findOdooStateId($civi_county_id, $civi_country_id) {
    static $cache = array();
    if (!isset($cache[$civi_county_id])) {
      $cache[$civi_county_id] = false; //when no country is found we should return false
      $state = CRM_Core_PseudoConstant::stateProvince($civi_county_id);
      
      //find country by its iso code
      $searchKey = array(
        new xmlrpcval(array(
          new xmlrpcval('name', 'string'),
          new xmlrpcval('=', 'string'),
          new xmlrpcval($state, 'string'),
        ), "array")
      ); 

      $state_ids = $this->connector->search('res.country.state', $searchKey);
      foreach($state_ids as $id_element) {
        $id = $id_element->scalarval();
        $cache[$civi_county_id] = $id;
        break;
      }
      
      //create province in Odoo
      $odoo_country_id = $this->findOdooCountryId($civi_country_id);
      if ($odoo_country_id) {
        //only create state if country exist
        $state_code = CRM_Core_PseudoConstant::stateProvinceAbbreviation($civi_county_id);
        $parameters['code'] = new xmlrpcval($state_code, "string");
        $parameters['name'] = new xmlrpcval($state, "string");
        $parameters['country_id'] = new xmlrpcval($odoo_country_id, "int");
      
        $new_odoo_state_id = $this->connector->create('res.country.state', $parameters);
        $cache[$civi_county_id] = $new_odoo_state_id;
      }
    }
    
    return $cache[$civi_county_id];
  }
}


