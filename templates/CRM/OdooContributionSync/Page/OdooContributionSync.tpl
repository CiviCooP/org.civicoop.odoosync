{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/OdooContributionSync/Form/OdooContributionSync.tpl"}
{else}

{if $rows}
    {if $action ne 1 and $action ne 2}
        <div class="action-link">
        <a href="{crmURL q="action=add&reset=1"}" id="newSetting" class="button"><span><div class="icon add-icon"></div>{ts}Add New contribution setting{/ts}</span></a>
        </div>
    {/if}

<div id="help">
    {ts}Contribution settings are used to determine how to book the contribution into Odoo{/ts}
</div>

    <div id="ltype">
    {strip}
        <br/>
        <table class="selector">        
            <tr class="columnheader">
                <th >{ts}Label{/ts}</th>
                <th >{ts}Financial type{/ts}</th>
                <th >{ts}Company ID{/ts}</th>
                <th >{ts}Journal ID{/ts}</th>
                <th >{ts}Account ID{/ts}</th>
                <th >{ts}Product ID{/ts}</th>
                <th >{ts}Action{/ts}</th>
            </tr>
            {foreach from=$rows item=row}
                <tr id="row_{$row.id}" class="crm-odoo_contribution_setting {cycle values="odd-row,even-row"} {$row.class}">
                    <td class="crm-odoo_contribution_setting-label">{$row.label}</td>
                    <td class="crm-odoo_contribution_setting-financial_type">{$row.financial_type_id}</td>
                    <td class="crm-odoo_contribution_setting-company_id">{$row.company_id}</td>
                    <td class="crm-odoo_contribution_setting-journal_id">{$row.journal_id}</td>
                    <td class="crm-odoo_contribution_setting-account_id">{$row.account_id}</td>
                    <td class="crm-odoo_contribution_setting-product_id">{$row.product_id}</td>
                    <td>{$row.action|replace:'xx':$row.id}</td>
                </tr>
            {/foreach}
        </table>
    {/strip}
    </div>

{elseif $action ne 1}
    <div class="messages status no-popup">
      <div class="icon inform-icon"></div>
        {ts}There are no settings configured yet.{/ts}
     </div>
    <div class="action-link">
    <a href="{crmURL q="action=add&reset=1"}" id="newSetting" class="button"><span><div class="icon add-icon"></div>{ts}Add New contribution setting{/ts}</span></a>
    </div>

{/if}
{/if}