<?php
/**
 * Copy and change the settings below to your civicrm.settings.php
 */

global $odoo_settings;
$odoo_settings['url'] = 'http://your.odoo:8069/xmlrpc/';
$odoo_settings['databasename'] = 'databasename';
$odoo_settings['username'] = 'username';
$odoo_settings['password'] - 'password';
$odoo_settings['view_partner_url'] = 'http://your.odoo:8069/?db=databasename#id={partner_id}&view_type=form&model=res.partner&action=569';
$odoo_settings['view_invoice_url'] = 'http://your.odoo:8069/?db=databasename#id={partner_id}&view_type=form&model=res.invoice&action=456';