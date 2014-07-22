<?php

class CRM_OdooContributionSync_BAO_OdooContributionSettings extends CRM_OdooContributionSync_DAO_OdooContributionSettings implements CRM_OdooContributionSync_Settings_Interface {
  
  protected $contribution = false;
  
  public function __construct($contribution = false) {
    parent::__construct();
    $this->contribution = $contribution;
  }
  
  public function getAccountId() {
    return $this->account_id;
  }
  
  public function getCompanyId() {
    return $this->company_id;
  }
  
  public function getJournalId() {
    return $this->journal_id;
  }
  
  public function getProductId() {
    return $this->product_id;
  }
  
  public function getTaxId() {
    return $this->tax_id;
  }
  
  public function getReference() {
    if ($this->contribution && isset($this->contribution['financial_type'])) {
      return $this->contribution['finanial_type'];
    }
    return '';
  }
  
}

