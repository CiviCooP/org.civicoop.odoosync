<?php

class CRM_Odoosync_Form_Report_OdooSyncQueue extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = array();
  protected $_customGroupGroupBy = FALSE; 
  protected $_add2groupSupported = FALSE;
  
  protected $_noFields = TRUE;
  
  function __construct() {
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    $this->_columns = array();
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Odoo Sync Queue'));
    parent::preProcess();
  }
  
  function buildQuery($applyLimit = TRUE) {
    return "SELECT entity, `status`, `last_error`, COUNT(*) AS `total` FROM `civicrm_odoo_entity` GROUP BY `entity`, `status`, `last_error` ORDER BY `status`, `entity`, `last_error`;";
  }

  function postProcess() {

    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $sql = $this->buildQuery(TRUE);
    
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
    $this->_columnHeaders['last_error'] = array('title' =>'Last error');
    $this->_columnHeaders['total'] = array('title' =>'total');
  }

  function alterDisplay(&$rows) {
    // custom code to alter rows
  }
}
