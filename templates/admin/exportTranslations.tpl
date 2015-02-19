{ajaxheader modname=$modinfo.name filename='translator.js' ui=true}
{strip}
{insert name='csrftoken' assign='csrftoken'}
{pageaddvarblock}
<script type="text/javascript">
	document.observe("dom:loaded", function() {
		Zikula.UI.Tooltips($$('.tooltips'));
	});
</script>
{/pageaddvarblock}
{/strip}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="view" size="small"}
    <h3>{gt text="Export Translations"}</h3>
</div>

<div class="z-clearfix">
    <ul class="z-menulinks">
        {foreach from=$links item="link"}
            <li>
                <a href="{$link.url}" class="z-iconlink {$link.class}">
                    {if ($smarty.server.REQUEST_URI == "/`$link.url`")}
                        <b>{$link.text}</b>
                    {else}
                        {$link.text}
                    {/if}
                </a>
            </li>
        {/foreach}
    </ul>
</div>

<table class="z-datatable">
    <thead>
        <tr>
            <th>{gt text='Module'}</th>
            <th>{gt text='All languages'}</th>
            {foreach from=$translationLanguages item='language'}
                <th>{$language}</th>
            {/foreach}
        </tr>
    </thead>
    <tbody>
        {foreach from=$modules item='module'}
            <tr class="{cycle values='z-odd,z-even'}">
                <td>{$module.moddesc}</td>
                <td class="z-center z-buttons">
                    <a href="{modurl modname='Translator' type='admin' func='export2pot' mod_id=$module.mod_id}">
                        <span class="z-icon-es-export"></span>
                        {gt text='Export to .pot-File'}
                    </a>
                    <a href="{modurl modname='Translator' type='admin' func='export2po' mod_id=$module.mod_id}">
                        <span class="z-icon-es-export"></span>
                        {gt text='Export to .po-File'}
                    </a>
                </td>
                {foreach from=$translationLanguages item='language'}
                    <td class="z-center z-buttons">
                        <a href="{modurl modname='Translator' type='admin' func='export2po' mod_id=$module.mod_id language=$language}">
                            <span class="z-icon-es-export"></span>
                            {gt text='Export to .po-File'}
                        </a>
                    </td>
                {/foreach}
            </tr>
        {/foreach}
    </tbody>
</table>

{adminfooter}
