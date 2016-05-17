<?php

/* 
 * Class holding parameter settings
 * such as the list of entities to sync
 */

class CRM_Odoosync_Objectlist {
  
  protected static $_instance;
  
  protected $list;
  
  protected $processedEntities = array();
  
  protected function __construct() {
    $this->loadObjectlist();
  }
  
  /**
   * Delegation of the post hook to resync objects
   * 
   * Check if the object should be synced to Odoo!
   * We do this by defining a list of objects which could be synced
   * if it could be synced we save the object in the list
   * to be synced later on by a background job
   * 
   */
  public function post($op,$objectName, $objectId) {
    foreach($this->list as $def) {
      if ($def->isObjectNameSupported($objectName)) {
        $data = array();
        try {
          $data = $def->getCiviCRMEntityDataById($objectId);
        } catch (Exception $e) { 
          //do nothing
        }
        $this->saveForSync($op, $objectId, $data, $def);
        break;
      }
    } 
  }

  /**
   * Schedules an item to be synched again.
   *
   * This is usefull when a previous sync
   * failed. Or when an item was not syncable but is now
   *
   */
  public function restoreSyncItem($objectName, $objectId) {
    foreach($this->list as $def) {
      if ($def->isObjectNameSupported($objectName)) {
        $data = array();
        try {
          $data = $def->getCiviCRMEntityDataById($objectId);
        } catch (Exception $e) {
          //do nothing
        }
        $this->resaveForSync($objectId, $data, $def);
        break;
      }
    }
  }
  
  public function complementSyncQueue($limit=1000) {
    $itemsToSync = $limit;
    foreach($this->list as $def) {
      if ($itemsToSync <= 0) {
        break;
      }
      
      $table = $def->getTableName();
      $id = $def->getIdFieldName();
      $entity = $def->getCiviCRMEntityName();
      $sql = "SELECT `".$table."`.`".$id."` AS `id` FROM `".$table."`
              WHERE `".$table."`.`".$id."` NOT IN (
                SELECT `civicrm_odoo_entity`.`entity_id` FROM `civicrm_odoo_entity` 
                WHERE `civicrm_odoo_entity`.`entity` = '".$entity."')
              LIMIT ".$itemsToSync;
      $dao = CRM_Core_DAO::executeQuery($sql);
      while($dao->fetch()) {
        $data = array();
        try {
          $data = $def->getCiviCRMEntityDataById($dao->id);
        } catch (Exception $e) { 
          //do nothing
        }
        $this->saveForSync('create', $dao->id, $data, $def);
        
        $itemsToSync --;
      }
    }
  }
  
  /**
   * Singleton pattern
   * 
   * @return CRM_Odoosync_Objectlist
   */
  public static function singleton() {
    if (!self::$_instance) {
      self::$_instance = new CRM_Odoosync_Objectlist();
    }
    return self::$_instance;
  }
  
  protected function resetProcessedEntityList() {
    $this->processedEntities = array();
  }
  
  protected function setProcessedEntity($entity, $entity_id) {
    $this->processedEntities[$entity][$entity_id] = true;
  }
  
  protected function isEntityProcessed($entity, $entity_id) {
    if (isset($this->processedEntities[$entity]) && isset($this->processedEntities[$entity][$entity_id]) && $this->processedEntities[$entity][$entity_id]) {
      return true;
    }
    return false;
  }

