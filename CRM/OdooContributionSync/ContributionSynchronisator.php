<?php

class CRM_OdooContributionSync_ContributionSynchronisator extends CRM_Odoosync_Model_ObjectSynchronisator {
  
  protected $_contributionCache = array();
  
  public function isThisItemSyncable(CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $contribution = $this->getContribution($sync_entity->getEntityId());
    if (isset($contribution['is_test']) && $contribution['is_test']) {
      try {
        $this->performDelete($sync_entity->getOdooId(), $sync_entity);
      } catch (Exception $ex) {
        //do nothing
      }
      return false;
    }
    
    if (!empty($contribution['invoice_id']) && is_int($contribution['invoice_id']) && $this->existsInOdoo($contribution['invoice_id'])) {
      //contribution is created from within Odoo.
      //so do not sync back to Odoo
      return false; 
    }
    
    //check if this contribution is still syncable
    $settings = CRM_OdooContributionSync_Factory::getSettingsForContribution($contribution);
    if ($settings === false) {
      try {
        if ($sync_entity->getOdooId()) {  
            $this->credit($sync_entity->getOdooId(), $sync_entity);
        }
      } catch (Exception $ex) {
        //do nothing
      }     
      return false;
    }

    //check status of contribution if status is cancelled invoice isn't pushed to Odoo then this
    //contribution is not syncable
    if ($contribution['contribution_status_id'] == CRM_Core_OptionGroup::getValue('contribution_status', 'Cancelled', 'name')) {
      try {
        $this->performDelete($sync_entity->getOdooId(), $sync_entity);
      } catch (Exception $e) {
        //do nothing
      }
      return false;
    }
    
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
    
    //check if date is a valid period in Odoo
    $utils = CRM_OdooContributionSync_Utils::singleton();  
    $date = new DateTime($contribution['receive_date']);
    if (!$utils->isBookYearAvailable($date->format('Y'))) {
        throw new Exception('Bookyear: '.$date->format('Y').' doesn\'t exist in Odoo or is closed');
    }
    
    $invoice_id = $this->createInvoice($contribution, $sync_entity);
    if ($invoice_id) {      
      //invoice has the state open and confirmed
      return $invoice_id;
    }
    throw new exception('Could not create invoice');
  }
  
