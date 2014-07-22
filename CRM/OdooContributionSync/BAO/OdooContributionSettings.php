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
  
  static function create($values) {
    $dao = new CRM_OdooContributionSync_BAO_OdooContributionSettings();
    $dao->copyValues($values);
    $dao->save();
  }

  static function edit($values, $id) {
    $dao = new CRM_OdooContributionSync_BAO_OdooContributionSettings();
    $dao->id = $id;
    if ($dao->find(TRUE)) {
      $dao->copyValues($values);
      $dao->save();
    }
  }
  
  static function del($id) {
    if (!$id) {
      CRM_Core_Error::fatal(ts('Invalid value passed to delete function'));
    }

    $dao = new CRM_OdooContributionSync_BAO_OdooContributionSettings();
    $dao->id = $id;
    if (!$dao->find(TRUE)) {
      return NULL;
    }
    $dao->delete();
  }
  
  static function getSettings($selectArr = NULL, $filter = NULL, $orderBy = 'id') {
    $settings = array();
    $temp      = array();
    $dao       = new CRM_OdooContributionSync_DAO_OdooContributionSettings();
    if ($filter && is_array($filter)) {
      foreach ($filter as $key => $value) {
        $dao->$key = $value;
      }
    }
    if ($selectArr && is_array($selectArr)) {
      $select = implode(',', $selectArr);
      $dao->selectAdd($select);
    }
    $dao->orderBy($orderBy);
    $dao->find();
    while ($dao->fetch()) {
      CRM_Core_DAO::storeValues($dao, $temp);
      $settings[] = $temp;
    }
    return $settings;
  }
  
}

