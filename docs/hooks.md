# Avaiable hooks

## hook_civicrm_odoo_object_definition

This hook returns a list with object definitions. A object definition is a definition
of a civicrm entity and how this could be synced with Odoo. 

**Return values**

Returns an array with classes which implement the CRM_Odoosync_Model_ObjectDefinitionInterface

**Example**

    function odoosync_civicrm_odoo_object_definition(&$list) {
        $list['civicrm_contact'] = new CRM_Odoosync_Model_ContactDefinition();
    }

## hook_civicrm_odoo_alter_parameters

This hook is useful to alter parameters before updating or inserting an object into Odoo. E.g. when a specific implementation of Odoo has specific fields which are also available in CivICRM

**Return values**

The parameter array can be altered in this hook

**Example**

    function odoosync_civicrm_odoo_alter_parameters(&$parameters, $entity, $entity_id, $action) {
        if ($entity == 'civicrm_contact') {
            $contact = civicrm_api3('Contact', 'getsingle', array('id' => $entity_id));
            if ($contact['contact_type'] == 'Individual') {
                $parameters['is_company'] = new xmlrpcval(true, 'boolean');
            }
        }
    }

## hook_civicrm_odoo_synchronisator

This hook is useful to define custom synchronisator classes for certain entities.
E.g. at one client they have implemented Odoo in such away that they can store multiple addresses
so rather then syncing only the primary addresses this hook returns a synchronisator class which could also 
sync non-primary addresses

**Return values**

The name of the synchronisator class can be altered

**Example**

    function odoosync_civicrm_odoo_synchronisator(CRM_Odoosync_Model_ObjectDefinition $objectDefinition, &$synchronisator) {
        if ($objectDefinition instanceof CRM_OdooContactSync_AddressDefinition) {
            $synchronisator = 'CRM_MyOdooSync_AddressSynchronisator';
        }
    }