org.civicoop.odoosync
=====================

Synchronisation between CiviCRM and Odoo

**This extension is developed for one specific client** so you should to tweak to 
your own needs and use it at your own risk.

It is a one-way synchronisation. So only data from civicrm to Odoo is 
synchronised.

Configuration
-------------

After you have installed the extension do not forget to add your settings to `civicrm.settings.php`:

    <?php
    // sites/default/civicrm.settings.php:
    // ...
    // ...
    // At the bottom add:
    global $odoo_settings;
    $odoo_settings['url'] = 'http://your.odoo:8069/xmlrpc/';
    $odoo_settings['databasename'] = 'databasename';
    $odoo_settings['username'] = 'username';
    $odoo_settings['password'] - 'password';
    $odoo_settings['view_partner_url'] = 'http://your.odoo:8069/?db=databasename#id={partner_id}&view_type=form&model=res.partner&action=569';
    $odoo_settings['view_invoice_url'] = 'http://your.odoo:8069/?db=databasename#id={partner_id}&view_type=form&model=res.invoice&action=456';

Sync specification
------------------

Only Individuals and Organisations are synchronised. Organisations are stored as 
companies in Odoo.
At contact level only the name and the prefix is synchronised.

Odoo has its own data model meaning that every partner can only have one address, 
one e-mail adress, one phone number, one fax and one mobile number there fore we
have the following rules for synchronisation applied:

- Only primary addresses are synchronised into Odoo
- Only primary e-mailadresses are synchronised into Odoo
- Only phone numbers of type Phone, Mobile and Fax are synchronised into Odoo

Available hooks for developers
------------------------------

See See the [hooks](docs/hooks.md)