<?php

interface CRM_Odoosync_Model_ObjectSynchronisationInterface {
  
  public function performInsert(CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  public function performUpdate($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entit);
  
  public function performDelete($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  public function findOdooId(CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  public function existsInOdoo($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity);
  
  
}
