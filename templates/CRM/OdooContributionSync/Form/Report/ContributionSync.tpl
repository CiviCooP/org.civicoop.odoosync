{* Use the default layout *}
{include file="CRM/Report/Form.tpl"}

<script type="text/javascript">
    {literal}
    function restoreSync(id) {
        CRM.api3('Odoo', 'Resync', {
            'object_name': 'Contribution',
            'id': id
        });
    }

    {/literal}
</script>
