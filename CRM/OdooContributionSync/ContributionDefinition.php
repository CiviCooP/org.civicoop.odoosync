<?php

Class CRM_OdooContributionSync_ContributionDefinition extends CRM_Odoosync_Model_ObjectDefinition implements CRM_Odoosync_Model_ObjectDependencyInterface {
  
  public function getCiviCRMEntityName() {
    return 'civicrm_contribution';
  }
  
  public function isObjectNameSupported($objectName) {
    if ($objectName == 'Contribution') {
      return true;
    }
    return false;
  }
  
  protected function getSynchronisatorClass() {
    return 'CRM_OdooContributionSync_ContributionSynchronisator';
  }
  
  public function getWeight($action) {
    if ($action == 'DELETE') {
      return -30;
    }
    return -10;
  }
  
  public function getName() {
    return 'civicrm_contribution';
  }
  
  public function getSyncDependenciesForEntity($entity_id, $data=false) {
    $dep = array();
    try {
      if (is_array($data) && isset($data['contact_id'])) {
         $contact_id = $data['contact_id'];
      } else {
        $contact_id = civicrm_api3('Contribution', 'getvalue', array('return' => 'contact_id', 'id' => $entity_id));
      }
      
      $dep[] = new CRM_Odoosync_Model_Dependency('civicrm_contact', $contact_id);
    } catch (Exception $ex) {
       //do nothing
    }
    return $dep;
  }
  
  public function getCiviCRMEntityDataById($id) {
    return civicrm_api3('Contribution', 'getsingle', array('id' => $id));
  }
}


