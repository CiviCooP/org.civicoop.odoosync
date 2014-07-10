<?php

/* 
 * Class holding parameter settings
 * such as the list of entities to sync
 */

class CRM_Odoosync_Objectlist {
  
  protected static $_instance;
  
  protected $list;
  
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
  public function post($op,$objectName, $objectId, &$objectRef) {
    foreach($this->list as $def) {
      if ($def->isObjectNameSupported($objectName)) {
        $this->saveForSync($op, $objectName, $objectId, $objectRef, $def);
        break;
      }
    }
  }
  
  /**
   * Singleton pattern
   * 
   * @return CRM_Odoosync_Parameters
   */
  public static function singleton() {
    if (!self::$_instance) {
      self::$_instance = new CRM_Odoosync_Objectlist();
    }
    return self::$_instance;
  }
  
  protected function saveForSync($op, $objectName, $objectId, &$objectRef, CRM_Odoosync_Model_ObjectDefinitionInterface $objectDef) {
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
        2 => array($objectDef->getWeight(), 'Integer'),
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
        4 => array($objectDef->getWeight(), 'Integer'),
      ));
    }
    
    $this->saveAllDependencies($objectDef, $objectId);
  }
  
  private function saveAllDependencies(CRM_Odoosync_Model_ObjectDefinitionInterface $objectDef, $entity_id) {
    if ($objectDef instanceof CRM_Odoosync_Model_ObjectDependencyInterface) {
      //definition has dependencies check those and save them into the sync queue
      foreach($objectDef->getSyncDependenciesForEntity($entity_id) as $dep) {
        $this->saveDependency($dep);
      }
    }
  }
  
  private function saveDependency(CRM_Odoosync_Model_Dependency $dep) {
    $objectDef = $this->getDefinitionForEntity($dep->getEntity());
    
    if ($objectDef === false) {
      return;
    }
    
    //check if entity exist already exist
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_odoo_entity` WHERE `entity` = %1 AND `entity_id` = %2", array(
      1 => array($dep->getEntity(), 'String'),
      2 => array($dep->getEntityId(), 'Positive')
    ));
    
    if (!$dao->fetch()) {
      //entity does not exist yet
      $action = 'INSERT';
      $sql = "INSERT INTO `civicrm_odoo_entity` (`action`, `change_date`, `entity`, `entity_id`, `weight`, `status`) VALUES(%1, NOW(), %2, %3, %4, 'OUT OF SYNC');";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($action, 'String'),
        2 => array($dep->getEntity(), 'String'),
        3 => array($dep->getEntityId(), 'Positive'),
        4 => array($objectDef->getWeight(), 'Integer'),
      ));
    }
    
    $this->saveAllDependencies($objectDef, $dep->getEntityId());
  }
  
  private function loadObjectlist() {
    $hooks = CRM_Utils_Hook::singleton();
    $list = $hooks->invoke(0, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, 'civicrm_odoo_object_definition');
    
    $this->list = array();
    if (is_array($list)) {
      foreach($list as $definition) {
        if ($definition instanceof CRM_Odoosync_Model_ObjectDefinitionInterface) {
          $this->list[$definition->getName()] = $definition;
        }
      }
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

