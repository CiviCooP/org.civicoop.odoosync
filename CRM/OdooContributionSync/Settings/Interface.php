<?php

/**
 * This interface holds functions to define values for 
 * making invoices into Odoo
 * 
 */
interface CRM_OdooContributionSync_Settings_Interface {
  
  /**
   * Returns the Journal ID
   * 
   * @return int
   */
  public function getJournalId();
  
  /**
   * Returns a string which is stored 
   * in the reference field of an Odoo Invoice line
   * 
   * @return string
   */
  public function getReference();
  
  /**
   * Returns a string which is stored 
   * in the reference field of an Odoo Invoice (Reference for client)
   * 
   * @return string
   */
  public function getClientReference();
  
  /**
   * Returns the ID of the company
   * 
   * @return int;
   */
  public function getCompanyId();
  
  /**
   * Returns the account ID on which the invoice is booked
   * 
   * E.g. returns id 8: for account  110200 Debiteuren
   * @return int
   */
  public function getAccountId();
  
  /**
   * Returns the product ID for this contribution
   * 
   */
  public function getProductId();
  
}

