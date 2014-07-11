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
   * @param int $entity_id
   * @param array $data data array holding data for entity (useful for when the entity_id could not be found in the database)
   * 
   * @return array of CRM_Odoosync_Model_Dependency
   */
  public function getSyncDependenciesForEntity($entity_id, $data=false);
  
}

