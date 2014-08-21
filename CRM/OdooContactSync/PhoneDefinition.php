<?php

/* 
 *
 * 
 */

Class CRM_OdooContactSync_PhoneDefinition extends CRM_Odoosync_Model_ObjectDefinition implements CRM_Odoosync_Model_ObjectDependencyInterface {
  
  public function getCiviCRMEntityName() {
    return 'civicrm_phone';
  }
  
  public function isObjectNameSupported($objectName) {
    if ($objectName == 'Phone') {
      return true;
    }
    return false;
  }
  
  protected function getSynchronisatorClass() {
    return 'CRM_OdooContactSync_PhoneSynchronisator';
  }
  
  public function getWeight($action) {
    if ($action == 'DELETE') {
      return -10;
    }
    return 0;
  }
  
  public function getName() {
    return 'civicrm_phone';
  }
  
  public function getSyncDependenciesForEntity($entity_id, $data=false) {
    $dep = array();
    try {
      if (is_array($data) && isset($data['contact_id'])) {
         $contact_id = $data['contact_id'];
      } else {
        $contact_id = civicrm_api3('Phone', 'getvalue', array('return' => 'contact_id', 'id' => $entity_id));
      }
      
      
      $dep[] = new CRM_Odoosync_Model_Dependency('civicrm_contact', $contact_id);
      
      $phones = civicrm_api3('Phone', 'get', array('contact_id' => $contact_id));
      foreach($phones['values'] as $phone) {
        if (empty($phone['id']) && $phone['id'] == $entity_id) {
          //skip current phone
          continue;
        }
        
        $weightOffset = -1;        
        $dep[] = new CRM_Odoosync_Model_Dependency('civicrm_phone', $phone['id'], $weightOffset, true);
      }
    } catch (Exception $ex) {
       //do nothing
    }
    return $dep;
  }
  
  public function getCiviCRMEntityDataById($id) {
    return civicrm_api3('Phone', 'getsingle', array('id' => $id));
  }
}


