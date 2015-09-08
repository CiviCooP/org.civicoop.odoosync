<?php

class CRM_Odoosync_Utils_Contact {

  /**
   * Returns true when the contact still exist in CiviCRM,
   * Returns false when the contact is deleted in CiviCRM
   * Returns false when the contact does not exist in CiviCRM
   *
   * @param $contact_id
   * @return bool
   */
  public static function doesContactExistInCivi($contact_id) {
    try {
      $contact = civicrm_api3('Contact', 'getsingle', array('id' => $contact_id));
      if (!$contact['is_deleted']) {
        return true;
      }
    } catch (Exception $e) {
      //do nothing
    }
    return false;
  }

}