<?php

/* 
 *
 * 
 */

Class CRM_OdooContactSync_ContactDefinition extends CRM_Odoosync_Model_ObjectDefinition {
  
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
  
  protected function getSynchronisatorClass() {
    return 'CRM_OdooContactSync_ContactSynchronisator';
  }
  
  public function getWeight($action) {
    if ($action == 'DELETE') {
      return -200;
    }
    return -100;
  }
  
  public function getName() {
    return 'civicrm_contact';
  }
  
  public function getCiviCRMEntityDataById($id) {
    return civicrm_api3('Contact', 'getsingle', array('id' => $id));
  }
}


