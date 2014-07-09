<?php

abstract class CRM_Odoosync_Model_ObjectSynchronisator {
  
  /**
   * Insert a civicrm entity into Odoo
   * 
   */
  abstract public function performInsert(CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  /**
   * Update an Odoo resource with civicrm data
   * 
   */
  abstract public function performUpdate($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  /**
   * Delete an item from Odoo
   * 
   */
  abstract public function performDelete($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  /**
   * Find item in Odoo and return odoo_id
   * 
   */
  abstract public function findOdooId(CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  /**
   * Check if in an entity still exists in Odoo
   * 
   */
  abstract public function existsInOdoo($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  /**
   * Checks if an entity still exists in CiviCRM.
   * 
   * This is used to check wether a civicrm entity is soft deleted or hard deleted. 
   * In the first case we have to update the entity in odoo 
   * In the second case we have to delete the entity from odoo 
   */
  abstract public function existsInCivi(CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  protected $connector;
  
  public function __construct() {
    $this->connector = CRM_Odoosync_Connector::singleton();
  }
  
  /**
   * Retruns wether this item is syncable
   * By default false. 
   * 
   * subclasses should implement this function to make items syncable
   */
  public function isThisItemSyncable(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    return false;
  }
}

