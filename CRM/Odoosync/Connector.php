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
  
  protected $lastResponse;

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
  
  protected function getClient($url) {
    $GLOBALS['xmlrpc_internalencoding']='UTF-8';
    $sock = new xmlrpc_client($url);
    $sock->request_charset_encoding="UTF-8";
    return $sock;
  }

  /**
   * Login into Odoo
   */
  protected function login() {
    $server_url = $this->config->getUrl();

    $sock = $this->getClient($server_url . 'common');
    $msg = new xmlrpcmsg('login');
    $msg->addParam(new xmlrpcval($this->config->getDatabasename(), "string"));
    $msg->addParam(new xmlrpcval($this->config->getUsername(), "string"));
    $msg->addParam(new xmlrpcval($this->config->getPassword(), "string"));
    
    $resp = $sock->send($msg);
    $this->setLastResponse($resp);
    
    $val = $resp->value();
    if ($val) {
      $id = $val->scalarval();
      if ($id > 0) {
        return $id;
      }
    }

    $this->log('Could not login into Odoo (raw response: '.$resp->raw_data.' )');
    return false;
  }
  
  public function search($resource, $key) {
    $server_url = $this->config->getUrl();

    $client = $this->getClient($server_url . 'object');
    $msg = new xmlrpcmsg('execute');
    $msg->addParam(new xmlrpcval($this->config->getDatabasename(), "string"));
    $msg->addParam(new xmlrpcval($this->getUserId(), "int"));
    $msg->addParam(new xmlrpcval($this->config->getPassword(), "string"));
    $msg->addParam(new xmlrpcval($resource, "string"));
    $msg->addParam(new xmlrpcval("search", "string"));
    $msg->addParam(new xmlrpcval($key, "array"));

    $resp = $client->send($msg);
    $this->setLastResponse($resp);

    if ($resp->faultCode()) {
      $this->log('Error search a '.$resource.': '.$resp->faultCode(). ' (' . $resp->faultString().') with raw response: '.$resp->raw_data);
      return false;
    }
    
    return $resp->value()->scalarval();
  }
  
  public function read($resource, $id) {
    $server_url = $this->config->getUrl();

    $client = $this->getClient($server_url . 'object');
    $msg = new xmlrpcmsg('execute');
    $msg->addParam(new xmlrpcval($this->config->getDatabasename(), "string"));
    $msg->addParam(new xmlrpcval($this->getUserId(), "int"));
    $msg->addParam(new xmlrpcval($this->config->getPassword(), "string"));
    $msg->addParam(new xmlrpcval($resource, "string"));
    $msg->addParam(new xmlrpcval("read", "string"));
    if (is_array($id)) {
      $msg->addParam(new xmlrpcval($id, "array"));
    } else {
      $msg->addParam(new xmlrpcval($id, "int"));
    }

    $resp = $client->send($msg);
    $this->setLastResponse($resp);

    if ($resp->faultCode()) {
      $this->log('Error read a '.$resource.': '.$resp->faultCode(). ' (' . $resp->faultString().') with raw response: '.$resp->raw_data."\r\nID: ".$id);
      return false;
    }
    
    return $resp->value()->scalarval();
  }
  
  public function write($resource, $id, $parameters) {
    $server_url = $this->config->getUrl();

    $client = $this->getClient($server_url . 'object');
    $msg = new xmlrpcmsg('execute');
    $msg->addParam(new xmlrpcval($this->config->getDatabasename(), "string"));
    $msg->addParam(new xmlrpcval($this->getUserId(), "int"));
    $msg->addParam(new xmlrpcval($this->config->getPassword(), "string"));
    $msg->addParam(new xmlrpcval($resource, "string"));
    $msg->addParam(new xmlrpcval("write", "string"));
    if (is_array($id)) {
      $msg->addParam(new xmlrpcval($id, "array"));
    } else {
      $msg->addParam(new xmlrpcval($id, "int"));
    }
    $msg->addParam(new xmlrpcval($parameters, "struct"));

    $resp = $client->send($msg);
    $this->setLastResponse($resp);

    if ($resp->faultCode()) {
      $this->log('Error write a '.$resource.': '.$resp->faultCode(). ' (' . $resp->faultString().') with raw response: '.$resp->raw_data."\r\nID: ".$id."\r\nParameters: ".  var_export($parameters, true));
      return false;
    }
    return $resp->value()->scalarval();
  }
  
  public function unlink($resource, $id) {
    $server_url = $this->config->getUrl();

    $client = $this->getClient($server_url . 'object');
    $msg = new xmlrpcmsg('execute');
    $msg->addParam(new xmlrpcval($this->config->getDatabasename(), "string"));
    $msg->addParam(new xmlrpcval($this->getUserId(), "int"));
    $msg->addParam(new xmlrpcval($this->config->getPassword(), "string"));
    $msg->addParam(new xmlrpcval($resource, "string"));
    $msg->addParam(new xmlrpcval("unlink", "string"));
    $msg->addParam(new xmlrpcval(array(new xmlrpcval($id, "int")), "array"));

    $resp = $client->send($msg);
    $this->setLastResponse($resp);

    if ($resp->faultCode()) {
      $this->log('Error unlink a '.$resource.': '.$resp->faultCode(). ' (' . $resp->faultString().') with raw response: '.$resp->raw_data."\r\nID: ".$id);
      return false;
    }
    return $resp->value()->scalarval();
  }

  public function create($resource, $parameters) {
    $server_url = $this->config->getUrl();

    $client = $this->getClient($server_url . 'object');
    $msg = new xmlrpcmsg('execute');
    $msg->addParam(new xmlrpcval($this->config->getDatabasename(), "string"));
    $msg->addParam(new xmlrpcval($this->getUserId(), "int"));
    $msg->addParam(new xmlrpcval($this->config->getPassword(), "string"));
    $msg->addParam(new xmlrpcval($resource, "string"));
    $msg->addParam(new xmlrpcval("create", "string"));
    $msg->addParam(new xmlrpcval($parameters, "struct"));

    $resp = $client->send($msg);
    $this->setLastResponse($resp);

    if ($resp->faultCode()) {
      $this->log('Error creating a '.$resource.': '.$resp->faultCode(). ' (' . $resp->faultString().') with raw response: '.$resp->raw_data."\r\nParameters: ".  var_export($parameters, true));
      return false;
    }
    
    return $resp->value()->scalarval();
  }
  
  public function exec_workflow($resource, $method, $id) {
    $server_url = $this->config->getUrl();

    $client = $this->getClient($server_url . 'object');
    $msg = new xmlrpcmsg('exec_workflow');
    $msg->addParam(new xmlrpcval($this->config->getDatabasename(), "string"));
    $msg->addParam(new xmlrpcval($this->getUserId(), "int"));
    $msg->addParam(new xmlrpcval($this->config->getPassword(), "string"));
    $msg->addParam(new xmlrpcval($resource, "string"));
    $msg->addParam(new xmlrpcval($method, "string"));
    $msg->addParam(new xmlrpcval($id, "int"));

    $resp = $client->send($msg);
    $this->setLastResponse($resp);

    if ($resp->faultCode()) {
      $this->log('Error exec_workflow a '.$resource.': '.$resp->faultCode(). ' (' . $resp->faultString().') with raw response: '.$resp->raw_data."\r\nID: ".$id);
      return false;
    }
    
    return $resp->value()->scalarval();
  }
  
  
  protected function setLastResponse($response) {
    $this->lastResponse = $response;
  }
  
  public function getLastResponseMessage() {
    return var_export($this->lastResponse, true);
  }

  protected function log($msg) {
    $this->log .= "\r\n\r\n" . $msg;
    CRM_Core_Error::debug_log_message($msg . "\r\n Odoo parameters: " . $this->config->getUsername() . ' into Openerp database ' . $this->config->getDatabasename() . ' at ' . $this->config->getUrl());
  }

  public function getLog() {
    return $this->log;
  }

}
