{capture assign="odoo_button"}{strip}
    <a href="{$link_to_odoo}" class="odoo button" title="View in Odoo">
        <span>{ts}View in Odoo{/ts}</span>
    </a>
{/strip}{/capture}

<script type="text/javascript">
{literal}
cj(function() {
  cj('.crm-contribution-view-form-block .crm-submit-buttons').each(function(index, item) {
      cj(item).find('a:last').after('{/literal}{$odoo_button}{literal}');
  });
});
{/literal}
</script>