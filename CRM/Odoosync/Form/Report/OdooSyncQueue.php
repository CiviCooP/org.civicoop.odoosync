<?php

class CRM_Odoosync_Form_Report_OdooSyncQueue extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = array();
  protected $_customGroupGroupBy = FALSE; 
  protected $_add2groupSupported = FALSE;
  
  protected $_noFields = TRUE;
  
  private $_urlDetailsReport = '#';
  
  function __construct() {
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    $this->_columns = array();
    
    try {
      // get the id of the sync queue details report instance
      $params = array(
        'title' => 'Odoo Sync Queue Details',
        'return' => 'id',
      );
      $reportInstanceID = civicrm_api3('ReportInstance', 'getvalue', $params);    
      $this->_urlDetailsReport = 'civicrm/report/instance/'. $reportInstanceID;
    }
    catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Session::setStatus("Kan het rapport met naam 'Odoo Sync Queue Details' niet vinden. Doorklikken op een fout is nu niet mogelijk.", ts('Error'), 'error');
    }
    
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
    foreach ($rows as $rowNum => $row) {
      if ($row['total'] > 0) {
        $url = CRM_Utils_System::url($this->_urlDetailsReport, "reset=1&entity=" . $row['entity'] . "&status=" . urlencode($row['status']) . "&last_error=" . urlencode($row['last_error']) . "&max_records=100");
        $rows[$rowNum]['total'] = '<a href="' . $url .'">' . $row['total'] . '</a>';
      }
    }
  }
}
