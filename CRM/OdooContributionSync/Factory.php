<?php

class CRM_OdooContributionSync_Factory {
  
  public static function getSettingsForContribution($contribution) {
    return new CRM_OdooContributionSync_Settings_Default($contribution);
  }
  
}