  /**
   * Update an existing contact in Odoo
   * 
   * @param type $odoo_id
   * @param CRM_Odoosync_Model_OdooEntity $sync_entit
   */
  public function performUpdate($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {    
    $contribution = $this->getContribution($sync_entity->getEntityId());
    
    //check if date is a valid period in Odoo
    $utils = CRM_OdooContributionSync_Utils::singleton();  
    $date = new DateTime($contribution['receive_date']);
    if (!$utils->isBookYearAvailable($date->format('Y'))) {
        throw new Exception('Bookyear: '.$date->format('Y').' doesn\'t exist in Odoo or is closed');
    }
    
    $invoice_id = $this->createInvoice($contribution, $sync_entity);
    if ($invoice_id) {
      //credit previous invoice
      $this->performDelete($odoo_id, $sync_entity);
      $sync_entity->setOdooField('');
      return $invoice_id;
    }
    throw new exception('Could not update invoice');
  }
  
  public function getSyncData(CRM_Odoosync_Model_OdooEntity $sync_entity, $odoo_id) {
    $contribution = $this->getContribution($sync_entity->getEntityId());
    $partner_id = $sync_entity->findOdooIdByEntity('civicrm_contact', $contribution['contact_id']);
    $parameters = $this->getOdooParameters($contribution, $partner_id, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'create');
    $parameters['lines'] = new xmlrpcval(array(
      $this->getInvoiceLineParameters($contribution, $odoo_id, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'create')
    ), 'array');
    return $parameters;
  }
  
  protected function createInvoice($contribution, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    $partner_id = $sync_entity->findOdooIdByEntity('civicrm_contact', $contribution['contact_id']);
    $parameters = $this->getOdooParameters($contribution, $partner_id, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'create');
    $invoice_id = $this->connector->create($this->getOdooResourceType(), $parameters);
    if ($invoice_id) {
      $odoo_line_id = false;
      try {
        $odoo_line_id = $this->addInvoiceLine($contribution, $invoice_id, $sync_entity->getEntity(), $sync_entity->getEntityId(), 'create');
      } catch (Exception $e) {
        //remove the invoice because we could not add the invoice line to the invoice
        $this->connector->unlink($this->getOdooResourceType(), $invoice_id);
        throw new exception('Could not create invoice line: '.$e->getMessage());
      }
      if ($odoo_line_id === false) {
        //remove the invoice because we could not add the invoice line to the invoice
        $this->connector->unlink($this->getOdooResourceType(), $invoice_id);
        throw new exception('Could not create invoice line');
      }
      
      //confirm invoice and set sate to open
      if ($this->confirmThisInvoice($contribution)) {
        $this->connector->exec_workflow($this->getOdooResourceType(), 'invoice_open', $invoice_id);
      }
      
      return $invoice_id;
    }
    throw new Exception('Could not create invoice');
  }
  
  /**
   * Delete contribution from Odoo by creating a refund
   * 
   * @param type $odoo_id
   * @param CRM_Odoosync_Model_OdooEntity $sync_entity
   */
  public function performDelete($odoo_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    if ($odoo_id) {
      $deletable = $this->isInvoiceDeletable($odoo_id);
      if ($deletable) {
        $this->connector->unlink($this->getOdooResourceType(), $odoo_id);
      } else {      
        $this->credit($odoo_id, $sync_entity);
      }
    }
  }
  
  protected function isInvoiceDeletable($odoo_invoice_id) {
    $invoice = $this->connector->read('account.invoice', $odoo_invoice_id);
    if (isset($invoice['state']) && $invoice['state']->scalarval() == 'draft') {
      return true;
    }     
    return false;
  }
  
  /**
   * Create a credit invoice for an existing invoice in Odoo
   * 
   * @param type $odoo_id
   */
  protected function credit($invoice_id, CRM_Odoosync_Model_OdooEntity $sync_entity) {
    if (!$invoice_id) {
      return false;
    }
    if ($sync_entity->getOdooField() != 'refunded') {
      $credit = new CRM_OdooContributionSync_CreditInvoice();
      $result = $credit->credit($invoice_id, $sync_entity->getChangeDate());
      if ($result) {
        $sync_entity->setOdooField('refunded');
      }
      return $result;
    }
    return true;
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
    $utils = CRM_OdooContributionSync_Utils::singleton();
    $settings = CRM_OdooContributionSync_Factory::getSettingsForContribution($contribution);
    
    $parameters = array();
    $parameters['journal_id'] = new xmlrpcval($settings->getJournalId(), 'int');
    $parameters['account_id'] = new xmlrpcval($settings->getAccountId(), 'int');
    $parameters['partner_id'] = new xmlrpcval($partner_id, 'int');
    $parameters['reference'] = new xmlrpcval($settings->getReference(), 'string');
    $parameters['company_id'] = new xmlrpcval($settings->getCompanyId(), 'int');
    $parameters['name'] = new xmlrpcval($settings->getClientReference(), 'string');
    
    //set currency
    if (isset($contribution['currency'])) {
      $currency_id = $utils->getOdooCurrencyIdByCode($contribution['currency']);
      $parameters['currency_id'] = new xmlrpcval($currency_id, 'int');
    }
    
    //set date
    $contrDate = new DateTime($contribution['receive_date']);
    $parameters['date_invoice'] = new xmlrpcval($contrDate->format('Y-m-d') ,'string');
    
    $this->alterOdooParameters($parameters, $this->getOdooResourceType(), $entity, $entity_id, $action);
    
    return $parameters;
  }
  
  protected function addInvoiceLine($contribution, $invoice_id, $entity, $entity_id, $action) {
    $resource = 'account.invoice.line';
    $line = $this->getInvoiceLineParameters($contribution, $invoice_id, $entity, $entity_id, $action);
    
    $odoo_id = $this->connector->create($resource, $line);
    if ($odoo_id) {
      return $odoo_id;
    }
    return false;
  }
  
  protected function getInvoiceLineParameters($contribution, $invoice_id, $entity, $entity_id, $action) {
    $resource = 'account.invoice.line';
    $utils = CRM_OdooContributionSync_Utils_Product::singleton();
    $settings = CRM_OdooContributionSync_Factory::getSettingsForContribution($contribution);

    $line = array();
    $line['quantity'] = new xmlrpcval(1, 'int');
    
    //Create a many2many for the tax option
    //(6, 0, [IDs])          replace the list of linked IDs 
    //                      (like using (5) then (4,ID) 
    //                      for each ID in the list of IDs)
    // 
    //See also https://doc.odoo.com/v6.0/developer/2_5_Objects_Fields_Methods/methods.html/#osv.osv.osv.write
    $tax = array(new xmlrpcval(array(
            new xmlrpcval(6, "int"),// 6 : id link
            new xmlrpcval(0, "int"), 
            new xmlrpcval($this->getProductTaxes($settings->getProductId()),"array")
            ),
        "array" ));
    
    $product = $utils->getProductFromOdoo($settings->getProductId());

    $income_account_id = false;
    if ($product['property_account_income']->scalarval()) {
      $income_account_id = reset($product['property_account_income']->scalarval());
    }
    if (!$income_account_id) {
      throw new Exception('Product Income account is not set in Odoo');
    }

    $line['invoice_line_tax_id'] = new xmlrpcval($tax, 'array');
    $line['name'] = new xmlrpcval($settings->getReference(), 'string');
    $line['price_unit'] = new xmlrpcval($contribution['total_amount'], 'double');
    $line['account_id'] = new xmlrpcval($income_account_id->scalarval(), 'int');
    $line['product_id'] = new xmlrpcval($settings->getProductId(), 'int'); //do we need product id?
    $line['invoice_id'] = new xmlrpcval($invoice_id, 'int');
    
    
    $this->alterOdooParameters($line, $resource, $entity, $entity_id, $action);
    
    return $line;
  }
  
  protected function getProductTaxes($product_id) {
    //$product = $this->connector->read('product.product', $product_id);
    $utils = CRM_OdooContributionSync_Utils_Product::singleton();
    $product = $utils->getProductFromOdoo($product_id);
    $taxes = array();
    foreach ($product['taxes_id']->scalarval() as $tax_id_res) {
      $taxes[] = new xmlrpcval($tax_id_res->scalarval(), 'int');
    }
    return $taxes;
  }
 
  protected function getContribution($entity_id) {
    if (!isset($this->_contributionCache[$entity_id])) {
      $this->_contributionCache[$entity_id] = civicrm_api3('Contribution', 'getsingle', array('id' => $entity_id));
    }
    
    return $this->_contributionCache[$entity_id];
  }
  
  /**
   * Returns whether a new invoice (contribution) 
   * should be saved as draft (false) or as confirmed (true)
   * 
   * Inheritted classes can override this method to determine 
   * 
   * @return boolean
   */
  protected function confirmThisInvoice($contribution) {
    $settings = CRM_OdooContributionSync_Factory::getSettingsForContribution($contribution);
    if ($settings->getConfirmed()) {
      return true;
    }
    return false;
  }
  
}


