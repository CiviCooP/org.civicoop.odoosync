<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 =>
    array (
      'name' => 'CRM_OdooContributionSync_Form_Report_ContributionSync',
      'entity' => 'ReportTemplate',
      'params' =>
        array (
          'version' => 3,
          'label' => 'Contribution sync status',
          'description' => 'Shows the status of the contribution sync',
          'class_name' => 'CRM_OdooContributionSync_Form_Report_ContributionSync',
          'report_url' => 'org.civicoop.odoosync/odoocontributionsync',
          'component' => 'CiviContribute',
        ),
    ),
);