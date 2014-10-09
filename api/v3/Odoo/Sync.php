<?php

/**
 * Odoo.Sync API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_odoo_sync_spec(&$spec) {
  
}

/**
 * Odoo.Sync API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_odoo_sync($params) {
  $returnValues = array();
  
  if (!empty($params['unlock'])) {
    CRM_Odoosync_Model_OdooEntity::unlock();
  } elseif (!empty($params['getlock'])) {
    $lock = CRM_Odoosync_Model_OdooEntity::getLockTime();
    $returnValues['lock'] = $lock;
    if ($lock) {
      $lockDateTime = new DateTime();
      $lockDateTime->setTimestamp($lock);
      $returnValues['lock_datetime'] = $lockDateTime->format('d-m-Y H:i:s');
    }
  } else {

    $limit = isset($params['limit']) ? $params['limit'] : 1000;
    $debug = isset($params['debug']) ? true : false;
    CRM_Odoosync_Model_OdooEntity::sync($limit, $debug);
  }
  
  // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'Odoo', 'Sync');
}
