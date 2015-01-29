<?php

class CRM_OdooContributionSync_CreditInvoice {

  /**
   *
   * @var CRM_Odoosync_Connector
   */
  protected $connector;

  public function __construct() {
    $this->connector = CRM_Odoosync_Connector::singleton();
  }

  public function credit($odoo_invoice_id, DateTime $date) {
    $invoice = $this->connector->read($this->getOdooResourceType(), $odoo_invoice_id);

    $refund_invoice_id = $this->createCreditInvoice($invoice, $date);
    if (!$this->convertInvoiceLineToCreditInvoiceLine($invoice, $refund_invoice_id)) {
      $this->connector->unlink($this->getOdooResourceType(), $refund_invoice_id);
      throw new Exception('Could not convert invoice lines to credit invoice lines');
    }
    $this->connector->exec_workflow($this->getOdooResourceType(), 'invoice_open', $refund_invoice_id);

    $refund_invoice = $this->connector->read($this->getOdooResourceType(), $refund_invoice_id);
    
    if ($invoice['state']->scalarval() != 'paid') {
      if (!$this->reconcile($invoice, $refund_invoice)) {
        return false; //do not throw an exception, the reconciliation should be done in OpenERP manually
      }
    
      //set invoice status to paid
      $update_paid_ids = array(
        new xmlrpcval($refund_invoice_id, 'int'), 
        new xmlrpcval($odoo_invoice_id, 'int')
      );
      $update_paid_parameters['state'] = new xmlrpcval('paid', 'string');
      $this->connector->write($this->getOdooResourceType(), $update_paid_ids, $update_paid_parameters);
    }
    
    return $refund_invoice_id;
  }
  
  protected function getOdooResourceType() {
    return 'account.invoice';
  }

  protected function reconcile($invoice, $refund_invoice) {
    $utils = CRM_OdooContributionSync_Utils::singleton();
    $account_id = $this->getIdAttributeFromInvoice($invoice, 'account_id');
    
    $invoice_move_id = $this->getIdAttributeFromInvoice($invoice, 'move_id');
    $debit_move_line_id = $utils->getMoveLineToAccount($account_id, $invoice_move_id);
    if ($debit_move_line_id === false) {
      return false;
    }
    
    $refund_move_id = $this->getIdAttributeFromInvoice($refund_invoice, 'move_id');
    $credit_move_line_id = $utils->getMoveLineToAccount($account_id, $refund_move_id);
    if ($credit_move_line_id === false) {
      return false;
    }
    
    //create reconcilation
    $reconcile['name'] = new xmlrpcval($invoice['reference']->scalarval(), 'string');
    $reconcile['type'] = new xmlrpcval('manual', 'string');
    $reconcile_id = $this->connector->create('account.move.reconcile', $reconcile);
    if ($reconcile_id === false) {
      return false;
    }
    
    $update_move_line_ids = array(
      new xmlrpcval($credit_move_line_id, 'int'), 
      new xmlrpcval($debit_move_line_id, 'int')
    );
    $update_move_line_parameters['reconcile_id'] = new xmlrpcval($reconcile_id, 'int');
    $this->connector->write('account.move.line', $update_move_line_ids, $update_move_line_parameters);
    
    return true;
  }

  protected function convertInvoiceLineToCreditInvoiceLine($invoice, $refund_invoice_id) {
    $count = 0;
    foreach ($invoice['invoice_line']->scalarval() as $invoice_line_res) {
      $invoice_line_id = $invoice_line_res->scalarval();
      $invoice_line = $this->connector->read('account.invoice.line', $invoice_line_id);

      $line = array();
      $line['quantity'] = new xmlrpcval(1, 'int');

      $taxes = array();
      foreach ($invoice_line['invoice_line_tax_id']->scalarval() as $tax_id_res) {
        $taxes[] = new xmlrpcval($tax_id_res->scalarval(), 'int');
      }
      $tax = array(new xmlrpcval(array(
          new xmlrpcval(6, "int"), // 6 : id link
          new xmlrpcval(0, "int"),
          new xmlrpcval($taxes, "array")
            ), "array"));
      $line['invoice_line_tax_id'] = new xmlrpcval($tax, 'array');
      $line['name'] = new xmlrpcval($invoice['reference']->scalarval(), 'string');
      $line['price_unit'] = new xmlrpcval($invoice_line['price_unit']->scalarval(), 'double');
      $line['product_id'] = new xmlrpcval($invoice_line['product_id']->arraymem(0)->scalarval(), 'int'); //do we need product id?
      $line['invoice_id'] = new xmlrpcval($refund_invoice_id, 'int');
      if ($invoice_line['account_id']->scalarval()) {
        $line['account_id'] = new xmlrpcval($invoice_line['account_id']->scalarval(), 'int');
      }

      $refund_line_id = $this->connector->create('account.invoice.line', $line);
      if ($refund_line_id === false) {
        return false;
      }

      $count ++;
    }

    if ($count) {
      return true;
    }
    return false;
  }

  protected function createCreditInvoice($invoice, DateTime $date) {
    $utils = CRM_OdooContributionSync_Utils::singleton();
    $journal_id = $utils->getCreditJournalId();
    if (!$journal_id) {
      throw new Exception('Refund journal not found');
    }

    $parameters = array();
    $parameters['type'] = new xmlrpcval('out_refund', 'string');
    $parameters['journal_id'] = new xmlrpcval($journal_id, 'int');
    $parameters['account_id'] = new xmlrpcval($this->getIdAttributeFromInvoice($invoice, 'account_id'), 'int');
    $parameters['partner_id'] = new xmlrpcval($this->getIdAttributeFromInvoice($invoice, 'partner_id'), 'int');
    $parameters['reference'] = new xmlrpcval($invoice['reference']->scalarval(), 'string');
    $parameters['company_id'] = new xmlrpcval($this->getIdAttributeFromInvoice($invoice, 'company_id'), 'int');
    $parameters['date_invoice'] = new xmlrpcval($date->format('Y-m-d'), 'string');

    //set currency
    if (isset($invoice['currency_id'])) {
      $parameters['currency_id'] = new xmlrpcval($invoice['currency_id']->scalarval(), 'int');
    }

    $credit_invoice_id = $this->connector->create($this->getOdooResourceType(), $parameters);
    if ($credit_invoice_id) {
      return $credit_invoice_id;
    }
    throw new Exception('Could not create credit invoice');
  }

  protected function getIdAttributeFromInvoice($invoice, $attribute) {
    if (!isset($invoice[$attribute])) {
      throw new Exception('Could not find ID Attribute: '.$attribute);
    }
    $id_obj = $invoice[$attribute]->scalarval();
    if (!isset($id_obj[0])) {
      throw new Exception('Could not find ID Attribute: '.$attribute);
    }
    $id = $id_obj[0];
    return $id->scalarval();
  }

}
