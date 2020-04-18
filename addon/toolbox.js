// TOOLBOX CONFIGURATION

require(['mod_ejsapp/vendor/blockly/blockly_compressed'], function (Blockly) {
	generalVars = function(worksp) {
		var extra = "";
		if (worksp.id !== workspace.id)
			extra = "2";

		var xmlList = [];
		xmlList.push(Blockly.Xml.textToDom('<xml><button text="' + Blockly.Msg["CreateVar"] + '" callbackKey="createVariablePressed"></button></xml>').firstChild);
		if (((!newImplement) && (keys_boolean.length === 0)) || ((newImplement) && (keys_boolean_input.length === 0) && (keys_boolean_output.length === 0))) {
		} else
			xmlList.push(Blockly.Xml.textToDom('<xml><label text="' + Blockly.Msg["VarBoolean"] + '" ></label></xml>').firstChild);
		if (!newImplement) {
			if (keys_boolean.length > 0) {
				xmlList.push(Blockly.Xml.textToDom('<xml><block type="set_model_variable_boolean' + extra + '"></block></xml>').firstChild);
				xmlList.push(Blockly.Xml.textToDom('<xml><block type="get_model_variable_boolean"></block></xml>').firstChild);
			}
		} else {
			if (keys_boolean_input.length > 0)
				xmlList.push(Blockly.Xml.textToDom('<xml><block type="set_model_variable_boolean' + extra + '"></block></xml>').firstChild);
			if (keys_boolean_output.length > 0)
				xmlList.push(Blockly.Xml.textToDom('<xml><block type="get_model_variable_boolean"></block></xml>').firstChild);
		}
		if (((!newImplement) && (keys_number.length === 0)) || ((newImplement) && (keys_number_input.length === 0) && (keys_number_output.length === 0))) {
		} else
			xmlList.push(Blockly.Xml.textToDom('<xml><label text="' + Blockly.Msg["VarNumber"] + '" ></label></xml>').firstChild);
		if (!newImplement) {
			if (keys_number.length > 0) {
				xmlList.push(Blockly.Xml.textToDom('<xml><block type="set_model_variable_number' + extra + '"></block></xml>').firstChild);
				xmlList.push(Blockly.Xml.textToDom('<xml><block type="get_model_variable_number"></block></xml>').firstChild);
			}
		} else {
			if (keys_number_input.length > 0)
				xmlList.push(Blockly.Xml.textToDom('<xml><block type="set_model_variable_number' + extra + '"></block></xml>').firstChild);
			if (keys_number_output.length > 0)
				xmlList.push(Blockly.Xml.textToDom('<xml><block type="get_model_variable_number"></block></xml>').firstChild);
		}
		return xmlList;

	};


	jss = function(workspace) {
		var xmlList = [];
		var blockText = '<xml>' + '<button text="' + Blockly.Msg["CreateJavaScript"] + '" callbackKey="jsButtonPressed"></button>' + '</xml>';
		var block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);
		blockText = '<xml>' + '<button text="' + Blockly.Msg["LoadJavaScript"] + '" callbackKey="loadjsButtonPressed"></button>' + '</xml>';
		block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);

		if (jsCodesGeneral.length > 0) {
			blockText = '<xml>' + '<block type="jsUpDown"></block>' + '</xml>';
			block = Blockly.Xml.textToDom(blockText).firstChild;
			xmlList.push(block);
			blockText = '<xml>' + '<block type="jsLeftRight"></block>' + '</xml>';
			block = Blockly.Xml.textToDom(blockText).firstChild;
			xmlList.push(block);
		}
		return xmlList;
	};

	jss2 = function(workspace) {
		var xmlList = [];
		var blockText = '<xml>' + '<button text="' + Blockly.Msg["CreateJavaScript"] + '" callbackKey="jsButtonPressed2"></button>' + '</xml>';
		var block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);
		blockText = '<xml>' + '<button text="' + Blockly.Msg["LoadJavaScript"] + '" callbackKey="loadjsButtonPressed2"></button>' + '</xml>';
		block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);

		if (jsCodesEvents.length > 0) {
			blockText = '<xml>' + '<block type="jsUpDown2"></block>' + '</xml>';
			block = Blockly.Xml.textToDom(blockText).firstChild;
			xmlList.push(block);
			blockText = '<xml>' + '<block type="jsLeftRight2"></block>' + '</xml>';
			block = Blockly.Xml.textToDom(blockText).firstChild;
			xmlList.push(block);
		}
		return xmlList;
	};

	controls = function(workspace) {
		var xmlList = [];
		if (events_vars.length > 0) {
			var blockText = '<xml>' + '<block type="event"></block>' + '</xml>';
			var block = Blockly.Xml.textToDom(blockText).firstChild;
			xmlList.push(block);
		}
		blockText = '<xml>' + '<block type="fixedRelation"></block>' + '</xml>';
		block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);
		return xmlList;
	};

	ejssFunctions = function(workspace) {
		var xmlList = [];
		for (var i = 0; i < function_from_ejss.length; i++) {
			var blockText = '<xml>' + '<block type="ejss_procedures_callnoreturn' + function_from_ejss[i].name + '"></block>' + '</xml>';
			var block = Blockly.Xml.textToDom(blockText).firstChild;
			xmlList.push(block);
		}
		for (var i = 0; i < function_from_ejss_with_return.length; i++) {
			var blockText = '<xml>' + '<block type="ejss_procedures_callwithreturn' + function_from_ejss_with_return[i].name + '"></block>' + '</xml>';
			var block = Blockly.Xml.textToDom(blockText).firstChild;
			xmlList.push(block);
		}
		return xmlList;
	}
});