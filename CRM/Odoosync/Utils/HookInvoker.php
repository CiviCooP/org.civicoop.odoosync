<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_Odoosync_Utils_HookInvoker {

  private static $singleton;

  private function __construct() {

  }

  /**
   * @return \CRM_Odoosync_Utils_HookInvoker
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Odoosync_Utils_HookInvoker();
    }
    return self::$singleton;
  }

  /**
   * This hook is useful to alter parameters before updating or inserting an object into Odoo.
   *
   * E.g. when a specific implementation of Odoo has specific fields which are also available in CivICRM.
   *
   * @param $parameters
   *   The parameter array can be altered in this hook.
   * @param $resource
   * @param $entity
   * @param $entity_id
   * @param $action
   * @return void
   */
  public function hook_civicrm_odoo_alter_parameters(&$parameters, $resource, $entity, $entity_id, $action) {
    return $this->invoke('civicrm_odoo_alter_parameters', 5, $parameters, $resource, $entity, $entity_id, $action);
  }

  /**
   * This hook is useful to define custom synchronisator classes for certain entities.
   *
   * E.g. at one client they have implemented Odoo in such away that they can
   * store multiple addresses so rather then syncing only the primary addresses
   * this hook returns a synchronisator class which could also sync non-primary addresses.
   *
   * @param $objectDefinition
   * @param $synchronisator
   *   The name of the synchronisator class. This can be altered.
   * @return void
   */
  public function hook_civicrm_odoo_synchronisator($objectDefinition, &$synchronisator) {
    return $this->invoke('civicrm_odoo_synchronisator', 2, $objectDefinition, $synchronisator);
  }

  /**
   * This hook returns a list with object dependencies returns an array of CRM_Odoosync_Model_Dependency.
   *
   * @param $deps
   * @param $objectDef
   * @param $entity_id
   * @param $action
   * @param $data
   * @return array
   *   Returns an array of CRM_Odoosync_Model_Dependency.
   */
  public function hook_civicrm_odoo_object_definition_dependency(&$deps, $objectDef, $entity_id, $action, $data) {
    return $this->invoke('civicrm_odoo_object_definition_dependency', 5, $deps, $objectDef, $entity_id, $action, $data);
  }

  /**
   * This hook returns a list with object definitions.
   *
   * A object definition is a definition of a civicrm entity and
   * how this could be synced with Odoo.
   *
   * @param $list
   * @return array Returns an array with classes which implement the CRM_Odoosync_Model_ObjectDefinitionInterface
   */
  public function hook_civicrm_odoo_object_definition(&$list) {
    return $this->invoke('civicrm_odoo_object_definition', 1, $list);
  }
  
  private function invoke($fnSuffix, $numParams, &$arg1 = null, &$arg2 = null, &$arg3 = null, &$arg4 = null, &$arg5 = null) {
    $hook =  CRM_Utils_Hook::singleton();
    $civiVersion = CRM_Core_BAO_Domain::version();

    if (version_compare($civiVersion, '4.5', '<')) {
      //in CiviCRM 4.4 the invoke function has 5 arguments maximum
      return $hook->invoke($numParams, $arg1, $arg2, $arg3, $arg4, $arg5, $fnSuffix);
    } else {
      //in CiviCRM 4.5 and later the invoke function has 6 arguments
      return $hook->invoke($numParams, $arg1, $arg2, $arg3, $arg4, $arg5, CRM_Utils_Hook::$_nullObject, $fnSuffix);
    }
  }

}