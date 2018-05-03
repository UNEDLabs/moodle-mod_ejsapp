// Save a program in blockly
var saveCode = function(context_id, user_id, ejsapp_id) {
	var xmlDom = Blockly.Xml.workspaceToDom(workspace);
	var xmlText = Blockly.Xml.domToPrettyText(xmlDom);
	_model.saveText('blocks_context_id_' + context_id + '_user_id_' + user_id + '_ejsapp_id_' + ejsapp_id, 'blk', xmlText);
};

// Load a program in blockly
var loadCode = function() {
	_model.readText(null, '.blk',
		function(xmlText) {
		if (xmlText) {
			workspace.clear();
			xmlDom = Blockly.Xml.textToDom(xmlText);
			Blockly.Xml.domToWorkspace(xmlDom, workspace);
		}
	});
};

function playCode() {
	parseCode();
	if (remoteLab) {
        if (_model.sendToRemoteController !== undefined) {
            var code = getRemoteCode();
            if (code !== "")
                _model.sendToRemoteController(code);
        }
    }
	inter = setInterval(stepCode, time_step);
}

function saveWorkspace() {
	var xmlDom = Blockly.Xml.workspaceToDom(workspace);
	var xmlText = Blockly.Xml.domToPrettyText(xmlDom);
	var file = (document.getElementById("myText").value + ".xml");
	localStorage.setItem(file, xmlText);
}

function loadWorkspace() {
	var file = (document.getElementById("myText").value + ".xml");
	var xmlText = localStorage.getItem(file);
	if (xmlText) {
		workspace.clear();
		xmlDom = Blockly.Xml.textToDom(xmlText);
		Blockly.Xml.domToWorkspace(xmlDom, workspace);
	}
}

function cleanCharts() {
    var clean = actual_chart;
	if(chartArray.length > 1) {
		prevChart();
		// clearInterval(chartArray[clean]["timer"]);
		var name = chartArray[clean]["title"];
		chartArray.splice(clean,1);
		for (var i = 0; i < chartInfo.length; i++) {
			if(chartInfo[i][0]["title"] === name) chartInfo.splice(i,1);
		}
	} else {
		hideAllCharts();
		// clearInterval(chartArray[clean]["timer"]);
		var name = chartArray[clean]["title"];
		chartArray.splice(clean,1);
		for (var i = 0; i < chartInfo.length; i++) {
			if(chartInfo[i][0]["title"] === name) chartInfo.splice(i,1);
		}
	}
    if (chartArray.length < 2) {
		document.getElementById("prev_chart").style.display = "none";
        document.getElementById("next_chart").style.display = "none";
        if (chartArray.length < 1) {
            document.getElementById("buttons_charts").style.display = "none";
            document.getElementById("slideshow").style.display = "none";
        }
    } else {
        document.getElementById("buttons_charts").style.display = "flex";
        document.getElementById("prev_chart").style.display = "inline";
        document.getElementById("next_chart").style.display = "inline";
    }
}

function getRemoteCode() {
	if (remoteLab) {
		var variables = "";
		for (var i = 0; i < declared_variables_remote.length; i++) {
			variables = variables + "var " + declared_variables_remote[i] + "; "
		}
		console.log("Remote Code: \n" + variables + function_code_remote);
		return variables + function_code_remote;
	}
}