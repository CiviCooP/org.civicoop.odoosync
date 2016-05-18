<?php

include_once DRUPAL_ROOT . '/includes/xmlrpc.inc';

class CRM_Odoosync_Form_Report_OdooSyncQueueDetails extends CRM_Report_Form {

	protected $_addressField = FALSE;

	protected $_emailField = FALSE;

	protected $_summary = NULL;

	protected $_customGroupExtends = array();
	protected $_customGroupGroupBy = FALSE;
	protected $_add2groupSupported = FALSE;

	protected $_noFields = TRUE;

	private $_errorEntity = '';
	private $_errorStatus = '';
	private $_errorLastError = '';
	private $_maxRecords = 100;

	function __construct() {
		$this->_groupFilter = FALSE;
		$this->_tagFilter = FALSE;
		$this->_columns = array();

		// get URL parameters
		$this->_errorEntity = CRM_Utils_Request::retrieve('entity', 'String');
		$this->_errorStatus = urldecode(CRM_Utils_Request::retrieve('status', 'String'));
		$this->_errorLastError = urldecode(CRM_Utils_Request::retrieve('last_error', 'String'));
		if (CRM_Utils_Request::retrieve('max_records', 'Positive')) {
			$this->_maxRecords = CRM_Utils_Request::retrieve('max_records', 'Positive');
		}

		// make sure we have at least 1 parameter
		if (empty($this->_errorEntity) && empty($this->_errorStatus) && empty($this->_errorLastError)) {
			CRM_Core_Session::setStatus("This report should be called from the report Odoo Sync Queue.", ts('Error'), 'error');
		}

		// dummy columns array to show the criteria
		$this->_columns = array(
			'dummy' => array(
				'fields' => array(
					'entity' => array(
						'title' => 'Entity: ' . $this->_errorEntity,
						'required' => TRUE,
					),
					'status' => array(
						'title' => 'Status: ' . $this->_errorStatus,
						'required' => TRUE,
					),
					'last_error' => array(
						'title' => 'Last error: ' . $this->_errorLastError,
						'required' => TRUE,
					),
					'max_error' => array(
						'title' => 'max records: ' . $this->_maxRecords,
						'required' => TRUE,
					),
				),
			),
		);

		parent::__construct();
	}

	function preProcess() {
		$this->assign('reportTitle', ts('Odoo Sync Queue Details') . '<br />' . 'Entity: ' . $this->_errorEntity . '<br />' . 'Status: ' . $this->_errorStatus . '<br />' . $this->_errorLastError);
		parent::preProcess();
	}

	function buildQuery($applyLimit = TRUE) {
		$query = "
      SELECT
        sync_date
        , odoo_id
        , odoo_field
        , action
        , data
      FROM
        `civicrm_odoo_entity`
      WHERE
        1 = 1
    ";

		if (!empty($this->_errorEntity)) {
			$query .= " AND entity = '" . $this->_errorEntity . "'";
		}

		if (!empty($this->_errorStatus)) {
			$query .= " AND status = '" . $this->_errorStatus . "'";
		}

		if (!empty($this->_errorLastError)) {
			$query .= " AND last_error = '" . $this->_errorLastError . "'";
		}

		$query .= "
      ORDER BY
        sync_date DESC
      LIMIT
        0, {$this->_maxRecords}
    ";

		return $query;
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
		$this->_columnHeaders['sync_date'] = array('title' => 'Sync date');
		$this->_columnHeaders['odoo_id'] = array('title' => 'Odoo ID');
		$this->_columnHeaders['odoo_field'] = array('title' => 'Odoo field');
		$this->_columnHeaders['action'] = array('title' => 'Action');
		$this->_columnHeaders['data'] = array('title' =>'Extra information');
	}

