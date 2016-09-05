<?php

/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_OdooContributionSync_Form_Report_ContributionSync extends CRM_Report_Form {

  private $btnRestoreSyncName;

  function __construct() {
    $this->_columns = array(
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          'id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'display_name' => array(
            'title' => ts('Contact'),
            'default' => TRUE,
            'name' => 'display_name',
            'required' => TRUE,
          ),
        ),
        'order_bys' =>
          array(
            'sort_name' => array(
              'title' => ts('Last Name, First Name'),
              'default' => '1',
              'default_weight' => '1',
              'default_order' => 'ASC',
            ),
          ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_contribution' => array(
        'dao' => 'CRM_Contribute_DAO_Contribution',
        'fields' => array(
          'contribution_id' => array(
            'name' => 'id',
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'financial_type_id' => array(
            'title' => ts('Financial Type'),
            'default' => TRUE,
          ),
          'contribution_status_id' => array(
            'title' => ts('Contribution Status'),
          ),
          'source' => array(
            'title' => ts('Source'),
          ),
          'payment_instrument_id' => array(
            'title' => ts('Payment Type'),
          ),
          'currency' => array(
            'required' => TRUE,
            'no_display' => TRUE,
          ),
          'receive_date' => array(
            'default' => TRUE
          ),
          'total_amount' => array(
            'title' => ts('Amount'),
            'required' => TRUE,
          ),
        ),
        'filters' => array(
          'receive_date' => array(
            'operatorType' => CRM_Report_Form::OP_DATE,
          ),
          'financial_type_id' => array(
            'title' => ts('Financial Type'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Contribute_PseudoConstant::financialType(),
            'type' => CRM_Utils_Type::T_INT,
          ),
          'payment_instrument_id' => array(
            'title' => ts('Payment Type'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Contribute_PseudoConstant::paymentInstrument(),
            'type' => CRM_Utils_Type::T_INT,
          ),
          'contribution_status_id' => array(
            'title' => ts('Contribution Status'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Contribute_PseudoConstant::contributionStatus(),
            'default' => array(1),
            'type' => CRM_Utils_Type::T_INT,
          ),
          'total_amount' => array(
            'title' => ts('Contribution Amount')
          ),
        ),
        'order_bys' => array(
          'financial_type_id' => array('title' => ts('Financial Type')),
          'contribution_status_id' => array(
            'title' => ts('Contribution Status'),
            'default' => FALSE,
          ),
          'payment_instrument_id' => array('title' => ts('Payment Instrument')),
          'receive_date' => array(
            'title' => ts('Receive Date'),
            'default' => '1',
            'default_weight' => '0',
            'default_order' => 'ASC',
          ),
        ),
        'grouping' => 'contri-fields',
      ),
      'civicrm_odoo_entity' => array(
        'fields' => array(
          'odoo_id' => array(
            'required' => true,
            'no_display' => true,
          ),
          'status' => array(
            'title' => ts('Sync status'),
            'required' => true,
            'default' => true,
          ),
          'last_error' => array(
            'title' => ts('Last error message'),
            'default' => true,
            'required' => true,
          ),
        ),
        'filters' => array(
          'status' => array(
            'title' => ts('Sync status'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'pseudofield' => TRUE,
            'options' => array(
              'NOT IN QUEUE' => 'NOT IN QUEUE',
              'OUT OF SYNC' => 'OUT OF SYNC',
              'NOT SYNCABLE' => 'NOT SYNCABLE',
              'SYNCED' => 'SYNCED',
            ),
            'default' => array('NOT IN QUEUE', 'OUT OF SYNC', 'NOT SYNCABLE'),
            'type' => CRM_Utils_Type::T_STRING,
          ),
        ),
        'grouping' => 'odoo',
      )
    );
    $this->_add2groupSupported = false;
    $this->_csvSupported = true;
    parent::__construct();
  }

  function preProcessCommon() {
    parent::preProcessCommon(); // TODO: Change the autogenerated stub
    $this->btnRestoreSyncName = $this->getButtonName('submit', 'restore_sync');
  }

  function setDefaultValues($freeze = TRUE) {
    parent::setDefaultValues($freeze);
    $this->_defaults['receive_date_relative'] = 'greater.quarter';

    return $this->_defaults;
  }

  function from() {
    $this->_from = " 
    FROM  civicrm_contribution {$this->_aliases['civicrm_contribution']} 
    LEFT JOIN civicrm_odoo_entity {$this->_aliases['civicrm_odoo_entity']} ON {$this->_aliases['civicrm_contribution']}.id = {$this->_aliases['civicrm_odoo_entity']}.entity_id AND {$this->_aliases['civicrm_odoo_entity']}.entity = 'civicrm_contribution'
    LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']} ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_contribution']}.contact_id
    {$this->_aclFrom} ";
  }

  function storeWhereHavingClauseArray(){
    parent::storeWhereHavingClauseArray();
    $fieldName = 'status';

    $op = CRM_Utils_Array::value("status_op", $this->_params);
    $values = CRM_Utils_Array::value("status_value", $this->_params);
    $clause = '';

    foreach($values as $value) {
      if (strlen($clause)) {
        $clause .= ' OR ';
      }
      if ($value == 'NOT IN QUEUE') {
        $clause .= "{$this->_aliases['civicrm_odoo_entity']}.status IS NULL";
      } else {
        $clause .= "{$this->_aliases['civicrm_odoo_entity']}.status = '".CRM_Utils_Type::escape($value, 'String')."'";
      }
    }
    if ($op == 'in' && strlen($clause)) {
      $this->_whereClauses[] = '('.$clause.')';
    } elseif ($op == 'notin' && strlen($clause)) {
      $this->_whereClauses[] = 'NOT ('.$clause.')';
    }
  }

  function buildInstanceAndButtons() {
    parent::buildInstanceAndButtons();
    if (CRM_Core_Permission::check('administer CiviCRM')) {
      $this->addElement('submit', $this->btnRestoreSyncName, ts('Restore sync for selected'));
    }
  }

  function processReportMode() {
    $buttonName = $this->controller->getButtonName();
    if ($buttonName == $this->btnRestoreSyncName) {
      $selected = $this->_submitValues['select_id'];
      if (is_array($selected)) {
        foreach($selected as $id) {
          civicrm_api3('Odoo', 'Resync', array('object_name' => 'Contribution', 'id' => $id));
        }
      }
    }
    parent::processReportMode();
  }

  function modifyColumnHeaders() {
    if (CRM_Core_Permission::check('administer CiviCRM')) {
      $tmpArray = array();
      $i = 0;
      foreach ($this->_columnHeaders as $key => $value) {
        if ($i === 0) {
          $tmpArray['checkbox'] = array('title' => '');
        }
        $tmpArray[$key] = $value;
      }
      $this->_columnHeaders = $tmpArray;
      $this->_columnHeaders['button'] = array('title' => '');
    }
  }

  function alterDisplay(&$rows) {
    $entryFound = FALSE;
    $contributionTypes  = CRM_Contribute_PseudoConstant::financialType();
    $contributionStatus = CRM_Contribute_PseudoConstant::contributionStatus();
    $paymentInstruments = CRM_Contribute_PseudoConstant::paymentInstrument();

    foreach ($rows as $rowNum => $row) {
      // convert donor sort name to link
      if (array_key_exists('civicrm_contact_display_name', $row) &&
        CRM_Utils_Array::value('civicrm_contact_display_name', $rows[$rowNum]) &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_display_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_display_name_hover'] = ts("View Contact Summary for this Contact.");
        $entryFound = TRUE;
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_financial_type_id', $row)) {
        $rows[$rowNum]['civicrm_contribution_financial_type_id'] = $contributionTypes[$value];
        $entryFound = TRUE;
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_contribution_status_id', $row)) {
        $rows[$rowNum]['civicrm_contribution_contribution_status_id'] = $contributionStatus[$value];
        $entryFound = TRUE;
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_payment_instrument_id', $row)) {
        $rows[$rowNum]['civicrm_contribution_payment_instrument_id'] = $paymentInstruments[$value];
        $entryFound = TRUE;
      }
      if (isset($row['civicrm_odoo_entity_status'])) {
        $rows[$rowNum]['civicrm_odoo_entity_status'] = nl2br(CRM_OdooContributionSync_Status::convertStatusToHumanReadable($row['civicrm_odoo_entity_status']));
        $entryFound = TRUE;
      }
      if (CRM_Core_Permission::check('administer CiviCRM')) {
        if (CRM_OdooContributionSync_Status::determineResyncable($row['civicrm_odoo_entity_odoo_id'], $row['civicrm_odoo_entity_status'], $row['civicrm_odoo_entity_last_error'])) {
          $rows[$rowNum]['checkbox'] = '<input type="checkbox" class="select_id" name="select_id[]" value="'.$row['civicrm_contribution_contribution_id'].'" />';
          $rows[$rowNum]['button'] = '<a href="#" onclick=restoreSync(\''.$row['civicrm_contribution_contribution_id'].'\'); return false;" class="button">Restore sync</a>';
        }
        $entryFound = TRUE;
      }
      // skip looking further in rows, if first row itself doesn't
      // have the column we need
      if (!$entryFound) {
        break;
      }
    }
  }

}