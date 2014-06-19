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
   */
  public function post($op,$objectName, $objectId, &$objectRef) {
    $saveForSync = false;
    foreach($this->list as $def) {
      if ($def->getCiviCRMEntityName == $objectName) {
        $saveForSync = true;
        break;
      }
    }
    
    if ($saveForSync) {
      //save entity for sync
      $this->saveForSync($op, $objectName, $objectId, $objectRef);
    }
  } 
  
  /**
   * Singleton pattern
   * 
   * @return CRM_Odoosync_Parameters
   */
  public static function singleton() {
    if (!self::$_instance) {
      self::$_instance = new CRM_Odoosync_Parameters();
    }
    return self::$_instance;
  }
  
  protected function saveForSync($op, $objectName, $objectId, &$objectRef) {
    //check if entity exist already exist
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_odoo_entity` WHERE `entity` = %1 AND `entity_id` = %2", array(
      1 => array($objectName, 'String'),
      2 => array($objectId, 'Positive')
    ));
    
    $action = "";
    switch($op) {
      case 'create':
        $action = "INSERT";
        break;
      case 'edit':
        $action = "UPDATE";
        break;
      case 'delete':
        $action = 'DELETE';
        break;
    }
    
    if ($dao->fetch()) {
      //do update of current item
      if ($action != 'DELETE') {
        $action = $dao->action;
      }
      $sql = "UPDATE `civicrm_odoo_entity` SET `action` = %1, `change_date` = CURDATE() WHERE `id` = %2";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($action, 'String'),
        2 => array($dao->id, 'Integeer')
      ));
    } else {
      //insert entity
      if ($action != 'DELETE') {
        $action = 'INSERT';
      }
      $sql = "INSERT INTO `civicrm_odoo_entity` (`action`, `change_date`, `entity`, `entity_id`) VALUES(%1, CURDATE(), %2, %3);";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($action, 'String'),
        2 => array($objectName, 'String'),
        3 => array($objectId, 'Positive')
      ));
    }
  }
  
  private function loadObjectlist() {
    $hooks = CRM_Utils_Hook::singleton();
    $list = $hooks->invoke(0, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, 'civicrm_odoo_object_definition');
    
    $this->list = array();
    if (is_array($list)) {
      foreach($list as $name => $definition) {
        if ($definition instanceof CRM_Odoosync_Model_ObjectDefinitionInterface) {
          $this->list[$name] = $definition;
        }
      }
    }
  }
}

