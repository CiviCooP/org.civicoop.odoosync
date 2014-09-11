<?php

class CRM_OdooContributionSync_DAO_OdooContributionSettings extends CRM_Core_DAO {

  /**
   * static instance to hold the field values
   *
   * @var array
   * @static
   */
  static $_fields = null;

  /**
   * empty definition for virtual function
   */
  static function getTableName() {
    return 'civicrm_odoo_contribution_settings';
  }

  /**
   * returns all the column names of this table
   *
   * @access public
   * @return array
   */
  static function &fields() {
    if (!(self::$_fields)) {
      self::$_fields = array(
        'id' => array(
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true,
        ),
        'label' => array(
          'name' => 'label',
          'type' => CRM_Utils_Type::T_STRING,
          'required' => true,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
        ),
        'financial_type_id' => array(
          'name' => 'financial_type_id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true,
        ),
        'company_id' => array(
          'name' => 'company_id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true,
        ),
        'journal_id' => array(
          'name' => 'journal_id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true,
        ),
        'account_id' => array(
          'name' => 'account_id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true,
        ),
        'product_id' => array(
          'name' => 'product_id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true,
        ),
        'tax_id' => array(
          'name' => 'tax_id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true,
        ),
        'confirmed' => array(
          'name' => 'confirmed',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'required' => true,
        ),
      );
    }
    return self::$_fields;
  }

  /**
   * Returns an array containing, for each field, the arary key used for that
   * field in self::$_fields.
   *
   * @access public
   * @return array
   */
  static function &fieldKeys() {
    if (!(self::$_fieldKeys)) {
      self::$_fieldKeys = array(
        'id' => 'id',
        'label' => 'label',
        'financial_type_id' => 'financial_type_id',
        'company_id' => 'financial_type_id',
        'journal_id' => 'journal_id',
        'account_id' => 'account_id',
        'product_id' => 'product_id',
        'tax_id' => 'tax_id',
        'confirmed' => 'confirmed'
      );
    }
    return self::$_fieldKeys;
  }

}