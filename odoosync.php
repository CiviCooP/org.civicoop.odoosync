<?php

require_once 'odoosync.civix.php';
require_once __DIR__.'/lib/xmlrpc.inc';

/**
 * Implementation of hook_civicrm_odoo_object_definition
 * 
 */
function odoosync_civicrm_odoo_object_definition(&$list) {
  $list['civicrm_phone'] = new CRM_OdooContactSync_PhoneDefinition();
  $list['civicrm_email'] = new CRM_OdooContactSync_EmailDefinition();
  $list['civicrm_address'] = new CRM_OdooContactSync_AddressDefinition();  
  $list['civicrm_contribution'] = new CRM_OdooContributionSync_ContributionDefinition();
  $list['civicrm_contact'] = new CRM_OdooContactSync_ContactDefinition();
}


/** 
 * Implementation of hook_civicrm_post
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 */
function odoosync_civicrm_post($op,$objectName, $objectId, &$objectRef) {
  //delegate the post hook to a class
  $objects = CRM_Odoosync_Objectlist::singleton();
  $objects->post($op,$objectName, $objectId);
}

/** 
 * Place a link to the partner detail screen in Odoo on the contact card
 * 
 * Implementation of hook_civicrm_summary
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_summary
 */
function odoosync_civicrm_pageRun(&$page) {
  if ($page instanceof CRM_Contact_Page_View_Summary) {
    $partnerLink = new CRM_OdooContactSync_PartnerLink($page->getVar('_contactId'));
    if ($partnerLink->contactIsPartner()) {
      CRM_Core_Region::instance('page-body')->add(array(
        'template' => "CRM/Contact/Page/View/Summary/link_to_odoo.tpl"
      ));
      $smarty = CRM_Core_Smarty::singleton();
      $smarty->assign('link_to_odoo', $partnerLink->getLink());
    }
  }
}


/**
 * Implementation of hook_civicrm_navigationMenu
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function odoosync_civicrm_navigationMenu( &$params ) {  
  
  $item = array(
    "name" => 'odoo_admin',
    "label" => ts("Odoo (OpenERP)"),
    "permission" => "administer CiviCRM",
  );
  _odoosync_insert_navigation_menu($params, "Administer", $item);
  
  $item_settings = array (
    "name"=> 'odoo_settings',
    "label"=> ts('Settings'),
    "url"=> "civicrm/admin/odoo",
    "permission" => "administer CiviCRM",
  );
  _odoosync_insert_navigation_menu($params, "Administer/odoo_admin", $item_settings);
  $item_contribution = array (
    "name"=> 'odoo_contribution_settings',
    "label"=> ts('Contribution settings'),
    "url"=> "civicrm/admin/odoo/contribution",
    "permission" => "administer CiviCRM",
  );
  
  _odoosync_insert_navigation_menu($params, "Administer/odoo_admin", $item_contribution);
}

/**
 * Inserts a navigation menu item at a given place in the hierarchy
 *
 * $menu - menu hierarchy
 * $path - path where insertion should happen (ie. Administer/System Settings)
 * $item - menu you need to insert (parent/child attributes will be filled for you)
 * $parentId - used internally to recurse in the menu structure
 */
function _odoosync_insert_navigation_menu(&$menu, $path, $item, $parentId = NULL) {
  static $navId;

  // If we are done going down the path, insert menu
  if (empty($path)) {
    if (!$navId) $navId = CRM_Core_DAO::singleValueQuery("SELECT max(id) FROM civicrm_navigation");
    $navId ++;
    $menu[$navId] = array (
      'attributes' => array_merge($item, array(
        'name'      => CRM_Utils_Array::value('name', $item),
        'label'      => CRM_Utils_Array::value('label', $item),
        'active'     => 1,
        'parentID'   => $parentId,
        'navID'      => $navId,
      ))
    );
    return true;
  } else {
    // Find an recurse into the next level down
    $found = false;
    $path = explode('/', $path);
    $first = array_shift($path);
    foreach ($menu as $key => &$entry) {
      if ($entry['attributes']['name'] == $first) {
        if (!$entry['child']) $entry['child'] = array();
        $found = _odoosync_insert_navigation_menu($entry['child'], implode('/', $path), $item, $key);
      }
    }
    return $found;
  }
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
