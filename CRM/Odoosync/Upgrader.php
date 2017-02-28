<?php

/**
 * Collection of upgrade steps
 */
class CRM_Odoosync_Upgrader extends CRM_Odoosync_Upgrader_Base {

  protected $contribution_status_id;
  
  protected $activity_type_id;
  
  public function __construct($extensionName, $extensionDir) {
    parent::__construct($extensionName, $extensionDir);
    
    $this->contribution_status_id = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => 'contribution_status'));
    $this->activity_type_id = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => 'activity_type'));
  }
  
  public function install() {
    $this->executeSqlFile('sql/install.sql');
    $this->addContributionStatuses();
    $this->addActivityTypes();

    $message = "Copy the settings below to civicrm.settings.php and change them to your need: \n\n";
    $message .= "global \$odoo_settings;\n";
    $message .= "\$odoo_settings['url'] = 'http://your.odoo:8069/xmlrpc';\n";
    $message .= "\$odoo_settings['databasename'] = 'odoo_database_name';\n";
    $message .= "\$odoo_settings['username'] = 'username';\n";
    $message .= "\$odoo_settings['password'] = 'password';\n";
    $message .= "\$odoo_settings['view_partner_url'] = 'http://your.odoo:8069/?db=databasename#id={partner_id}&view_type=form&model=res.partner&action=569';\n";
    $message .= "\$odoo_settings['view_invoice_url'] = 'http://your.odoo:8069/?db=databasename#id={partner_id}&view_type=form&model=res.partner&action=569';\n";
    CRM_Core_Session::setStatus(nl2br($message), 'Odoo settings', 'success', array('expires' => 0));
  }
  
  public function upgrade_1001() {
    $this->addContributionStatuses();
    return TRUE;
  }
  
  public function upgrade_1002() {
    $this->addActivityTypes();
    return TRUE;
  }
  
  public function upgrade_1003() {
    $this->executeSqlFile('sql/upgrade_1003.sql');
    return TRUE;
  }
  
  public function upgrade_1004() {
    $this->executeSqlFile('sql/upgrade_1004.sql');
    return TRUE;
  }
  
  public function upgrade_1005() {
    $this->executeSqlFile('sql/upgrade_1005.sql');
    return TRUE;
  }
  
  public function upgrade_1006() {
    $this->addOptionValue('send_to_bank', 'Send to bank', $this->contribution_status_id);
    return TRUE;
  }
  
  public function upgrade_1007() {
    $this->executeSqlFile('sql/upgrade_1007.sql');
    return TRUE;
  }

  public function upgrade_1008() {
    $this->executeSqlFile('sql/upgrade_1008.sql');
    return TRUE;
  }

  public function upgrade_1009() {
    $values = array();
    $values['url'] = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'url');
    $values['db_name'] = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'databasename');
    $values['username'] = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'username');
    $values['password'] = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'password');
    $values['view_partner_url'] = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'view_partner_url');
    $values['view_invoice_url'] = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', 'view_invoice_url');
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_setting WHERE group_name = 'org.civicoop.odoosync'");

    $message = "Copy the settings below to civicrm.settings.php: \n\n";
    $message .= "global \$odoo_settings;\n";
    $message .= "\$odoo_settings['url'] = '" . $values['url'] . "';\n";

    $message .= "\$odoo_settings['databasename'] = '" . $values['db_name'] . "';\n";
    $message .= "\$odoo_settings['username'] = '" . $values['username'] . "';\n";
    $message .= "\$odoo_settings['password'] = '" . $values['password'] . "';\n";
    $message .= "\$odoo_settings['view_partner_url'] = '" . $values['view_partner_url'] . "';\n";
    $message .= "\$odoo_settings['view_invoice_url'] = '" . $values['view_invoice_url'] . "';\n";

    CRM_Core_Session::setStatus(nl2br($message), 'Odoo settings', 'success', array('expires' => 0));

    return true;
  }

  public function uninstall() {
   $this->executeSqlFile('sql/uninstall.sql');
  }
  
  protected function addContributionStatuses() {
    $this->addOptionValue('refunded', 'Refund', $this->contribution_status_id);
    $this->addOptionValue('payment_scheme', 'Payment scheme', $this->contribution_status_id);
    $this->addOptionValue('send_to_bank', 'Send to bank', $this->contribution_status_id);
  }
  
  protected function addActivityTypes() {
    $this->addOptionValue('payment_reminder', 'Payment reminder', $this->activity_type_id);
    $this->addOptionValue('odoo_communication', 'Communication from within Odoo', $this->activity_type_id);
  }
  
  protected function addOptionValue($name, $label, $option_group_id) {
    try {
      $exist_id = civicrm_api3('OptionValue', 'getvalue', array('return' => 'id', 'name' => $name, 'option_group_id' => $option_group_id));
      return; //aleardy exist
    } catch (Exception $e) {
      //do nothing
    }
    
    $params['name'] = $name;
    $params['label'] = $label;
    $params['option_group_id'] = $option_group_id;
    civicrm_api3('OptionValue','create', $params);
  }
  
}
