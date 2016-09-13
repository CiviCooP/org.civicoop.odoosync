{capture assign="odoo_status"}{strip}
<table class="no-border">
    <tbody>
    <tr>
        <td class="section-shown form-item">
            <div class="crm-accordion-wrapper">
                <div class="crm-accordion-header">{ts}Odoo information{/ts}</div>
                <div class="crm-accordion-body">
                    <table class="crm-info-panel">
                        <tbody>
                        <tr>
                            <td class="label">{ts}Odoo sync status{/ts}</td>
                            <td class="html-adjust">{$odoo_sync_status}</td>
                        </tr>
                        {if ($odoo_is_resyncable)}
                        <tr>
                            <td class="label"></td>
                            <td class="html-adject">
                                <a href="#" class="odoo button" title="Restore Sync" onclick="restoreSync({$contribution_id}); return false;">
                                    <span>{ts}Restore sync{/ts}</span>
                                </a>
                            </td>
                        </tr>
                        {/if}
                        </tbody>
                    </table>
                </div>
            </div>
        </td>
    </tr>
    </tbody>
</table>
{/strip}{/capture}

<script type="text/javascript">
    {literal}
    cj(function() {
        cj('.crm-contribution-view-form-block  > table.crm-info-panel').after('{/literal}{$odoo_status|replace:"'":"\'"}{literal}');
    });

    function restoreSync(id) {
        CRM.api3('Odoo', 'Resync', {
            'object_name': 'Contribution',
            'id': id
        }).done(function(result) {
            if (typeof result.is_error === 'undefined' || !result.is_error) {
                location.reload();
            }
        });
    }

    {/literal}
</script>