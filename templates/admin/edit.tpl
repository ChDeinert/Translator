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
    
    <form class="z-form" id="configForm" action="{modurl modname='Translator' type='admin' func='edit'}" method="get" enctype="application/x-www-form-urlencoded">
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

<form class="z-form" id="translationForm" action="{modurl modname='Translator' type='admin' func='store'}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="searchfor" value="{$searchfor}"/>
    <input type="hidden" name="searchby" value="{$searchby}"/>
    <input type="hidden" name="sort" value="{$sort}"/>
    <input type="hidden" name="sortdir" value="{$sortdir}"/>
    <input type="hidden" name="itemsperpage" value="{$itemsperpage}"/>
    <input type="hidden" name="startnum" value="{$startnum}"/>
    <input type="hidden" name="mod" value="{$mod}"/>
    
    <table class="z-datatable">
        <thead>
            <tr>
                <th>
                    {sortlink __linktext='ID' sort='trans_id' currentsort=$sort sortdir=$sortdir modname='Translator' type='admin' func='edit' itemsperpage=$itemsperpage mod=$mod}
                </th>
                <th>
                    {sortlink __linktext='Sourcestring' sort='sourcestring' currentsort=$sort sortdir=$sortdir modname='Translator' type='admin' func='edit' itemsperpage=$itemsperpage mod=$mod}
                </th>
                {foreach from=$translationLanguages item='translang'}
                    <th>{gt text='Targetstring'} {$translang}</th>
                {/foreach}
            </tr>
        </thead>
        <tbody>
            {foreach from=$items item='item'}
                <tr class="{cycle values="z-odd,z-even"}">
                    <td>{$item.trans_id}</td>
                    <td align="right">
                        {$item.sourcestring}
                    </td>
                    {foreach from=$translationLanguages item='translang'}
                        <td>
                            <input class="z-hide" type="checkbox" name="upd_targetstring_{$translang}[]" id="upd_targetstring_{$item.trans_id}_{$translang}" value="{$item.trans_id}||{$translang}"/>
                            <input type="text" name="targetstring_{$item.trans_id}_{$translang}" value="{$item.translations[$translang]}" size="50" onchange="$('upd_targetstring_{$item.trans_id}_{$translang}').checked=true;"/>
                        </td>
                    {/foreach}
                </tr>
            {foreachelse}
                <tr class="z-datatableempty"><td colspan="6">{gt text="No items found."}</td></tr>
            {/foreach}
        </tbody>
    </table>
    
    <div class="z-buttonrow z-buttons z-center">
        {button id=translationForm|cat:'_submit' type='submit' src='button_ok.png' class='z-btgreen' set='icons/extrasmall' __alt='Save' __title='Save' __text='Save'}
        <a href="{modurl modname='Translator' type='admin' func='edit'}" class="z-btred">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
    </div>
</form>

{pager rowcount=$count limit=$itemsperpage posvar='startnum' shift=1 maxpages="30"}

{adminfooter}
