function prepareJavaScript(jsCodesGeneral,jsCodesEvents,conditionFixed,chartId,chartInfo,function_from_ejss,function_from_ejss_with_return) {
	require(['mod_ejsapp/vendor/blockly/javascript_compressed'], function (BlocklyJS) {
		BlocklyJS.get_model_variable_boolean = function(block) {
			return getJS(block, "modelvariables1");
		};

		BlocklyJS.set_model_variable_boolean = function(block) {
			return setJS(block, "model variables1", true);
		};


		BlocklyJS.get_model_variable_number = function(block) {
			return getJS(block, "modelvariables3");
		};

		BlocklyJS.set_model_variable_number = function(block) {
			return setJS(block, "model variables3", true);
		};

		// EVENTS
		BlocklyJS.set_model_variable_boolean2 = function(block) {
			return setJS(block, "model variables1", false);
		};

		BlocklyJS.set_model_variable_number2 = function(block) {
			return setJS(block, "model variables3", false);
		};

		//

		function getJS(block, text) {
			var dropdown_d = block.getFieldValue(text);
			return [dropdown_d, BlocklyJS.ORDER_ATOMIC];
		}

		function setJS(block, text, general) {
			var dropdown_d = block.getFieldValue(text);
			var value_name = BlocklyJS.valueToCode(block, "NAME", BlocklyJS.ORDER_ATOMIC) || "0";
			var code = 'boolean';
			if (general)
				return "var set = true;\n" + dropdown_d + ' = ' + value_name + ';\nrevFin();\nset=false;\n';
			else
				return dropdown_d + ' = ' + value_name + ';\n';
		}

		BlocklyJS.play_lab = function(block) {
			return "play();\n";
		};

		BlocklyJS.pause_lab = function(block) {
			return "pause();\n";
		};

		BlocklyJS.reset_lab = function(block) {
			return "reset();\n";
		};

		BlocklyJS.initialize_lab = function(block) {
			return "initialize();\n";
		};

		BlocklyJS.wait = function(block) {
			var number_name = block.getFieldValue('TIME');
			number_name = Number(number_name) * 1000;
			return 'setTimeStep(' + number_name + ');\n';
		};

		BlocklyJS.jsUpDown = function(block) {
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

		BlocklyJS.jsUpDown2 = function(block) {
			var value_name = block.getFieldValue('jsOption');
			for (var i = 0; i < jsCodesEvents.length; i++) {
				if (jsCodesEvents[i].name === value_name) {
					return "var set = true;\n" + jsCodesEvents[i].code + '\nrevFin();\nset=false;\n';
				}
			}
			return '\n';
		};

		BlocklyJS.jsLeftRight = function(block) {
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
			return [code, BlocklyJS.ORDER_NONE];
		};

		BlocklyJS.jsLeftRight2 = function(block) {
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
			return [code, BlocklyJS.ORDER_NONE];
		};


		BlocklyJS.start_rec = function(block) {
			return "rec(true);\n";
		};

		BlocklyJS.stop_rec = function(block) {
			return "rec(false);\n";
		};

		BlocklyJS.event = function(block) {
			var dropdown_d = block.getFieldValue("events_vars");
			var statements_name1 = BlocklyJS.valueToCode(block, 'NAME1', BlocklyJS.ORDER_ATOMIC) || "0";
			var statements_name2 = BlocklyJS.statementToCode(block, 'NAME2');
			statements_name2 = statements_name2.replace(/(\/\*([\s\S]*?)\*\/)|(\/\/(.*)$)/gm, '');
			statements_name2 = statements_name2.replace(/(\r\n|\n|\r)/gm, "");
			/* TODO: Assemble JavaScript into code variable. */
			var condString = statements_name1 + " - " + dropdown_d;
			return 'addEvent("' + condString + '", "' + statements_name2 + '");\n';
		};

		BlocklyJS.fixedRelation = function(block) {
			var statements_name1 = BlocklyJS.statementToCode(block, 'NAME');

			statements_name1 = statements_name1.replace(/(\/\*([\s\S]*?)\*\/)|(\/\/(.*)$)/gm, '');
			statements_name1 = statements_name1.replace(/(\r\n|\n|\r)/gm, "");
			return 'addFixedRelation(' + conditionFixed.length + ', "' + statements_name1 + '");\n';
		};

		// CHARTS

		BlocklyJS.createChart = function(block) {
			var name = block.getFieldValue('Chart_NAME');
			var time = block.getFieldValue('time');
			var check = block.getFieldValue('checkboxOnlyLast') == 'TRUE';
			var renew = block.getFieldValue('checkboxRenew') == 'TRUE';
			var numb = block.getFieldValue('numberOnlyLast');
			var value_name = BlocklyJS.valueToCode(block, 'x', BlocklyJS.ORDER_ATOMIC);
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
				var y = BlocklyJS.valueToCode(block, ('ADD' + i), BlocklyJS.ORDER_ATOMIC);
				chartInfo2.push({
					"name": cname,
					"value": y
				});
			}
			chartInfo.push(chartInfo2);
			var code = "createChart(" + (chartInfo.length - 1) + ");\n";
			return code;
		};

		BlocklyJS.record_var = function(block) {
			var dropdown_name = block.getFieldValue('model variables3');
			var dropdown_name2 = block.getFieldValue('model variables4');
			// TODO: Assemble JavaScript into code variable.
			var code = 'record_var("' + dropdown_name + '", "' + dropdown_name2 + '");\n';
			return code;
		};

		// FUNCIONES EJSS
		for (var k = 0; k < function_from_ejss.length; k++) {
			BlocklyJS['ejss_procedures_callnoreturn' + function_from_ejss[k].name] = function(block) {
				var tipo = this.type.replace("ejss_procedures_callnoreturn", "");
				var code = "";
				for (var i = 0; i < function_from_ejss.length; i++) {
					if (function_from_ejss[i].name.localeCompare(tipo) == 0) {
						var params = "[";
						for (var j = 0; j < function_from_ejss[i].params.length; j++) {
							if (j !== 0)
								params = params + ',';
							params = params + BlocklyJS.valueToCode(block, 'NAME' + j, BlocklyJS.ORDER_ATOMIC);
						}
						params = params + ']';
						code = 'callFunction("' + tipo + '",' + params + ');\n';
					}
				}
				return code;
			}
		}

		for (var i = 0; i < function_from_ejss_with_return.length; i++) {

			BlocklyJS['ejss_procedures_callwithreturn' + function_from_ejss_with_return[i].name] = function(block) {
				var tipo = this.type.replace("ejss_procedures_callwithreturn", "");
				var code = "";
				for (var i = 0; i < function_from_ejss_with_return.length; i++) {
					if (function_from_ejss_with_return[i].name.localeCompare(tipo) == 0) {
						var params = "[";
						for (var j = 0; j < function_from_ejss_with_return[i].params.length; j++) {
							if (j !== 0)
								params = params + ',';
							params = params + BlocklyJS.valueToCode(block, 'NAME' + j, BlocklyJS.ORDER_ATOMIC);
						}
						params = params + ']';
						code = 'callFunction("' + tipo + '",' + params + ')\n';
					}
				}
				return [code, BlocklyJS.ORDER_NONE];

			}
		}
	});
}