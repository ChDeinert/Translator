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
    {icon type="config" size="small"}
    <h3>{gt text="Configure Modules to translate"}</h3>
</div>

<form class="z-form" id="configForm" action="{modurl modname='Translator' type='admin' func='storeConfigModules'}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
    <table class="z-datatable">
        <thead>
            <tr>
                <th>{gt text='ID'}</th>
                <th>{gt text='Modulename'}</th>
                <th>{gt text='Active for Translation'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$availableModules item='item'}
                <tr class="{cycle values="z-odd,z-even"}">
                    <td>{$item.id}</td>
                    <td>{$item.name}</td>
                    <td align="center">
                        <input type="checkbox" name="modules[]" id="modules" value="{$item.id}" {if ($item.active)}checked="checked"{/if}/>
                    </td>
                </tr>
            {foreachelse}
                <tr class="z-datatableempty"><td colspan="3">{gt text="No items found."}</td></tr>
            {/foreach}
        </tbody>
    </table>

    <div class="z-buttonrow z-buttons z-center">
        {button id=configForm|cat:'_submit' type='submit' src='button_ok.png' class='z-btgreen' set='icons/extrasmall' __alt='Save' __title='Save' __text='Save'}
        <a href="{modurl modname='Translator' type='admin' func='configModules'}" class="z-btred">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
    </div>
</form>

{adminfooter}
