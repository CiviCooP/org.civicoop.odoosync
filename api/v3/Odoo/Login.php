<?php

/**
 * Odoo.Login API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_odoo_login_spec(&$spec) {
  
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
function civicrm_api3_odoo_login($params) {
    // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
  
  $connector = CRM_Odoosync_Connector::singleton();
  $returnValues = array();
  $returnValues[1]['id'] = $connector->getUserId();
  $returnValues[1]['log'] = $connector->getLog();
  
  return civicrm_api3_create_success($returnValues, $params, 'Odoo', 'Login');
  
}

