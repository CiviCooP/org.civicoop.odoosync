<div class="crm-block crm-form-block crm-odoo-form-block">

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="top"}
</div>


{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">
        {if $elementName == 'url'}
        <p class="description">{ts}E.g. http://localhost:8069/xmlrpc/{/ts}</p>
        {/if}
        {if $elementName == 'view_partner_url'}
        <p class="description">{ts}{literal}E.g. http://localhost:8069/?db=sp#id={partner_id}&view_type=form&model=res.partner&action=569<br>Use {partner_id} to insert the ID of the partner{/literal}{/ts}</p>
        {/if}
        {if $elementName == 'view_invoice_url'}
            <p class="description">{ts}{literal}E.g. http://localhost:8069/?db=sp#id={invoice_id}&view_type=form&model=res.partner&action=569<br>Use {invoice_id} to insert the ID of the invoice{/literal}{/ts}</p>
        {/if}
        {$form.$elementName.html}
    </div>
    <div class="clear"></div>
  </div>
{/foreach}

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

</div>
