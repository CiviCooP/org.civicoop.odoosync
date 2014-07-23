<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_OdooContributionSync_Form_OdooContributionSync extends CRM_Core_Form {
  protected $_id = NULL;

  function preProcess() {

    $this->_id = $this->get('id');

    CRM_Utils_System::setTitle(ts('Manage - Odoo Contribution settings'));

    if ($this->_id) {
      $refreshURL = CRM_Utils_System::url('civicrm/admin/odoo/contribution', "reset=1&action=update&id={$this->_id}", FALSE, NULL, FALSE
      );
    } else {
      $refreshURL = CRM_Utils_System::url('civicrm/admin/odoo/contribution', "reset=1&action=add", FALSE, NULL, FALSE
      );
    }

    $this->assign('refreshURL', $refreshURL);
  }

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    parent::buildQuickForm();

    if ($this->_action & CRM_Core_Action::DELETE) {
      $this->addButtons(array(
        array(
          'type' => 'next',
          'name' => ts('Delete'),
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
          )
      );
      return;
    } else {
      $this->addButtons(array(
        array(
          'type' => 'next',
          'name' => ts('Save'),
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
          )
      );
    }

    $attributes = CRM_Core_DAO::getAttribute('CRM_OdooContributionSync_DAO_OdooContributionSettings');

    $utils = CRM_OdooContributionSync_Utils::singleton();
    $financialTypes = $this->getFinancialTypes();
    $companies = array(' -- '.ts('Select a company').' -- ') + $utils->getAvailableCompanies();
    $journals = array(' -- '.ts('Select a journal').' -- ') + $utils->getAvailableJournals();
    $accounts = array(' -- '.ts('Select an account').' -- ') + $utils->getAvailableAccounts();
    $products = array(' -- '.ts('Select a product').' -- ') + $utils->getAvailableProducts();
    $taxes = array(' -- '.ts('Select a tax').' -- ') + $utils->getAvailableTaxes();

    $this->add('text', 'label', ts('Label'), $attributes['label'], TRUE
    );
    
    $this->add('select', 'financial_type_id', ts('Financial Type'), $financialTypes, FALSE);
    $this->add('select', 'company_id', ts('Company'), $companies, FALSE);
    $this->add('select', 'journal_id', ts('Journal'), $journals, FALSE);
    $this->add('select', 'account_id', ts('Debtor Account'), $accounts, FALSE);
    $this->add('select', 'product_id', ts('Product'), $products, FALSE);
    $this->add('select', 'tax_id', ts('Tax'), $taxes, FALSE);
  }

  function setDefaultValues() {
    $defaults = array();

    //set default value
    $dao = new CRM_OdooContributionSync_DAO_OdooContributionSettings();
    $dao->id = $this->_id;
    
    if (!$dao->find(TRUE)) {
      return $defaults;
    }

    CRM_Core_DAO::storeValues($dao, $defaults);
    return $defaults;
  }

  /**
   * Function to process the form
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    if ($this->_action & CRM_Core_Action::DELETE) {
      CRM_OdooContributionSync_BAO_OdooContributionSettings::del($this->_id);
      CRM_Core_Session::setStatus(ts('Selected setting has been deleted.'), ts('Deleted'), 'success');
      return;
    }
    
    $values = $this->controller->exportValues($this->_name);
    
    if ($this->_action & CRM_Core_Action::UPDATE) {
      CRM_OdooContributionSync_BAO_OdooContributionSettings::edit($values, $this->_id);
    } elseif ($this->_action & CRM_Core_Action::ADD) {
      CRM_OdooContributionSync_BAO_OdooContributionSettings::create($values);
    }
    
  }
  
  protected function getFinancialTypes() {
    $financial_types = new CRM_Financial_DAO_FinancialType();
    $financial_types->is_active = 1;
    $financial_types->find(FALSE);
    $return[] = ' -- '.ts('Select a financial type').' -- ';
    while($financial_types->fetch()) {
      $return[$financial_types->id] = $financial_types->name;
    }
    return $return;
  }
}
