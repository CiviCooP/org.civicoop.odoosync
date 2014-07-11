<?php

require_once 'odoosync.civix.php';
require_once __DIR__.'/lib/xmlrpc.inc';

/**
 * Implementation of hook_civicrm_odoo_object_definition
 * 
 */
function odoosync_civicrm_odoo_object_definition(&$list) {
  $list['civicrm_contact'] = new CRM_OdooContactSync_ContactDefinition();
  $list['civicrm_address'] = new CRM_OdooContactSync_AddressDefinition();
  $list['civicrm_email'] = new CRM_OdooContactSync_EmailDefinition();
  $list['civicrm_phone'] = new CRM_OdooContactSync_PhoneDefinition();
}


/** 
 * Implementation of hook_civicrm_post
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 */
function odoosync_civicrm_post($op,$objectName, $objectId, &$objectRef) {
  //delegate the post hook to a class
  $objects = CRM_Odoosync_Objectlist::singleton();
  $objects->post($op,$objectName, $objectId, $objectRef);
}

/**
 * Implementation of hook_civicrm_navigationMenu
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function odoosync_civicrm_navigationMenu( &$params ) {  
  $item = array (
    "name"=> ts('Odoo (OpenERP)'),
    "url"=> "civicrm/admin/odoo",
    "permission" => "administer CiviCRM",
  );
  _odoosync_civix_insert_navigation_menu($params, "Administer/System Settings", $item);
}

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function odoosync_civicrm_config(&$config) {
  _odoosync_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function odoosync_civicrm_xmlMenu(&$files) {
  _odoosync_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function odoosync_civicrm_install() {
  return _odoosync_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function odoosync_civicrm_uninstall() {
  return _odoosync_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function odoosync_civicrm_enable() {
  return _odoosync_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function odoosync_civicrm_disable() {
  return _odoosync_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function odoosync_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _odoosync_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function odoosync_civicrm_managed(&$entities) {
  return _odoosync_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function odoosync_civicrm_caseTypes(&$caseTypes) {
  _odoosync_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function odoosync_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _odoosync_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
