<?php

final class CRM_Odoosync_Model_Dependency {
  
  private $entity;
  
  private $entity_id;
  
  public function __construct($entity, $entity_id) {
    $this->entity = $entity;
    $this->entity_id = $entity_id;
  }
  
  public function getEntity() {
    return $this->entity;
  }
  
  public function getEntityId() {
    return $this->entity_id;
  }
  
}

