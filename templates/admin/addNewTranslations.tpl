{ajaxheader modname=$modinfo.name filename='translator.js' ui=true}
{strip}
{insert name='csrftoken' assign='csrftoken'}
{pageaddvarblock}
<script type="text/javascript"> 
var modules = {{$items}};
var len = modules.length;
var i = 0;

document.observe("dom:loaded", function() {
    Zikula.UI.Tooltips($$('.tooltips'));
    processModule();
});
</script>
{/pageaddvarblock}
{/strip}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="search" size="small"}
    <h3>{gt text="New Translationstrings"}</h3>
</div>

<div align="center"><b>{gt text='Current module:'}</b> <span id="currentmod">&nbsp;</span></div>
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
        <a href="{modurl modname='Translator' type='admin' func='edit'}" class="z-btgreen z-bt-ok">{gt text='OK'}</a>
    </div>
</div>

{adminfooter}
