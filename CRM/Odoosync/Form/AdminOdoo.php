<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Odoosync_Form_AdminOdoo extends CRM_Core_Form {
  function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('Odoo settings'));
    
    // add form elements
    $this->add(
      'text', // field type
      'url', // field name
      'Odoo server URL', // field label
      array(
        'size' => CRM_Utils_Type::HUGE,
      ), //attributes 
      true // is required
    );
    
    $this->add(
      'text', // field type
      'db_name', // field name
      'Database name', // field label
      array('size' => CRM_Utils_Type::HUGE), //attributes 
      true // is required
    );
    
    $this->add(
      'text', // field type
      'username', // field name
      'Username', // field label
      array('size' => CRM_Utils_Type::HUGE), //attributes 
      true // is required
    );
    
    $this->add(
      'text', // field type
      'password', // field name
      'Password', // field label
      array('size' => CRM_Utils_Type::HUGE), //attributes 
      true // is required
    );
    
    // add form elements
    $this->add(
      'text', // field type
      'view_partner_url', // field name
      'View partner URL', // field label
      array(
        'size' => CRM_Utils_Type::HUGE,
      ), //attributes 
      true // is required
    );

    // add form elements
    $this->add(
      'text', // field type
      'view_invoice_url', // field name
      'View invoice URL', // field label
      array(
        'size' => CRM_Utils_Type::HUGE,
      ), //attributes
      true // is required
    );
    
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }
  
  function setDefaultValues() {
    parent::setDefaultValues();
    
    $values['url'] = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'url');
    $values['db_name'] = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'databasename');
    $values['username'] = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'username');
    $values['password'] = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'password');
    $values['view_partner_url'] = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'view_partner_url');
    $values['view_invoice_url'] = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'view_invoice_url');
    return $values;
  }

  function postProcess() {
    $values = $this->exportValues();
    
    CRM_Core_BAO_Setting::setItem($values['url'], 'org.civicoop.odoosync', 'url');
    CRM_Core_BAO_Setting::setItem($values['db_name'], 'org.civicoop.odoosync', 'databasename');
    CRM_Core_BAO_Setting::setItem($values['username'], 'org.civicoop.odoosync', 'username');
    CRM_Core_BAO_Setting::setItem($values['password'], 'org.civicoop.odoosync', 'password');
    CRM_Core_BAO_Setting::setItem($values['view_partner_url'], 'org.civicoop.odoosync', 'view_partner_url');
    CRM_Core_BAO_Setting::setItem($values['view_invoice_url'], 'org.civicoop.odoosync', 'view_invoice_url');
    
    CRM_Core_Session::setStatus(ts('Saved Odoo settings'), ts('Odoo settings'), 'success');
    
    $connector = CRM_Odoosync_Connector::singleton();
    if ($connector->getUserId() === false) {
      CRM_Core_Session::setStatus(ts('Could not connect to Odoo. Did you provide the right settings?'), 'Problem connecting to Odoo', 'error');
    }
    
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}
