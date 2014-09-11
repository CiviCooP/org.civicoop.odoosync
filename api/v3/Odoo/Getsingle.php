<?php

/**
 * Odoo.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_odoo_getsingle_spec(&$spec) {
  $spec['resource']['api.required'] = 1;
  $spec['id']['api.required'] = 1;
}

/**
 * Odoo.Login API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_odoo_getsingle($params) {
    // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
  
  $connector = CRM_Odoosync_Connector::singleton();
  $returnValues = array();
  
  $read = $connector->read($params['resource'], $params['id']);
  
  //what to do with the return values
  $returnValues[] = $read;
  
  return civicrm_api3_create_success($returnValues, $params, 'Odoo', 'getsingle');
  
}

