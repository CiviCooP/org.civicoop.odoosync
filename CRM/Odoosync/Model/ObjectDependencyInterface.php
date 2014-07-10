<?php

interface CRM_Odoosync_Model_ObjectDependencyInterface {
  
  /**
   * Returns an array with instances of CRM_Odoosync_Model_Dependency
   * 
   * This is useful for e.g. the address entity which assumes that the contact 
   * entity is already synced or queued for sync
   * 
   * If there are no dependencies then you should return an empty array
   * 
   * @return array of CRM_Odoosync_Model_Dependency
   */
  public function getSyncDependenciesForEntity($entity_id);
  
}

