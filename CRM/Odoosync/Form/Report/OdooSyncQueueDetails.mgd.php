<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'CRM_Odoosync_Form_Report_OdooSyncQueueDetails',
    'entity' => 'ReportTemplate',
    'params' => 
    array (
      'version' => 3,
      'label' => 'Odoo Sync Queue Details',
      'description' => 'Shows a detailed sync queue information. Should be opened from the sync queue summary report.',
      'class_name' => 'CRM_Odoosync_Form_Report_OdooSyncQueueDetails',
      'report_url' => 'org.civicoop.odoosync/odoosyncqueuedetails',
      'component' => '',
    ),
  ),
);
