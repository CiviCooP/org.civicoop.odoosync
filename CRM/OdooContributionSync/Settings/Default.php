<?php

class CRM_OdooContributionSync_Settings_Default implements CRM_OdooContributionSync_Settings_Interface {
  
  protected $contribution;
  
  public function __construct($contribution) {
    $this->contribution = $contribution;
  }
  
  /**
   * Returns the default journal ID
   * 
   * @return int
   */
  public function getJournalId() {
    return 1; //Verkoop boek
  }
  
  public function getCompanyId() {
     return 1; //default company
  }
  
  public function getReference() {
    return $this->contribution['financial_type'];
  }
  
  public function getAccountId() {
     return 8; //110200 Debtors
  }
  
  public function getTaxId() {
    return 7; //21% btw
  }
  
  public function getProductId() {
    return 1; //dienst
  }
  
}
