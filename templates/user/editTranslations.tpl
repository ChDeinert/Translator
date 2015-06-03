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
    <h2>{gt text="Translation of the Module"} <small>{$modinformations.displayname}</small></h2>
</div>

{insert name='getstatusmsg'}

<div>
  <ul class="z-menulinks">
    <li><a href="{modurl modname=$modinfo.name type='User' func='viewModules'}" class="translator-icon-back">{gt text='Back'}</a></li>
    <li><a href="{modurl modname=$modinfo.name type='User' func='searchTranslations' mod_id=$mod_id}" class="z-icon-es-search">{gt text='Scan for untranslated Strings'}</a></li>
    <li><a href="{modurl modname=$modinfo.name type='User' func='exportTranslation' mod_id=$mod_id target='po'}" class="z-icon-es-export">{gt text='Export Translations'}</a></li>
    <li><a href="{modurl modname=$modinfo.name type='User' func='exportTranslation' mod_id=$mod_id target='pot'}" class="z-icon-es-export">{gt text='Export Translation Template'}</a></li>
    {foreach from=$importable_files item='file'}
        <li><a href="{modurl modname=$modinfo.name type='User' func='importTranslation' mod_id=$mod_id file=$file.file language=$file.language filetype=$file.type}" class="z-icon-es-import">{gt text='Import from %s file' tag1=$file.type}{if ($file.type != 'pot')}({gt text='Language: %s' tag1=$file.language}){/if}</a></li>
    {/foreach}
  </ul>
</div>

<div class="z-form">
  <fieldset>
    <legend>{gt text='Language'}</legend>
    <div class="z-buttonrow z-buttons z-center">
      {foreach from=$languages item='lang'}
        <a href="{modurl modname=$modinfo.name type='User' func='editTranslations' mod_id=$mod_id translation_language=$lang}" {if ($lang == $translation_language)}class="active"{/if}>
          <img src="/images/flags/flag-{$lang}.png" style="width:20px;height:15px;"> {$lang}
        </a>
      {/foreach}
    </div>
  </fieldset>
</div>

<div>
  <form class="z-form" id="translationForm" action="{modurl modname='Translator' type='User' func='saveTranslations'}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="mod_id" value="{$mod_id}" />
    <input type="hidden" name="translation_language" value="{$translation_language}" />
    
    <table class="z-datatable">
      <thead>
        <tr>
          <th width="50%">{gt text='Untranslated'} ({$translation_count})</th>
          <th width="50%">{gt text='Translated'}</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$translations item='translation'}
          <tr class="{cycle values='z-odd,z-even'}">
            <td>{$translation.sourcestring|htmlentities}</td>
            <td class="z-center">
              <input type="text" name="translations[{$translation.id}]" id="translation_{$translation.id}" value="{$translation.targetstring}" style="width:90%"/>
            </td>
          </tr>
        {foreachelse}
          <tr class="z-datatableempty">
            <td colspan="2">
              <p>{gt text='No Translations available. Please scan the module to see if there are String to translate!'}</p>
              <a href="{modurl modname=$modinfo.name type='User' func='searchTranslations' mod_id=$mod_id}" class="z-button">
                <i class="z-icon-es-search"></i>{gt text='Scan for untranslated Strings'}
              </a>
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>
    <div class="z-buttons z-buttonrow z-center">
      {button id=translationForm|cat:'_submit' type='submit' src='button_ok.png' class='z-btgreen' set='icons/extrasmall' __alt='Save' __title='Save' __text='Save'}
      <a href="{modurl modname=$modinfo.name type='User' func='viewModules'}" class="z-btred">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
    </div>
  </form>
</div>

{include file='user/partials/footer.tpl'}
