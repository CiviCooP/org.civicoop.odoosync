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
