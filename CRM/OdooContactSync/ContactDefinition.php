<?php

/* 
 *
 * 
 */

Class CRM_OdooContactSync_ContactDefinition extends CRM_Odoosync_Model_ObjectDefinition implements CRM_Odoosync_Model_ObjectDependencyInterface {
  
  public function getCiviCRMEntityName() {
    return 'civicrm_contact';
  }
  
  public function isObjectNameSupported($objectName) {
    $contactTypes = CRM_Contact_BAO_ContactType::basicTypes();
    if (in_array($objectName, $contactTypes)) {
      return true;
    }
    if ($objectName == 'civicrm_contact') {
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

  public function getSyncDependenciesForEntity($entity_id, $data=false) {
    $dep = array();
    try {
      $weightOffset = +10;
      $phones = civicrm_api3('Phone', 'get', array('contact_id' => $entity_id));
      foreach($phones['values'] as $phone) {
        $dep[] = new CRM_Odoosync_Model_Dependency('civicrm_phone', $phone['id'], $weightOffset, true);
      }
      $emails = civicrm_api3('Email', 'get', array('contact_id' => $entity_id));
      foreach($emails['values'] as $email) {
        $dep[] = new CRM_Odoosync_Model_Dependency('civicrm_email', $email['id'], $weightOffset, true);
      }
      $addresses = civicrm_api3('Address', 'get', array('contact_id' => $entity_id));
      foreach($addresses['values'] as $address) {
        $dep[] = new CRM_Odoosync_Model_Dependency('civicrm_address', $address['id'], $weightOffset, true);
      }
      $contributions = civicrm_api3('Contribution', 'get', array('contact_id' => $entity_id, 'options' => array('limit' => 100000000000)));
      foreach($contributions['values'] as $contribution) {
        $odoo_id = CRM_Odoosync_Model_OdooEntity::findOdooIdByEntityAndEntityId('civicrm_contribution', $contribution['id']);
        // Only sync contributions which aren't pushed to Odoo.
        if (empty($odoo_id)) {
          $dep[] = new CRM_Odoosync_Model_Dependency('civicrm_contribution', $contribution['id'], $weightOffset, TRUE);
        }
      }
    } catch (Exception $ex) {
      //do nothing
    }
    return $dep;
  }
}


