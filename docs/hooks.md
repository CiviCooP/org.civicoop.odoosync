# Avaiable hooks

## hook_civicrm_odoo_object_definition

This hook returns a list with object definitions. A object definition is a definition
of a civicrm entity and how this could be synced with Odoo. 

**Return values**

Returns an array with classes which implement the CRM_Odoosync_Model_ObjectDefinitionInterface

**Example**

    function odoosync_civicrm_odoo_object_definition() {
        $list[] = new CRM_Odoosync_Model_ContactDefinition();
        return $list;
    }

