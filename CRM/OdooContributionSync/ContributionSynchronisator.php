<?php

class CRM_OdooContributionSync_ContributionSynchronisator extends CRM_Odoosync_Model_ObjectSynchronisator {
  
  protected $_contributionCache = array();
  
  public function isThisItemSyncable(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    return true;
  }
  
  public function existsInCivi(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    try {
      $contribution = civicrm_api3('Contribution', 'getsingle', array('id' => $sync_entity->getEntityId()));
    } catch (CiviCRM_API3_Exception $ex) {
      return false;
    }
    return true;
  }
  
  
  /**
   * Insert a new Contact into Odoo
   * 
   * @param CRM_Odoosync_Model_OdooEntity $sync_entity
   * @return type
   * @throws Exception
   */
  public function performInsert(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $contribution = $this->getContribution($sync_entity->getEntityId());
    $partner_id = $sync_entity->findOdooIdByEntity('civicrm_contact', $contribution['contact_id']);
    $parameters = $this->getOdooParameters($contribution, $partner_id, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'create');
    
    throw new exception('to be implemented');
  }
  
  /**
   * Update an existing contact in Odoo
   * 
   * @param type $odoo_id
   * @param CRM_Odoosync_Model_OdooEntity $sync_entit
   */
  public function performUpdate($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    throw new Exception('To be implemented');
  }
  
  /**
   * Delete contact from Odoo
   * 
   * @param type $odoo_id
   * @param CRM_Odoosync_Model_OdooEntity $sync_entity
   */
  public function performDelete($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    throw new Exception('To be implemented');
  }
  
  /**
   * Find the odoo id of this resource
   * 
   * @param CRM_Odoosync_Model_OdooEntity $sync_entity
   * @return boolean
   */
  public function findOdooId(CRM_Odoosync_Model_OdooEntity $sync_entity) {    
    return false;
  }
  
  /**
   * Returns the name of the Odoo resource e.g. res.partner
   * 
   * @return string
   */
  public function getOdooResourceType() {
    return 'account.invoice';
  }
  
  /**
   * Returns the parameters to update/insert an Odoo object
   * 
   * @param type $contact
   * @return \xmlrpcval
   */
  protected function getOdooParameters($contribution, $partner_id, $entity, $entity_id, $action) {
    $settings = CRM_OdooContributionSync_Factory::getSettingsForContribution($contribution);
    $parameters = array();
    $parameters['journal_id'] = new xmlrpcval($settings->getJournalId(), 'int');
    $parameters['partner_id'] = new xmlrpcval($partner_id, 'int');
    $parameters['partner_id'] = new xmlrpcval($settings->getReference(), 'string');
    $parameters['company_id'] = new xmlrpcval($settings->getCompanyId(), 'int');
    $contrDate = new DateTime($contribution['receive_date']);
    $parameters['date_invoice'] = new xmlrpcval($contrDate->format('Y-m-d') ,'dateTime.iso8601');
    
    //add the invoice lines
    $invoice_lines = array();
    $parameters['invoice_lines'] = new xmlrpcval($invoice_lines, $invoice_lines);
    
    $this->alterOdooParameters($parameters, $entity, $entity_id, $action);
    
    return $parameters;
  }
 
  protected function getContribution($entity_id) {
    if (!isset($this->_contributionCache[$entity_id])) {
      $this->_contributionCache[$entity_id] = civicrm_api3('Contribution', 'getsingle', array('id' => $entity_id));
    }
    
    return $this->_contributionCache[$entity_id];
  }
  
}


