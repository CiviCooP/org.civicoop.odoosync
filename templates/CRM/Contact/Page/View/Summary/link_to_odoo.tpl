{capture assign="odoo_button"}{strip}
<li class="crm-odoo-action crm-contact-odoo">
    <a href="{$link_to_odoo}" class="odoo button" title="View in Odoo">
        <span>{ts}View in Odoo{/ts}</span>
    </a>
</li>
{/strip}{/capture}


<script type="text/javascript">
{literal}
cj(function() {
  cj('li.crm-summary-block').after('{/literal}{$odoo_button}{literal}');
}); 
{/literal}
</script>