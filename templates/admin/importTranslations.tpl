{ajaxheader modname=$modinfo.name filename='mcspool.js' ui=true}
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
    <h3>{gt text="Import Translations"}</h3>
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
            <th>{gt text='Files'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$importmodules item='module'}
            <tr class="{cycle values='z-odd,z-even'}">
                <td>{$module.moddesc}</td>
                <td class="z-center z-buttons">
                    {foreach from=$module.files item='file'}
                        <a href="{modurl modname='Translator' type='admin' func='importFromFile' mod_id=$module.mod_id file=$file.file filetype=$file.type language=$file.language}">
                            {gt text='Import from %s-file' tag1=$file.type}
                            {if ($file.language != '')}
                                {gt text='Language: '} {$file.language}
                            {/if}
                        </a>
                    {/foreach}
                </td>
            </tr>
        {/foreach}
    </tbody>
</table>

{adminfooter}
