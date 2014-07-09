<?php

class CRM_Odoosync_Connector {

  /**
   * Singleton
   * @var type 
   */
  protected static $instance;

  /**
   * Config parameters for Odoo
   * 
   * @var CRM_Odoosync_Config_OdooParameters 
   */
  protected $config;

  /**
   * Odoo userId
   * 
   * @var int 
   */
  protected $userId = false;
  protected $log = "";

  protected function __construct() {
    $this->config = CRM_Odoosync_Config_OdooParameters::singleton();
    $this->userId = $this->login();
  }

  /**
   * Singleton pattern
   */
  public static function singleton() {
    if (!self::$instance) {
      self::$instance = new CRM_Odoosync_Connector();
    }
    return self::$instance;
  }

  public function getUserId() {
    return $this->userId;
  }

  /**
   * Login into Odoo
   */
  protected function login() {
    $server_url = $this->config->getUrl();

    $sock = new xmlrpc_client($server_url . 'common');
    $msg = new xmlrpcmsg('login');
    $msg->addParam(new xmlrpcval($this->config->getDatabasename(), "string"));
    $msg->addParam(new xmlrpcval($this->config->getUsername(), "string"));
    $msg->addParam(new xmlrpcval($this->config->getPassword(), "string"));
    $resp = $sock->send($msg);
    $val = $resp->value();
    $id = $val->scalarval();
    if ($id > 0) {
      return $id;
    }

    $this->log('Could not login into Odoo');
    return false;
  }
  
  public function search($resource, $key) {
    $server_url = $this->config->getUrl();

    $client = new xmlrpc_client($server_url . 'object');
    $msg = new xmlrpcmsg('execute');
    $msg->addParam(new xmlrpcval($this->config->getDatabasename(), "string"));
    $msg->addParam(new xmlrpcval($this->getUserId(), "int"));
    $msg->addParam(new xmlrpcval($this->config->getPassword(), "string"));
    $msg->addParam(new xmlrpcval($resource, "string"));
    $msg->addParam(new xmlrpcval("search", "string"));
    $msg->addParam(new xmlrpcval($key, "array"));

    $resp = $client->send($msg);

    if ($resp->faultCode()) {
      $this->log('Error search a '.$resource.': '.$resp->faultCode(). ' (' . $resp->faultString().') with raw response: '.$resp->raw_data);
      return false;
    }
    
    return $resp->value()->scalarval();
  }
  
  public function read($resource, $id) {
    $server_url = $this->config->getUrl();

    $client = new xmlrpc_client($server_url . 'object');
    $msg = new xmlrpcmsg('execute');
    $msg->addParam(new xmlrpcval($this->config->getDatabasename(), "string"));
    $msg->addParam(new xmlrpcval($this->getUserId(), "int"));
    $msg->addParam(new xmlrpcval($this->config->getPassword(), "string"));
    $msg->addParam(new xmlrpcval($resource, "string"));
    $msg->addParam(new xmlrpcval("read", "string"));
    $msg->addParam(new xmlrpcval($id, "int"));

    $resp = $client->send($msg);

    if ($resp->faultCode()) {
      $this->log('Error search a '.$resource.': '.$resp->faultCode(). ' (' . $resp->faultString().') with raw response: '.$resp->raw_data);
      return false;
    }
    
    return $resp->value()->scalarval();
  }
  
  public function write($resource, $id, $parameters) {
    $server_url = $this->config->getUrl();

    $client = new xmlrpc_client($server_url . 'object');
    $msg = new xmlrpcmsg('execute');
    $msg->addParam(new xmlrpcval($this->config->getDatabasename(), "string"));
    $msg->addParam(new xmlrpcval($this->getUserId(), "int"));
    $msg->addParam(new xmlrpcval($this->config->getPassword(), "string"));
    $msg->addParam(new xmlrpcval($resource, "string"));
    $msg->addParam(new xmlrpcval("write", "string"));
    $msg->addParam(new xmlrpcval($id, "int"));
    $msg->addParam(new xmlrpcval($parameters, "struct"));

    $resp = $client->send($msg);

    if ($resp->faultCode()) {
      $this->log('Error search a '.$resource.': '.$resp->faultCode(). ' (' . $resp->faultString().') with raw response: '.$resp->raw_data);
      return false;
    }
    return $resp->value()->scalarval();
  }
  
  public function unlink($resource, $id) {
    $server_url = $this->config->getUrl();

    $client = new xmlrpc_client($server_url . 'object');
    $msg = new xmlrpcmsg('execute');
    $msg->addParam(new xmlrpcval($this->config->getDatabasename(), "string"));
    $msg->addParam(new xmlrpcval($this->getUserId(), "int"));
    $msg->addParam(new xmlrpcval($this->config->getPassword(), "string"));
    $msg->addParam(new xmlrpcval($resource, "string"));
    $msg->addParam(new xmlrpcval("unlink", "string"));
    $msg->addParam(new xmlrpcval(array(new xmlrpcval($id, "int")), "array"));

    $resp = $client->send($msg);

    if ($resp->faultCode()) {
      $this->log('Error search a '.$resource.': '.$resp->faultCode(). ' (' . $resp->faultString().') with raw response: '.$resp->raw_data);
      return false;
    }
    return $resp->value()->scalarval();
  }

  public function create($resource, $parameters) {
    $server_url = $this->config->getUrl();

    $client = new xmlrpc_client($server_url . 'object');
    $msg = new xmlrpcmsg('execute');
    $msg->addParam(new xmlrpcval($this->config->getDatabasename(), "string"));
    $msg->addParam(new xmlrpcval($this->getUserId(), "int"));
    $msg->addParam(new xmlrpcval($this->config->getPassword(), "string"));
    $msg->addParam(new xmlrpcval($resource, "string"));
    $msg->addParam(new xmlrpcval("create", "string"));
    $msg->addParam(new xmlrpcval($parameters, "struct"));

    $resp = $client->send($msg);

    if ($resp->faultCode()) {
      $this->log('Error creating a '.$resource.': '.$resp->faultCode(). ' (' . $resp->faultString().') with raw response: '.$resp->raw_data);
      return false;
    }
    
    return $resp->value()->scalarval();
  }

  protected function log($msg) {
    $this->log .= "\r\n\r\n" . $msg;
    CRM_Core_Error::debug_log_message($msg . "\r\n Odoo parameters: " . $this->config->getUsername() . ' into Openerp database ' . $this->config->getDatabasename() . ' at ' . $this->config->getUrl());
  }

  public function getLog() {
    return $this->log;
  }

}
