org.civicoop.odoosync
=====================

Synchronisation between CiviCRM and Odoo

**This extension is developed for one specific client** so you should to tweak to 
your own needs and use it at your own risk.

It is a one-way synchronisation. So only data from civicrm to Odoo is 
synchronised.

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
