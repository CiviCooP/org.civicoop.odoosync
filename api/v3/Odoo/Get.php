<?php

/**
 * Odoo.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_odoo_get_spec(&$spec) {
  $spec['resource']['api.required'] = 1;
  $spec['attribute']['api.required'] = 1;
  $spec['operator']['api.required'] = 1;
  $spec['value']['api.required'] = 1;
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
function civicrm_api3_odoo_get($params) {
    // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
  
  $connector = CRM_Odoosync_Connector::singleton();
  $returnValues = array();
  
  $type = 'string';
  if (isset($params['type'])) {
    $type = $params['type'];
  }
  
  $key = array(
    new xmlrpcval(array(
      new xmlrpcval($params['attribute'], 'string'),
      new xmlrpcval($params['operator'], 'string'),
      new xmlrpcval($params['value'], $type),
    ), "array")
  );
  
  $ids = $connector->search($params['resource'], $key);
  foreach($ids as $id_element) {
    $id = $id_element->scalarval();
    $returnValues[$id]['id'] = $id;
  }
  
  //$returnValues[1]['log'] = $connector->getLog();
  
  return civicrm_api3_create_success($returnValues, $params, 'Odoo', 'Login');
  
}

