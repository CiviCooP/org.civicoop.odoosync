<?php

class CRM_OdooContributionSync_Factory {
  
  public static function getSettingsForContribution($contribution) {
    $settings = new CRM_OdooContributionSync_BAO_OdooContributionSettings($contribution);
    $settings->financial_type_id = $contribution['financial_type_id'];
    if ($settings->find(TRUE)) {
      return $settings;
    }
    
    return false;
  }
  
}

