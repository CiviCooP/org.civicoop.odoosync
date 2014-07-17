<?php

interface CRM_Odoosync_Model_ObjectDefinitionInterface {
  
  /**
   * @return String entity name e.g. civicrm_contact
   */
  public function getCiviCRMEntityName();
  
  /**
   * Should return if this definition supports this object
   * 
   * @param String objectName passed by the post hook
   * @return bool
   */
  public function isObjectNameSupported($objectName);
  
  /**
   * Returns the weight of this object
   * 
   * This way we can control which should be synced first
   * e.g. a civicrm_contact should be synced before a civicrm_address
   * 
   */
  public function getWeight($action);
  
  /**
   * Returns the synchronisator for this entity
   * 
   * @return CRM_Odoosync_Model_ObjectSynchronisationInterface
   */
  public function getSynchronisator();
  
  /**
   * Returns the name of the synchronisator
   * 
   * @return String
   */
  public function getName();
  
}

