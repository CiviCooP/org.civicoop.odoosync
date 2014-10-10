<?php

class CRM_OdooContributionSync_Utils_Product {
  
  private static $_singleton;
  
  private $products = array();
  
  private $connector;
  
  private function __construct() {
    $this->connector = CRM_Odoosync_Connector::singleton();
  }
  
  /**
   * 
   * @return CRM_OdooContributionSync_Utils_Product
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_OdooContributionSync_Utils_Product();
    }
    return self::$_singleton;
  }
  
  public function getProductFromOdoo($product_id) {
    if (!isset($this->products[$product_id])) {
      $this->products[$product_id] = $this->connector->read('product.product', $product_id);
    }
    
    return $this->products[$product_id];
  }
  
}

