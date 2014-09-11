<?php

/**
 * Find odoo partner by their label
 */

class CRM_OdooContactSync_Helper_FindLabel {
  
  /**
   * Find a label, returns the label id
   * 
   * Returns false if label id is not found
   * 
   * @param type $label
   * @param type $parent_id
   * @return boolean
   */
  public static function findLabel($label, $parent_id=false) {
    $connector = CRM_Odoosync_Connector::singleton();
    
    $key = array(
    new xmlrpcval(array(
      new xmlrpcval('name', 'string'),
      new xmlrpcval('=', 'string'),
      new xmlrpcval($label, 'string'),
    ), "array"));
    
    if ($parent_id) {
      $key[] = new xmlrpcval(array(
        new xmlrpcval('parent_id', 'string'),
        new xmlrpcval('=', 'string'),
        new xmlrpcval($parent_id, 'int'),
      ), "array");
    }

    $result = $connector->search('res.partner.category', $key);
    
    foreach($result as $id_element) {
      return $id_element->scalarval();
    }
    
    return false;
  }
  
}

