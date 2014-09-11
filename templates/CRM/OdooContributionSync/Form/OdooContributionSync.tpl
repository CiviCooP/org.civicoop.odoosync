<h3>{if $action eq 1}{ts}New contribution setting{/ts}{elseif $action eq 2}{ts}Edit contribution setting{/ts}{else}{ts}Delete contribution setting{/ts}{/if}</h3>
<div class="crm-block crm-form-block crm-job-form-block">
 <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>

{if $action eq 8}
  <div class="messages status no-popup">  
      <div class="icon inform-icon"></div>{ts}Do you want to continue?{/ts}
  </div>
{else}
  <table class="form-layout-compressed">
    <tr class="crm-sms-odoo_contribution_setting-form-block-label">
        <td class="label">{$form.label.label}</td><td>{$form.label.html}</td>
    </tr>
    <tr class="crm-sms-odoo_contribution_setting-form-block-financial_type_id">
        <td class="label">{$form.financial_type_id.label}</td><td>{$form.financial_type_id.html}</td>
    </tr>
    <tr class="crm-sms-odoo_contribution_setting-form-block-company_id">
        <td class="label">{$form.company_id.label}</td><td>{$form.company_id.html}</td>
    </tr>
    <tr class="crm-sms-odoo_contribution_setting-form-block-journal_id">
        <td class="label">{$form.journal_id.label}</td><td>{$form.journal_id.html}</td>
    </tr>
    <tr class="crm-sms-odoo_contribution_setting-form-block-account_id">
        <td class="label">{$form.account_id.label}</td><td>{$form.account_id.html}</td>
    </tr>
    <tr class="crm-sms-odoo_contribution_setting-form-block-product_id">
        <td class="label">{$form.product_id.label}</td><td>{$form.product_id.html}</td>
    </tr>
    <tr class="crm-sms-odoo_contribution_setting-form-block-tax_id">
        <td class="label">{$form.tax_id.label}</td><td>{$form.tax_id.html}</td>
    </tr>
    <tr class="crm-sms-odoo_contribution_setting-form-block-confirmed">
        <td class="label">{$form.confirmed.label}</td><td>{$form.confirmed.html}</td>
    </tr>
  </table>
{/if} 
</table>
       <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
  </fieldset>
</div>