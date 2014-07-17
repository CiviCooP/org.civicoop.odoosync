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
   * in the reference field of an Odoo Invoice
   * 
   * @return string
   */
  public function getReference();
  
  /**
   * Returns the ID of the company
   * 
   * @return int;
   */
  public function getCompanyId();
  
}

