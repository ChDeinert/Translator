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
    {icon type="locale" size="small"}
    <h3>{gt text="Configuration of languages to translate"}</h3>
</div>

<form class="z-form" id="configForm" action="{modurl modname='Translator' type='admin' func='storeLanguages'}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
    <table class="z-datatable">
        <thead>
            <tr>
                <th></th>
                <th>{gt text='Langcode'}</th>
                <th>{gt text='Language'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$allLanguages item='language' key='langcode'}
                <tr class="{cycle values="z-odd,z-even"}">
                    <td>
                        <input type="checkbox" name="translationLanguages[]" id="translationLanguages" value="{$langcode}" {if ($language.selected)}checked="checked"{/if}/>
                    </td>
                    <td>{$langcode}</td>
                    <td>{$language.desc}</td>
                </tr>
            {foreachelse}
                <tr class="z-datatableempty"><td colspan="3">{gt text="No items found."}</td></tr>
            {/foreach}
        </tbody>
    </table>

    <div class="z-buttonrow z-buttons z-center">
        {button id=configForm|cat:'_submit' type='submit' src='button_ok.png' class='z-btgreen' set='icons/extrasmall' __alt='Save' __title='Save' __text='Save'}
        <a href="{modurl modname='Translator' type='admin' func='config'}" class="z-btred">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
    </div>
</form>

{adminfooter}
