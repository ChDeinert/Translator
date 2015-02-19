{ajaxheader modname=$modinfo.name filename='translator.js' ui=true}
{strip}
{insert name='csrftoken' assign='csrftoken'}
{pageaddvarblock}
<script type="text/javascript">
    var mod_id = {{$mod_id}};
    var files = {{$files_to_search_in}};
    var len = files.length;
    var i = 0;
    
	document.observe("dom:loaded", function() {
        processFile();
	});
</script>
{/pageaddvarblock} 
{/strip}

<div class="z-content-pagetitle">
  <h2>{gt text="Scanning the Module for Translation strings"} <small>{$modinformations.displayname}</small></h2>
</div>

<div>
  <div class="z-center">
    <h3>{gt text='Current file:'}</h3>
    <span id="currentfile">&nbsp;</span>
  </div>

  <div align="center" style="width: 100%;">
    <div class="translator-progress-bar translator-green translator-shine" align="left">
      <span style="width:0%;" id="progbar">
      </span>
    </div>
  </div>
  <div align="center" id="percentage">0 %</div>

  <div align="center" style="margin-top: 10px;">
    <table class="z-datatable">
      <thead>
        <tr>
          <th>{gt text='New Sourcestrings'}</th>
        </tr>
      </thead>
      <tbody id="resultarea"></tbody>
    </table>
  </div>

  <div align="center" id="okbuttonarea" style="margin-top: 30px;" class="z-hide">
    <div class="z-buttonrow z-buttons z-center">
      <a href="{modurl modname=$modinfo.name type='User' func='editTranslations' mod_id=$mod_id}" class="z-btgreen z-bt-ok">{gt text='OK'}</a>
    </div>
  </div>
</div>

{include file='user/partials/footer.tpl'}
