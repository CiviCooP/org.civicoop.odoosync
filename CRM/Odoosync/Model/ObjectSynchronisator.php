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
   * Returns an array with data to sync
   * 
   * @return array;
   */
  abstract public function getSyncData(CRM_Odoosync_Model_OdooEntity $sync_entity, $odoo_id);
  
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
   * Checks if an entity still exists in CiviCRM.
   * 
   * This is used to check wether a civicrm entity is soft deleted or hard deleted. 
   * In the first case we have to update the entity in odoo 
   * In the second case we have to delete the entity from odoo 
   */
  abstract public function existsInCivi(CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  /**
   * Returns the name of the Odoo resource e.g. res.partner
   * 
   * @return string
   */
  abstract public function getOdooResourceType();
  
  /**
   *
   * @var CRM_Odoosync_Connector 
   */
  protected $connector;
  
  /**
   * 
   * @var CRM_Odoosync_Model_ObjectDefinitionInterface
   */
  protected $objectDefinition;
  
  public function __construct(CRM_Odoosync_Model_ObjectDefinitionInterface $objectDefinition) {
    $this->objectDefinition = $objectDefinition;
    $this->connector = CRM_Odoosync_Connector::singleton();
  }
  
  /**
   * Check if in an entity still exists in Odoo
   * 
   * @param int $odoo_id
   * @return boolean
   */
  public function existsInOdoo($odoo_id) {
    if ($this->connector->read($this->getOdooResourceType(), $odoo_id)) {
      return true;
    }
    return false;
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
  
  /**
   * Invokes a hook to extend/change the Odoo parameters
   * 
   * This could be useful to tweak for specific implementations 
   * e.g. at one client all partners in Odoo where companies, even if they are actual persons
   * 
   * @param type $parameters
   * @param type $entity
   * @param type $entity_id
   * @param String $action
   * 
   */
  public function alterOdooParameters(&$parameters, $resource, $entity, $entity_id, $action) {
    try {
      CRM_Utils_Hook::singleton()->invoke(5, $parameters, $resource, $entity, $entity_id, $action, 'civicrm_odoo_alter_parameters');
    } Catch (Exception $ex) {
      //do nothing
    }
  }
}

