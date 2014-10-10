<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'CRM_Odoosync_Form_Report_OdooSyncQueue',
    'entity' => 'ReportTemplate',
    'params' => 
    array (
      'version' => 3,
      'label' => 'Odoo Sync Queue',
      'description' => 'Shows a summary of the sync queue',
      'class_name' => 'CRM_Odoosync_Form_Report_OdooSyncQueue',
      'report_url' => 'org.civicoop.odoosync/odoosyncqueue',
      'component' => '',
    ),
  ),
);