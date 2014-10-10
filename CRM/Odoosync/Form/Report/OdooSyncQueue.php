<?php

class CRM_Odoosync_Form_Report_OdooSyncQueue extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = array();
  protected $_customGroupGroupBy = FALSE; 
  
  function __construct() {
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Odoo Sync Queue'));
    parent::preProcess();
  }

  function postProcess() {

    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $sql = "SELECT entity, `status`, COUNT(*) AS `total` FROM `civicrm_odoo_entity` GROUP BY `entity`, `status`;";

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }
  
  function modifyColumnHeaders() {
    // use this method to modify $this->_columnHeaders
    $this->_columnHeaders['entity'] = array('title' => 'entity');
    $this->_columnHeaders['status'] = array('title' =>'status');
    $this->_columnHeaders['total'] = array('title' =>'total');
  }

  function alterDisplay(&$rows) {
    // custom code to alter rows
  }
}
