<?php

class CRM_Odoosync_Model_Lock {
  
  protected $key;
  
  public function __construct($key) {
    $this->key = $key;
  }
  
  public function unlock($keepLockCount=false) {
    if ($keepLockCount) {
      $lockCount = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', $this->key.'_lock_count');
      if ($lockCount) {
        $lockCount++;
      } else {
        $lockCount = 1;
      }
      CRM_Core_BAO_Setting::setItem($lockCount, 'org.civicoop.odoosync', $this->key.'_lock_count');
    } else {
      CRM_Core_BAO_Setting::setItem(0, 'org.civicoop.odoosync', $this->key.'_lock_count');
    }
    CRM_Core_BAO_Setting::setItem('0', 'org.civicoop.odoosync', $this->key.'_lock');
  }
  
  public function autoUnlock() {
    if ($this->isLocked()) {
      $lockTime = time() - $this->getLockTime();
      if ($lockTime > (4*60*60)) {
        //after 4 hours unlock
        $this->unlock(true);
      }
    }
  }
  
  public function getLockCount() {
    $lockCount = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', $this->key.'_lock_count');
    return $lockCount;
  }
  
  public function getLockTime() {
    $lock = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', $this->key.'_lock');
    return $lock;
  }
  
  public function isLocked() {
    $lock = CRM_Core_BAO_Setting::getItem('org.civicoop.odoosync', $this->key.'_lock');
    return $lock ? true : false;
  }
  
  public function lock() {
    if ($this->isLocked()) {
      return;
    }
    CRM_Core_BAO_Setting::setItem(time(), 'org.civicoop.odoosync', $this->key.'_lock');
  }
}

