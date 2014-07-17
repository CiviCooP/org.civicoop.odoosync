<?php

/* 
 *
 * 
 */

Class CRM_OdooContactSync_AddressDefinition extends CRM_Odoosync_Model_ObjectDefinition implements CRM_Odoosync_Model_ObjectDependencyInterface {
  
  public function getCiviCRMEntityName() {
    return 'civicrm_address';
  }
  
  public function isObjectNameSupported($objectName) {
    if ($objectName == 'Address') {
      return true;
    }
    return false;
  }
  
  protected function getSynchronisatorClass() {
    return 'CRM_OdooContactSync_AddressSynchronisator';
  }
  
  public function getWeight($action) {
    if ($action == 'DELETE') {
      return -10;
    }
    return 0;
  }
  
  public function getName() {
    return 'civicrm_address';
  }
  
  public function getSyncDependenciesForEntity($entity_id, $data=false) {
    $dep = array();
    try {
      if (is_array($data) && isset($data['contact_id'])) {
         $contact_id = $data['contact_id'];
      } else {
        $contact_id = civicrm_api3('Address', 'getvalue', array('return' => 'contact_id', 'id' => $entity_id));
      }
      
      
      $dep[] = new CRM_Odoosync_Model_Dependency('civicrm_contact', $contact_id);
      
      $addresses = civicrm_api3('Address', 'get', array('contact_id' => $contact_id));
      foreach($addresses['values'] as $address) {
        if (empty($address['id']) && $address['id'] == $entity_id) {
          //skip current address
          continue;
        }
        
        $weightOffset = -1;
        if ($address['is_primary']) {
          $weightOffset = 1; //make sure primary addresses are synced as last
          //by lowering the priority of the primary address we make sure
          //that non primary addresses can empty the primary adress field on 
          //res.partner before the primary sets them again
        }
        
        $dep[] = new CRM_Odoosync_Model_Dependency('civicrm_address', $address['id'], $weightOffset, true);
      }
    } catch (Exception $ex) {
       //do nothing
    }
    return $dep;
  }
}


