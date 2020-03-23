function prepareJavaScript() {

	Blockly.JavaScript.get_model_variable_boolean = function (block) {
		return getJS(block, "modelvariables1");
	};

	Blockly.JavaScript.set_model_variable_boolean = function (block) {
		return setJS(block, "model variables1", true);
	};


	Blockly.JavaScript.get_model_variable_number = function (block) {
		return getJS(block, "modelvariables3");
	};

	Blockly.JavaScript.set_model_variable_number = function (block) {
		return setJS(block, "model variables3", true);
	};

	// EVENTS
	Blockly.JavaScript.set_model_variable_boolean2 = function (block) {
		return setJS(block, "model variables1", false);
	};

	Blockly.JavaScript.set_model_variable_number2 = function (block) {
		return setJS(block, "model variables3", false);
	};

	//

	function getJS(block, text) {
		var dropdown_d = block.getFieldValue(text);
		return [dropdown_d, Blockly.JavaScript.ORDER_ATOMIC];
	}

	function setJS(block, text, general) {
		var dropdown_d = block.getFieldValue(text);
		var value_name = Blockly.JavaScript.valueToCode(block, "NAME", Blockly.JavaScript.ORDER_ATOMIC) || "0";
		var code = 'boolean';
		if (general)
			return "var set = true;\n" + dropdown_d + ' = ' + value_name + ';\nrevFin();\nset=false;\n';
		else
			return dropdown_d + ' = ' + value_name + ';\n';
	}

	Blockly.JavaScript.play_lab = function (block) {
		return "play();\n";
	};

	Blockly.JavaScript.pause_lab = function (block) {
		return "pause();\n";
	};

	Blockly.JavaScript.reset_lab = function (block) {
		return "reset();\n";
	};

	Blockly.JavaScript.initialize_lab = function (block) {
		return "initialize();\n";
	};

	Blockly.JavaScript.wait = function (block) {
		var number_name = block.getFieldValue('TIME');
		number_name = Number(number_name) * 1000;
		return 'setTimeStep(' + number_name + ');\n';
	};

	Blockly.JavaScript.jsUpDown = function (block) {
		var value_name = block.getFieldValue('jsOption');
		var code = "";
		for (var i = 0; i < jsCodesGeneral.length; i++) {
			if (jsCodesGeneral[i].name === value_name) {
				return "var set = true;\n" + jsCodesGeneral[i].code + '\nrevFin();\nset=false;\n';
			}
		}
		/*code = code.replace(/(\/\*([\s\S]*?)\*\/)|(\/\/(.*)$)/gm, '');
		code = code.replace(/(\r\n|\n|\r)/gm, "");*/
		return '\n';
	};

	Blockly.JavaScript.jsUpDown2 = function (block) {
		var value_name = block.getFieldValue('jsOption');
		var code = "";
		for (var i = 0; i < jsCodesEvents.length; i++) {
			if (jsCodesEvents[i].name === value_name) {
				return "var set = true;\n" + jsCodesEvents[i].code + '\nrevFin();\nset=false;\n';
			}
		}
		/*code = code.replace(/(\/\*([\s\S]*?)\*\/)|(\/\/(.*)$)/gm, '');
		code = code.replace(/(\r\n|\n|\r)/gm, "");*/
		return '\n';
	};

	Blockly.JavaScript.jsLeftRight = function (block) {
		var value_name = block.getFieldValue('jsOption');
		var code = "";
		for (var i = 0; i < jsCodesGeneral.length; i++) {
			if (jsCodesGeneral[i].name === value_name) {
				code = jsCodesGeneral[i].code;
				break;
			}
		}
		/*code = code.replace(/(\/\*([\s\S]*?)\*\/)|(\/\/(.*)$)/gm, '');
        code = code.replace(/(\r\n|\n|\r)/gm, "");*/
		return [code, Blockly.JavaScript.ORDER_NONE];
	};

	Blockly.JavaScript.jsLeftRight2 = function (block) {
		var value_name = block.getFieldValue('jsOption');
		var code = "";
		for (var i = 0; i < jsCodesEvents.length; i++) {
			if (jsCodesEvents[i].name === value_name) {
				code = jsCodesEvents[i].code;
				break;
			}
		}
		/*code = code.replace(/(\/\*([\s\S]*?)\*\/)|(\/\/(.*)$)/gm, '');
        code = code.replace(/(\r\n|\n|\r)/gm, "");*/
		return [code, Blockly.JavaScript.ORDER_NONE];
	};


	Blockly.JavaScript.start_rec = function (block) {
		return "rec(true);\n";
	};

	Blockly.JavaScript.stop_rec = function (block) {
		return "rec(false);\n";
	};

	Blockly.JavaScript.event = function (block) {
		var dropdown_d = block.getFieldValue("events_vars");
		var statements_name1 = Blockly.JavaScript.valueToCode(block, 'NAME1', Blockly.JavaScript.ORDER_ATOMIC) || "0";
		var statements_name2 = Blockly.JavaScript.statementToCode(block, 'NAME2');
		statements_name2 = statements_name2.replace(/(\/\*([\s\S]*?)\*\/)|(\/\/(.*)$)/gm, '');
		statements_name2 = statements_name2.replace(/(\r\n|\n|\r)/gm, "");
		/* TODO: Assemble JavaScript into code variable. */
		var condString = statements_name1 + " - " + dropdown_d;
		code = 'addEvent("' + condString + '", "' + statements_name2 + '");\n';
		return code;
	};

	Blockly.JavaScript.fixedRelation = function (block) {
		var statements_name1 = Blockly.JavaScript.statementToCode(block, 'NAME');

		statements_name1 = statements_name1.replace(/(\/\*([\s\S]*?)\*\/)|(\/\/(.*)$)/gm, '');
		statements_name1 = statements_name1.replace(/(\r\n|\n|\r)/gm, "");
		code = 'addFixedRelation(' + conditionFixed.length + ', "' + statements_name1 + '");\n';
		return code;
	};

	// CHARTS

	Blockly.JavaScript.createChart = function (block) {
		var name = block.getFieldValue('Chart_NAME');
		var time = block.getFieldValue('time');
		var check = block.getFieldValue('checkboxOnlyLast') == 'TRUE';
		var renew = block.getFieldValue('checkboxRenew') == 'TRUE';
		var numb = block.getFieldValue('numberOnlyLast');
		var value_name = Blockly.JavaScript.valueToCode(block, 'x', Blockly.JavaScript.ORDER_ATOMIC);
		var c0name = block.getFieldValue('cName0');
		var chartInfo2 = [];
		chartInfo2.push({
			"idNumber": chartId,
			"name": c0name,
			"value": value_name,
			"checkBox": check,
			"renew": renew,
			"number": numb,
			"title": name,
			"time": time
		});
		chartId++;
		for (var i = -1; i < block.itemCount_; i++) {
			var cname = block.getFieldValue('nameY' + i);
			var y = Blockly.JavaScript.valueToCode(block, ('ADD' + i), Blockly.JavaScript.ORDER_ATOMIC);
			chartInfo2.push({
				"name": cname,
				"value": y
			});
		}
		chartInfo.push(chartInfo2);
		var code = "createChart(" + (chartInfo.length - 1) + ");\n";
		return code;
	};

	Blockly.JavaScript['record_var'] = function (block) {
		var dropdown_name = block.getFieldValue('model variables3');
		var dropdown_name2 = block.getFieldValue('model variables4');
		// TODO: Assemble JavaScript into code variable.
		var code = 'record_var("' + dropdown_name + '", "' + dropdown_name2 + '");\n';
		return code;
	};

	// FUNCIONES EJSS
	for (var k = 0; k < function_from_ejss.length; k++) {
		Blockly.JavaScript['ejss_procedures_callnoreturn' + function_from_ejss[k].name] = function (block) {
			var tipo = this.type.replace("ejss_procedures_callnoreturn", "");
			var code = "";
			for (var i = 0; i < function_from_ejss.length; i++) {
				if (function_from_ejss[i].name.localeCompare(tipo) == 0) {
					var params = "[";
					for (var j = 0; j < function_from_ejss[i].params.length; j++) {
						if (j !== 0)
							params = params + ',';
						params = params + Blockly.JavaScript.valueToCode(block, 'NAME' + j, Blockly.JavaScript.ORDER_ATOMIC);
					}
					params = params + ']';
					code = 'callFunction("' + tipo + '",' + params + ');\n';
				}
			}
			return code;
		}
	}

	for (var i = 0; i < function_from_ejss_with_return.length; i++) {

		Blockly.JavaScript['ejss_procedures_callwithreturn' + function_from_ejss_with_return[i].name] = function (block) {
			var tipo = this.type.replace("ejss_procedures_callwithreturn", "");
			var code = "";
			for (var i = 0; i < function_from_ejss_with_return.length; i++) {
				if (function_from_ejss_with_return[i].name.localeCompare(tipo) == 0) {
					var params = "[";
					for (var j = 0; j < function_from_ejss_with_return[i].params.length; j++) {
						if (j !== 0)
							params = params + ',';
						params = params + Blockly.JavaScript.valueToCode(block, 'NAME' + j, Blockly.JavaScript.ORDER_ATOMIC);
					}
					params = params + ']';
					code = 'callFunction("' + tipo + '",' + params + ')\n';
				}
			}
			return [code, Blockly.JavaScript.ORDER_NONE];

		}
	}
}