<?php

/* 
 *
 * 
 */

Class CRM_OdooContactSync_EmailDefinition extends CRM_Odoosync_Model_ObjectDefinition implements CRM_Odoosync_Model_ObjectDependencyInterface {
  
  public function getCiviCRMEntityName() {
    return 'civicrm_email';
  }
  
  public function isObjectNameSupported($objectName) {
    if ($objectName == 'Email') {
      return true;
    }
    return false;
  }
  
  protected function getSynchronisatorClass() {
    return 'CRM_OdooContactSync_EmailSynchronisator';
  }
  
  public function getWeight($action) {
    if ($action == 'DELETE') {
      return -10;
    }
    return 0;
  }
  
  public function getName() {
    return 'civicrm_email';
  }
  
  public function getSyncDependenciesForEntity($entity_id, $data=false) {
    $dep = array();
    try {
      if (is_array($data) && isset($data['contact_id'])) {
         $contact_id = $data['contact_id'];
      } else {
        $contact_id = civicrm_api3('Email', 'getvalue', array('return' => 'contact_id', 'id' => $entity_id));
      }
      
      
      $dep[] = new CRM_Odoosync_Model_Dependency('civicrm_contact', $contact_id);
      
      $emails = civicrm_api3('Email', 'get', array('contact_id' => $contact_id));
      foreach($emails['values'] as $email) {
        if (empty($email['id']) && $email['id'] == $entity_id) {
          //skip current address
          continue;
        }
        
        $weightOffset = -1;
        if ($email['is_primary']) {
          $weightOffset = 1; //make sure primary addresses are synced as last
          //by lowering the priority of the primary address we make sure
          //that non primary addresses can empty the primary adress field on 
          //res.partner before the primary sets them again
        }
        
        $dep[] = new CRM_Odoosync_Model_Dependency('civicrm_email', $email['id'], $weightOffset, true);
      }
    } catch (Exception $ex) {
       //do nothing
    }
    return $dep;
  }
  
  public function getCiviCRMEntityDataById($id) {
    return civicrm_api3('Email', 'getsingle', array('id' => $id));
  }
}


