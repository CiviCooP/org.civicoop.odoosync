<?php

class CRM_Odoosync_Model_OdooEntity {
  
  protected $id;
  
  protected $entity;
  
  protected $entity_id;
  
  protected $odoo_resource;
  
  protected $odoo_id;
  
  protected $action;
  
  protected $status;
  
  public function __construct(CRM_Core_DAO $dao) {
    $this->id = $dao->id;
    $this->entity = $dao->entity;
    $this->entity_id = $dao->entity_id;
    $this->odoo_id = $dao->odoo_id;
    $this->action = $dao->action;
  }
  
  public function getEntity() {
    return $this->entity;
  }
  
  public function getEntityId() {
    return $this->entity_id;
  }
  
  public function process() {
    $objectList = CRM_Odoosync_Objectlist::singleton();
    
    $synchronisator = $objectList->getSynchronisatorForEntity($this->entity);
    if (!$synchronisator) {
      $this->logSyncError('No synchronisator found');
      return;
    }
    
    //set action to update if object still exists in database (e.g. it is only a soft delete)
    if ($this->action == 'DELETE' && $synchronisator->existsInCivi($this)) {
      $this->action = 'UPDATE';
    } elseif (!$synchronisator->existsInCivi($this)) {
      $this->action = 'DELETE'; //delete from Odoo because entity does not exist in civi anymore
    }
    
    //check if we shoudl sync this item
    if ($this->action != 'DELETE' && !$synchronisator->isThisItemSyncable($this)) {
      $this->status = "NOT SYNCABLE";
      $this->save();
      return;
    }
    
    if (!$this->odoo_id) {
      $odoo_id = $synchronisator->findOdooId($this);
      if ($odoo_id) {
        $this->odoo_id = $odoo_id;
      }
    } elseif ($this->action != 'DELETE' && !$synchronisator->existsInOdoo($this->odoo_id)) {
      $this->logSyncError('Entity doesn\'t exist in Odoo anymore');
      return;
    }
    
    //set action to update if we do an insert of an existing odoo entity
    if ($this->action == 'INSERT' && $this->odoo_id) {
      $this->action = 'UPDATE';
    }
    
    try {
      switch($this->action) {
        case 'INSERT':
          $this->odoo_id = $synchronisator->performInsert($this);
          $this->odoo_resource = $synchronisator->getOdooResourceType();
          $this->status = "SYNCED";
          $this->save();
          break;
        case 'UPDATE':
          $this->odoo_id = $synchronisator->performUpdate($this->odoo_id, $this);
          $this->odoo_resource = $synchronisator->getOdooResourceType();
          $this->status = "SYNCED";
          $this->save();
          break;
        case 'DELETE':
          if ($synchronisator->existsInOdoo($this->odoo_id, $this)) {
            $this->odoo_id = $synchronisator->performDelete($this->odoo_id, $this);
          }
          $this->remove();
          break;
      }
    } catch (Exception $e) {
      $this->logSyncError($e->getMessage());
      
    }
  }
  
  private function remove() {
    CRM_Core_DAO::executeQuery("DELETE FROM `civicrm_odoo_entity`  WHERE `id` = %1", array(1 => array($this->id, 'Positive')));
  }
  
  private function save() {
    $sql = "UPDATE `civicrm_odoo_entity` SET `action` = NULL, odoo_resource = %1, odoo_id = %2, `status` = %3, `sync_date` = NOW(), `last_error` = NULL, `last_error_date` = NULL WHERE `id` = %4";
    CRM_Core_DAO::executeQuery($sql, array(
      1 => array($this->odoo_resource, 'String'),
      2 => array($this->odoo_id, 'Positive'),
      3 => array($this->status, 'String'),
      4 => array($this->id, 'Positive'),
    ));
  }
  
  public static function sync($limit = 1000) {
    $sql = "SELECT * FROM `civicrm_odoo_entity`  WHERE `action` IS NOT NULL AND `last_error_date` IS NULL ORDER BY `weight` ASC, `change_date` ASC LIMIT 0, %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1=>array($limit, 'Positive')));
    while($dao->fetch()) {
      //sync this object
      $odooEntity = new CRM_Odoosync_Model_OdooEntity($dao);
      $odooEntity->process();
    }
  }
  
  private function logSyncError($error) {
    $sql_error_log = "INSERT INTO `civicrm_odoo_sync_error_log` (`entity`, `entity_id`, `odoo_id`, `date`, `action`, `error`) VALUES(%1, %2, %3, NOW(), %4, %5);";
    CRM_Core_DAO::executeQuery($sql_error_log, array(
      1 => array($this->entity, 'String'),
      2 => array($this->entity_id, 'Positive'),
      3 => array($this->odoo_id ? $this->odoo_id : '0', 'Integer'),
      4 => array($this->action, 'String'),
      5 => array($error, 'String')
    ));
    
    $sql = "UPDATE `civicrm_odoo_entity` SET `last_error`  = %1, `last_error_date` = NOW() WHERE `id`  = %2";
    CRM_Core_DAO::executeQuery($sql, array(
      1 => array($error, 'String'),
      2 => array($this->id, 'Positive')
    ));
  }
  
  /**
   * Return the stored odoo_id for an entity
   * 
   * @param String $entity
   * @param int $entity_id
   * @return int|false
   */
  public function findOdooIdByEntity($entity, $entity_id) {
    $sql = "SELECT `odoo_id`  FROM `civicrm_odoo_entity` WHERE `entity` = %1  AND `entity_id`  = %2";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
      1 => array($entity, 'String'),
      2 => array($entity_id, 'Integer'),
    ));
    
    if ($dao->fetch()) {
      return $dao->odoo_id;
    } 
    return false;
  }
}

