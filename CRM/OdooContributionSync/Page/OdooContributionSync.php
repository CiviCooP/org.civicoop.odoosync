<?php

require_once 'CRM/Core/Page.php';

class CRM_OdooContributionSync_Page_OdooContributionSync extends CRM_Core_Page_Basic {
  /**
   * The action links that we need to display for the browse screen
   *
   * @var array
   * @static
   */
  static $_links = NULL;
  
  function getBAOName() {
    return 'CRM_OdooContributionSync_BAO_OdooContributionSettings';
  }
  
  /**
   * Get action Links
   *
   * @return array (reference) of action links
   */
  function &links() {
    if (!(self::$_links)) {
      self::$_links = array(
        CRM_Core_Action::UPDATE => array(
          'name' => ts('Edit'),
          'url' => 'civicrm/admin/odoo/contribution',
          'qs' => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit contribution setting'),
        ),
        CRM_Core_Action::DELETE => array(
          'name' => ts('Delete'),
          'url' => 'civicrm/admin/odoo/contribution',
          'qs' => 'action=delete&id=%%id%%',
          'title' => ts('Delete contribution setting'),
        ),
      );
    }
    return self::$_links;
  }

  /**
   * Run the page.
   *
   * This method is called after the page is created. It checks for the
   * type of action and executes that action.
   * Finally it calls the parent's run method.
   *
   * @return void
   * @access public
   *
   */
  function run() {
    // set title and breadcrumb
    CRM_Utils_System::setTitle(ts('Contribution settings - Odoo'));
    $breadCrumb = array(array('title' => ts('Contribution settings'),
        'url' => CRM_Utils_System::url('civicrm/admin/odoo/contribution',
          'reset=1'
        ),
      ));
    CRM_Utils_System::appendBreadCrumb($breadCrumb);

    $this->_id = CRM_Utils_Request::retrieve('id', 'String',
      $this, FALSE, 0
    );
    $this->_action = CRM_Utils_Request::retrieve('action', 'String',
      $this, FALSE, 0
    );
    
    //check if Odoo connection exists
    $connector = CRM_Odoosync_Connector::singleton();
    if ($connector->getUserId() === false) {
      CRM_Core_Session::setStatus(ts('Could not connect to Odoo. Did you provide the right settings?'), 'Problem connecting to Odoo', 'error');
    }

    return parent::run();
  }

  /**
   * Browse all Providers.
   *
   * @return void
   * @access public
   * @static
   */
  function browse($action = NULL) {
    $utils = CRM_OdooContributionSync_Utils::singleton();
    
    $financialTypes = $this->getFinancialTypes();
    $companies = $utils->getAvailableCompanies();
    $journals = $utils->getAvailableJournals();
    $accounts = $utils->getAvailableAccounts();
    $products = $utils->getAvailableProducts();
    
    $settings = CRM_OdooContributionSync_BAO_OdooContributionSettings::getSettings();
    
    $rows = array();
    foreach ($settings as $setting) {
      $action = array_sum(array_keys($this->links()));
      
      $setting['financial_type_id'] = $financialTypes[$setting['financial_type_id']];
      $setting['company_id'] = $companies[$setting['company_id']];
      $setting['journal_id'] = $journals[$setting['journal_id']];
      $setting['account_id'] = $accounts[$setting['account_id']];
      $setting['product_id'] = $products[$setting['product_id']];

      $setting['action'] = CRM_Core_Action::formLink(self::links(), $action,
        array('id' => $setting['id'])
      );
      $rows[] = $setting;
    }
    $this->assign('rows', $rows);
  }

  /**
   * Get name of edit form
   *
   * @return string Classname of edit form.
   */
  function editForm() {
    return 'CRM_OdooContributionSync_Form_OdooContributionSync';
  }

  /**
   * Get edit form name
   *
   * @return string name of this page.
   */
  function editName() {
    return 'Odoo contribution setting';
  }

  /**
   * Get user context.
   *
   * @return string user context.
   */
  function userContext($mode = NULL) {
    return 'civicrm/admin/odoo/contribution';
  }
  
  protected function getFinancialTypes() {
    $financial_types = new CRM_Financial_DAO_FinancialType();
    $financial_types->is_active = 1;
    $financial_types->find(FALSE);
    $return = array();
    $return[] = '';
    while($financial_types->fetch()) {
      $return[$financial_types->id] = $financial_types->name;
    }
    return $return;
  }
}
