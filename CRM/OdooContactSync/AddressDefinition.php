<?php

/* 
 *
 * 
 */

Class CRM_OdooContactSync_AddressDefinition implements CRM_Odoosync_Model_ObjectDefinitionInterface, CRM_Odoosync_Model_ObjectDependencyInterface {
  
  public function getCiviCRMEntityName() {
    return 'civicrm_address';
  }
  
  public function isObjectNameSupported($objectName) {
    if ($objectName == 'Address') {
      return true;
    }
    return false;
  }
  
  public function getSynchronisator() {
    return new CRM_OdooContactSync_AddressSynchronisator($this);
  }
  
  public function getWeight() {
    return 0;
  }
  
  public function getName() {
    return 'civicrm_address';
  }
  
  public function getSyncDependenciesForEntity($entity_id) {
    $dep = array();
    try {
      $contact_id = civicrm_api3('Address', 'getvalue', array('return' => 'contact_id', 'id' => $entity_id));
      $dep[] = new CRM_Odoosync_Model_Dependency('civicrm_contact', $contact_id);
    } catch (Exception $ex) {
       //do nothing
    }
    return $dep;
  }
}


