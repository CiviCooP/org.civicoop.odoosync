<?php

/* 
 *
 * 
 */

Class CRM_OdooContactSync_ContactDefinition implements CRM_Odoosync_Model_ObjectDefinitionInterface {
  
  public function getCiviCRMEntityName() {
    return 'civicrm_contact';
  }
  
  public function isObjectNameSupported($objectName) {
    $contactTypes = CRM_Contact_BAO_ContactType::basicTypes();
    if (in_array($objectName, $contactTypes)) {
      return true;
    }
    return false;
  }
  
  public function getSynchronisator() {
    return new CRM_OdooContactSync_ContactSynchronisator();
  }
  
  public function getWeight() {
    return -100;
  }
  
  public function getName() {
    return 'civicrm_contact';
  }
}