	function alterDisplay(&$rows) {
		foreach ($rows as $rowNum => $row) {
			if (!empty($row['data'])) {
				// data is an xmlrpc object, unserialize it
				$o = unserialize($row['data']);

				// show the relevant field(s) based on the entity
				switch ($this->_errorEntity) {
					case "civicrm_phone":
						// data can contain a field 'phone' or 'mobile'

						if (array_key_exists('phone', $o)) {
							$rows[$rowNum]['data'] = 'phone = ' . reset($o['phone']->me);
						}
						else if (array_key_exists('mobile', $o)) {
							$rows[$rowNum]['data'] = 'mobile = ' . reset($o['mobile']->me);
						}
						break;
					case "civicrm_address":
						// data can contain the fields 'street', 'city' and 'zip'
						$rows[$rowNum]['data'] = '';
						if (array_key_exists('street', $o)) {
							$rows[$rowNum]['data'] .= 'street = ' . reset($o['street']->me);
						}
						if (array_key_exists('city', $o)) {
							$rows[$rowNum]['data'] .= ', city = ' . reset($o['city']->me);
						}
						if (array_key_exists('zip', $o)) {
							$rows[$rowNum]['data'] .= ', zip = ' . reset($o['zip']->me);
						}
						break;
					case "civicrm_contact":
						// data can contain a field 'display name'
						if (array_key_exists('display_name', $o)) {
							$rows[$rowNum]['data'] = 'display name = ' . reset($o['display_name']->me);
						}
						break;
					case "civicrm_contribution":
						// data can contain the fields journal_id, account_id, partner_id, reference, date_invoice... among others
						$rows[$rowNum]['data'] = '';
						if (array_key_exists('journal_id', $o)) {
							$rows[$rowNum]['data'] .= 'journal id = ' . reset($o['journal_id']->me);
						}
						if (array_key_exists('account_id', $o)) {
							$rows[$rowNum]['data'] .= ', account id = ' . reset($o['account_id']->me);
						}
						if (array_key_exists('partner_id', $o)) {
							$rows[$rowNum]['data'] .= ', partner id = ' . reset($o['partner_id']->me);
						}
						if (array_key_exists('reference', $o)) {
							$rows[$rowNum]['data'] .= ', reference = ' . reset($o['reference']->me);
						}
						if (array_key_exists('date_invoice', $o)) {
							$rows[$rowNum]['data'] .= ', date invoice = ' . reset($o['date_invoice']->me);
						}
						break;
					case "civicrm_email":
						// data can contain a field 'email'
						if (array_key_exists('email', $o)) {
							$rows[$rowNum]['data'] = 'email = ' . reset($o['email']->me);
						}
						break;
					case "civicrm_value_iban":
						// data can contain the fields acc_number, bank_bic, owner_name
						$rows[$rowNum]['data'] = '';
						if (array_key_exists('acc_number', $o)) {
							$rows[$rowNum]['data'] .= 'account number = ' . reset($o['acc_number']->me);
						}
						if (array_key_exists('bank_bic', $o)) {
							$rows[$rowNum]['data'] .= ', BIC = ' . reset($o['bank_bic']->me);
						}
						if (array_key_exists('owner_name', $o)) {
							$rows[$rowNum]['data'] .= ', owner name = ' . reset($o['owner_name']->me);
						}
						break;
					case "civicrm_value_payment_arrangement":
						// data can contain a field 'comment'
						if (array_key_exists('comment', $o)) {
							$rows[$rowNum]['data'] = 'comment = ' . reset($o['comment']->me);
						}
						break;
					case "civicrm_value_sepa_mandaat":
						// data can contain the fields partner_bank_id, signature_date, unique_mandate_reference, state
						$rows[$rowNum]['data'] = '';
						if (array_key_exists('partner_bank_id', $o)) {
							$rows[$rowNum]['data'] .= 'partner bank id = ' . reset($o['partner_bank_id']->me);
						}
						if (array_key_exists('signature_date', $o)) {
							$rows[$rowNum]['data'] .= ', signature date = ' . reset($o['signature_date']->me);
						}
						if (array_key_exists('unique_mandate_reference', $o)) {
							$rows[$rowNum]['data'] .= ', unique mandate reference = ' . reset($o['unique_mandate_reference']->me);
						}
						if (array_key_exists('state', $o)) {
							$rows[$rowNum]['data'] .= ', state = ' . reset($o['state']->me);
						}
						break;
					default:
						// leave "as is"
				}
			}
		}
	}
}
