function processModule() {
	module = modules.pop();
	$('currentmod').innerHTML = module.modname;
	pars = {
		mod: module.mod_id
	};
	var myAjax = new Zikula.Ajax.Request(
		Zikula.Config.baseURL + 'ajax.php?module=Translator&func=searchTranslations', 
		{
			parameters: pars,
			onComplete: processModule_response
		}
	);
}

function processModule_response(req) {
	data = req.getData();
	i++;
	$('progbar').style.width = (100 * (i) / len ).toFixed(2) + '%';
	$('percentage').innerHTML = (100 * (i) / len ).toFixed(2) + ' %';
	tbody = $('resultarea');
	
	for (var j = 0; j < data.result.length; j++) {
		tr = document.createElement("tr");
		td = document.createElement("td");
		td.innerHTML = data.result[j];
		tr.appendChild(td);
		tbody.appendChild(tr);
	}
	
	if (i < len) {
		processModule();
	} else {
		$('currentmod').innerHTML = '';
		$('okbuttonarea').className = '';
	}
}
