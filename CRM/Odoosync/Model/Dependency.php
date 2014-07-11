<?php

final class CRM_Odoosync_Model_Dependency {
  
  private $entity;
  
  private $entity_id;
  
  private $weight_offset;
  
  private $queueForUpdate;
  
  public function __construct($entity, $entity_id, $weight_offset = -1, $queueForUpdate=false) {
    $this->entity = $entity;
    $this->entity_id = $entity_id;
    $this->weight_offset = $weight_offset;
    $this->queueForUpdate = $queueForUpdate;
    
  }
  
  public function getEntity() {
    return $this->entity;
  }
  
  public function getEntityId() {
    return $this->entity_id;
  }
  
  /**
   * The weight offset is used to determine which gets synced first
   * An offset of below zero means that this dependency gets synced first
   * An offset of above zero means that this dependency gets synced later
   * 
   * @return type
   */
  public function getWeightOffset() {
    return $this->weight_offset;
  }
  
  /**
   * Returns true if we should queue this dependency for update
   * Returns false if the dependency should only be inserted before
   * 
   * @return type
   */
  public function isQueuedForUpdate() {
    return $this->queueForUpdate;
  }
  
}

