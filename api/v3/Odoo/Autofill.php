<?php

/**
 * Odoo.Autofill API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_odoo_autofill($params) {
  $returnValues = array();
  
  $limit = isset($params['limit']) ? $params['limit'] : 1000;
  $objectList = CRM_Odoosync_Objectlist::singleton();
  $objectList->complementSyncQueue($limit);
  
  
  //$returnValues[1]['log'] = $connector->getLog();
  
  return civicrm_api3_create_success($returnValues, $params, 'Odoo', 'Autofill');
  
}

