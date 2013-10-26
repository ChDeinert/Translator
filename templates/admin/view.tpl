{ajaxheader modname=$modinfo.name filename='mcspool.js' ui=true}
{strip}
{insert name='csrftoken' assign='csrftoken'}
{pageaddvarblock}
<script type="text/javascript">
	document.observe("dom:loaded", function() {
		Zikula.UI.Tooltips($$('.tooltips'));
	});
</script>
{/pageaddvarblock} {/strip} {adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="view" size="small"}
    <h3>{gt text="Avaiable Translations"}</h3>
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
    
    <form class="z-form" id="configForm" action="{modurl modname='Translator' type='admin' func='view'}" method="get" enctype="application/x-www-form-urlencoded">
        <input type="hidden" name="sort" value="{$sort}"/>
        <input type="hidden" name="sortdir" value="{$sortdir}"/>
        
        <ul class="z-menulinks">
            <li>
                {gt text='Items per page'}:
                <select name="itemsperpage" id="itemsperpage" onchange="$('submit').click();">
                    <option {if ($itemsperpage == 25)}selected="selected"{/if}>25</option>
                    <option {if ($itemsperpage == 50)}selected="selected"{/if}>50</option>
                    <option {if ($itemsperpage == 75)}selected="selected"{/if}>75</option>
                    <option {if ($itemsperpage == 100)}selected="selected"{/if}>100</option>
                    <option {if ($itemsperpage == 250)}selected="selected"{/if}>250</option>
                    <option {if ($itemsperpage == 500)}selected="selected"{/if}>500</option>
                    <option value="-1"{if ($itemsperpage == -1)}selected="selected"{/if}>{gt text='All'}</option>
                </select>
            </li>
            <li>
                {gt text='Moduletranslations'}
                <select name="mod" id="mod" onchange="$('submit').click();">
                    <option></option>
                    {foreach from=$awl_modules item='awl_module'}
                        <option value="{$awl_module.mod_id}" {if ($awl_module.mod_id == $mod)}selected="selected"{/if}>{$awl_module.modname}</option>
                    {/foreach}
                </select>
            </li>
        </ul>
        <input type="submit" class="z-hide" id="submit"/>
    </form>
</div>

<table class="z-datatable">
    <thead>
        <tr>
            <th>
                {sortlink __linktext='ID' sort='trans_id' currentsort=$sort sortdir=$sortdir modname='Translator' type='admin' func='view' itemsperpage=$itemsperpage}
            </th>
            <th>
                {sortlink __linktext='Sourcestring' sort='sourcestring' currentsort=$sort sortdir=$sortdir modname='Translator' type='admin' func='view' itemsperpage=$itemsperpage}
            </th>
            <th>{gt text='Languages'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$items item='item'}
            <tr class="{cycle values="z-odd,z-even"}">
                <td>{$item.trans_id}</td>
                <td>{$item.sourcestring}</td>
                <td align="center">
                    {foreach from=$translationLanguages item='language'}
                        {$language}
                        {if ($item.translationAvaiable[$language])}
                            {img modname='Translator' src='icons/16x16/checkbox-checked.png'}
                        {else}
                            {img modname='Translator' src='icons/16x16/checkbox-unchecked.png'}
                        {/if}
                    {foreachelse}
                        {gt text='No Language configured.'}
                    {/foreach}
                </td>
            </tr>
        {foreachelse}
            <tr class="z-datatableempty"><td colspan="6">{gt text="No items found."}</td></tr>
        {/foreach}
    </tbody>
</table>

{pager rowcount=$count limit=$itemsperpage posvar='startnum' shift=1 maxpages="30"}

{adminfooter}
