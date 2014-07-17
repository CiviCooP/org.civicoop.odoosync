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
    return 1;
  }
  
  public function getCompanyId() {
     return 1;
  }
  
  public function getReference() {
    return $this->contribution['financial_type'];
  }
  
}
