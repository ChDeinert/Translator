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

<div class="z-content-pagetitle">
    <h2>{gt text="Select Module to Translate"}</h2>
</div>

{insert name='getstatusmsg'}

<div>
  <ul class="z-menulinks"></ul>
</div>

<div>
  <table class="z-datatable">
    <thead>
      <tr>
        <th>{gt text='Module'}</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$available_modules item='module'}
        <tr class="{cycle values='z-odd,z-even'}">
          <td class="z-center">{$module.displayname}</td>
          <td><a href="{modurl modname=$modinfo.name type='User' func='editTranslations' mod_id=$module.id}" class="translator-icon-play"></a></td>
        </tr>
      {foreachelse}
        <tr class="z-datatableempty">
          <td colspan="2">
            {gt text='No Modules found!'}
          </td>
        </tr>
      {/foreach}
    </tbody>
  </table>
</div>

{include file='user/partials/footer.tpl'}
