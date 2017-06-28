
// Save a program in blockly
var saveCode = function (context_id, user_id, ejsapp_id) {
	var xmlDom = Blockly.Xml.workspaceToDom(workspace);
	var xmlText = Blockly.Xml.domToPrettyText(xmlDom);
	_model.saveText('blocks_context_id_' + context_id + '_user_id_' + user_id + '_ejsapp_id_' + ejsapp_id, 'blk', xmlText);
};

// Load a program in blockly
var loadCode = function () {
	_model.readText(null, '.blk',
		function (xmlText) {
		if (xmlText) {
			workspace.clear();
			xmlDom = Blockly.Xml.textToDom(xmlText);
			Blockly.Xml.domToWorkspace(xmlDom, workspace);
		}
	});
};
function playCode() {
	parseCode();
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
