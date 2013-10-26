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
    {icon type="search" size="small"}
    <h3>{gt text="New Translationstrings"}</h3>
</div>

<table class="z-datatable">
    <thead>
        <tr>
            <th>{gt text='Sourcestring'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$items item='item' key='sourcestring'}
            <tr class="{cycle values="z-odd,z-even"}">
                <td>{$sourcestring}</td>
            </tr>
        {foreachelse}
            <tr class="z-datatableempty"><td colspan="6">{gt text="No items found."}</td></tr>
        {/foreach}
    </tbody>
</table>

<div class="z-buttons">
    <a href="{modurl modname='Translator' type='admin' func='edit'}" class="z-btgreen z-bt-ok">{gt text='OK'}</a>
</div>

{adminfooter}