  protected function resaveForSync($objectId, $data, CRM_Odoosync_Model_ObjectDefinitionInterface $objectDef) {
    $this->resetProcessedEntityList();

    //check if entity exist already exist
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_odoo_entity` WHERE `entity` = %1 AND `entity_id` = %2", array(
      1 => array($objectDef->getCiviCRMEntityName(), 'String'),
      2 => array($objectId, 'Positive')
    ));

    $action = "UPDATE";

    if ($dao->fetch()) {
      if ($dao->status == 'SYNCED') {
        return; //do not resync items which are in sync state
      }

      //do update of current item
      if ((!empty($dao->action) && $dao->action == 'INSERT') || $dao->odoo_id <= 0) {
        $action = 'INSERT';
      }
      $sql = "UPDATE `civicrm_odoo_entity` SET `action` = %1, `weight` = %2, `change_date` = NOW(), `status` = 'OUT OF SYNC' WHERE `id` = %3";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($action, 'String'),
        2 => array($objectDef->getWeight($action), 'Integer'),
        3 => array($dao->id, 'Integer')
      ));
    } else {
      //insert entity
      if ($action != 'DELETE') {
        $action = 'INSERT';
      }
      $sql = "INSERT INTO `civicrm_odoo_entity` (`action`, `change_date`, `entity`, `entity_id`, `weight`, `status`) VALUES(%1, NOW(), %2, %3, %4, 'OUT OF SYNC');";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($action, 'String'),
        2 => array($objectDef->getCiviCRMEntityName(), 'String'),
        3 => array($objectId, 'Positive'),
        4 => array($objectDef->getWeight($action), 'Integer'),
      ));
    }

    $this->setProcessedEntity($objectDef->getCiviCRMEntityName(), $objectId);

    $this->saveAllDependencies($objectDef, $objectId, $action, $data);
  }
  
  protected function saveForSync($op, $objectId, $data, CRM_Odoosync_Model_ObjectDefinitionInterface $objectDef) {
    $this->resetProcessedEntityList();
    
    //check if entity exist already exist
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_odoo_entity` WHERE `entity` = %1 AND `entity_id` = %2", array(
      1 => array($objectDef->getCiviCRMEntityName(), 'String'),
      2 => array($objectId, 'Positive')
    ));
    
    $action = "";
    switch($op) {
      case 'create':
        $action = "INSERT";
        break;
      case 'edit':
      case 'trash':
      case 'restore':
        $action = "UPDATE";
        break;
      case 'delete':        
        $action = 'DELETE';
        break;
    }
    
    if ($dao->fetch()) {
      //do update of current item
      if (!empty($dao->action) && $dao->action == 'INSERT') {
        $action = 'INSERT';
      }
      $sql = "UPDATE `civicrm_odoo_entity` SET `action` = %1, `weight` = %2, `change_date` = NOW(), `status` = 'OUT OF SYNC' WHERE `id` = %3";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($action, 'String'),
        2 => array($objectDef->getWeight($action), 'Integer'),
        3 => array($dao->id, 'Integer')
      ));
    } else {
      //insert entity
      if ($action != 'DELETE') {
        $action = 'INSERT';
      }
      $sql = "INSERT INTO `civicrm_odoo_entity` (`action`, `change_date`, `entity`, `entity_id`, `weight`, `status`) VALUES(%1, NOW(), %2, %3, %4, 'OUT OF SYNC');";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($action, 'String'),
        2 => array($objectDef->getCiviCRMEntityName(), 'String'),
        3 => array($objectId, 'Positive'),
        4 => array($objectDef->getWeight($action), 'Integer'),
      ));
    }
    
    $this->setProcessedEntity($objectDef->getCiviCRMEntityName(), $objectId);
    
    $this->saveAllDependencies($objectDef, $objectId, $action, $data);
  }
  
  private function saveAllDependencies(CRM_Odoosync_Model_ObjectDefinitionInterface $objectDef, $entity_id, $action, $data = false) {
    $deps = array();
    if ($objectDef instanceof CRM_Odoosync_Model_ObjectDependencyInterface) {
      //definition has dependencies check those and save them into the sync queue      
      $deps = $objectDef->getSyncDependenciesForEntity($entity_id, $data);
    }
    
    $hooks = CRM_Odoosync_Utils_HookInvoker::singleton();
    $hooks->hook_civicrm_odoo_object_definition_dependency($deps, $objectDef, $entity_id, $action, $data);
    
    foreach($deps as $dep) {        
      $this->saveDependency($dep, $objectDef->getWeight($action) + $dep->getWeightOffset());
    }
  }
  
  private function saveDependency(CRM_Odoosync_Model_Dependency $dep, $weight) {
    $objectDef = $this->getDefinitionForEntity($dep->getEntity());

    if ($objectDef === false) {
      return;
    }
    
    if ($this->isEntityProcessed($dep->getEntity(), $dep->getEntityId())) {
      return;
    }

    //check if entity exist already exist
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_odoo_entity` WHERE `entity` = %1 AND `entity_id` = %2", array(
      1 => array($dep->getEntity(), 'String'),
      2 => array($dep->getEntityId(), 'Positive')
    ));
    
    if ($dao->fetch()) {
      //entity exist
      if ($dep->isQueuedForUpdate() || $dao->odoo_id <= 0) {
        $action = "UPDATE";
        if ($dao->action == "INSERT" || $dao->odoo_id <= 0) {
          $action = "INSERT";
        }
        
        $weightToUse = ($objectDef->getWeight($action) < $weight) ? $objectDef->getWeight($action) : $weight;
        
        $sql = "UPDATE `civicrm_odoo_entity` SET `weight` = %1, `action` = %2, `change_date` = NOW(), `status` = 'OUT OF SYNC' WHERE `id` = %3";
        CRM_Core_DAO::executeQuery($sql, array(
          1 => array($weightToUse, 'Integer'),
          2 => array($action, 'String'),
          3 => array($dao->id, 'Integer')
        ));
      } else {
        $action = $dao->action;
        $weightToUse = ($objectDef->getWeight($dao->action) < $weight) ? $objectDef->getWeight($dao->action) : $weight;
        $sql = "UPDATE `civicrm_odoo_entity` SET `weight` = %1 WHERE `id` = %2";
        CRM_Core_DAO::executeQuery($sql, array(
          1 => array($weightToUse, 'Integer'),
          2 => array($dao->id, 'Integer')
        ));
      }     
    } else {
      //entity does not exist yet
      $action = 'INSERT';
      $weightToUse = ($objectDef->getWeight($action) < $weight) ? $objectDef->getWeight($action) : $weight;
      
      $sql = "INSERT INTO `civicrm_odoo_entity` (`action`, `change_date`, `entity`, `entity_id`, `weight`, `status`) VALUES(%1, NOW(), %2, %3, %4, 'OUT OF SYNC');";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($action, 'String'),
        2 => array($dep->getEntity(), 'String'),
        3 => array($dep->getEntityId(), 'Positive'),
        4 => array($weightToUse, 'Integer'),
      ));
    }
    
    $this->setProcessedEntity($dep->getEntity(), $dep->getEntityId());
    
    $this->saveAllDependencies($objectDef, $dep->getEntityId(), $weightToUse - 1, $action);
  }
  
  private function loadObjectlist() {
    $list = array();
    $hooks = CRM_Odoosync_Utils_HookInvoker::singleton();
    $hooks->hook_civicrm_odoo_object_definition($list);
    
    $this->list = array();
    if (is_array($list)) {
      $this->list = $list;
    }
  }
  
  public function getSynchronisatorForEntity($entity) {
    foreach($this->list as $def) {
      if ($def->getCiviCRMEntityName() == $entity) {
        return $def->getSynchronisator();
      }
    }
    return false;
  }
  
  public function getDefinitionForEntity($entity) {
    foreach($this->list as $def) {
      if ($def->getCiviCRMEntityName() == $entity) {
        return $def;
      }
    }
    return false;
  }
}

