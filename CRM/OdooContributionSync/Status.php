<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_OdooContributionSync_Status {

  protected $contact_id;

  protected $contribution_id;

  protected $status;

  protected $isResyncable = true;

  protected $odoo_id;

  protected $config;

  public function __construct($contribution_id, $contact_id) {
    $this->config = CRM_Odoosync_Config_OdooParameters::singleton();
    $this->contact_id = $contact_id;
    $this->contribution_id = $contribution_id;

    //find odoo id
    $this->status = 'Not available in Odoo sync table. Did something go wrong?';
    $this->odoo_id = false;
    $sql = "SELECT *  FROM `civicrm_odoo_entity` WHERE `entity` = %1  AND `entity_id`  = %2";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
      1 => array('civicrm_contribution', 'String'),
      2 => array($contribution_id, 'Integer'),
    ));

    if ($dao->fetch()) {
      $this->status = CRM_OdooContributionSync_Status::convertStatusToHumanReadable($dao->status);

      if (!empty($dao->last_error)) {
        $this->status .= "\r\nLast error message: ".$dao->last_error;
      }
      $this->isResyncable = CRM_OdooContributionSync_Status::determineResyncable($dao->odoo_id, $dao->status, $dao->last_error);
    }
  }

  public static function determineResyncable($odoo_id, $status, $last_error) {
    if ( ($odoo_id > 0 && $status == 'SYNCED') || ($status == 'OUT OF SYNC' && empty($last_error))) {
      return false;
    }
    return true;
  }

  public static function convertStatusToHumanReadable($status) {
    $s = $status;
    if (empty($s)) {
      $s = ts('Not available in Odoo sync table. Did something go wrong?');
    }
    switch ($status) {
      case 'OUT OF SYNC':
        $s .= "\r\n".ts("In the queue to be synced.");
        break;
      case 'SYNCED':
        $s .= "\r\n".ts("Synced with Odoo.");
        break;
      case 'NOT SYNCABLE':
        $s .= "\r\n".ts("Not possible to sync this contribution.");
        break;
    }
    return $s;
  }

  public function contributionIsInOdoo() {
    if ($this->odoo_id) {
      return true;
    }
    return false;
  }

  public function getLink() {
    return $this->config->getViewInvoiceUrl($this->odoo_id);
  }

  public function isResyncable() {
    return $this->isResyncable ? true : false;
  }

  public function getStatus() {
    return str_replace("\r\n", "<br>", $this->status);
  }

}