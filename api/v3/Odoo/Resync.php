<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

/**
 * Odoo.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_odoo_resync_spec(&$spec) {
  $spec['object_name']['api.required'] = 1;
  $spec['id']['api.required'] = 1;
}

function civicrm_api3_odoo_resync($params) {
  $returnValues = array();
  $objectList = CRM_Odoosync_Objectlist::singleton();
  if (is_numeric($params['id'])) {
    $objectList->restoreSyncItem($params['object_name'], $params['id']);
  }
  return civicrm_api3_create_success($returnValues, $params, 'Odoo', 'Resync');
}