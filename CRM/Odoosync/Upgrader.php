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

  public function uninstall() {
   $this->executeSqlFile('sql/uninstall.sql');
  }
  
  protected function addContributionStatuses() {
    $this->addOptionValue('refunded', 'Refund', $this->contribution_status_id);
    $this->addOptionValue('payment_scheme', 'Payment scheme', $this->contribution_status_id);
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
