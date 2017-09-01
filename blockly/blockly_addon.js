var keys_boolean = [];
var keys_number = [];
var keys_string = [];
var keys_others = [];
var events_vars = [];
var statem = [];
var record = false;
var checkedValue;
var functions;
var events = [{}];
var fixedStatements = [];
var num_events = 0;
var arrayColumn = [1];
var conditionFixed = [];
var workspace;
var myInterpreter = null;
var code;
var highlightPause = false;
var interval = false;
var inter;
var paramsList = "";
var replacing = false;
var special = false;
var declared_variables_remote = [];
var function_code_remote = "";
var remote = false;

function loadModelBlocks() {
	var _vars = _model.getOdes()[0]._getOdeVars();
	var obj = _model._userSerializePublic();

	var keys = [];
	var i = 1;
    var dupla;
	for (var k in obj) {
		dupla = [];
		dupla.push(k);
		dupla.push(k);
		switch (typeof obj[k]) {
		case 'string':
			keys_string.push(dupla);
			break;
		case 'number':
			keys_number.push(dupla);
			break;
		case 'boolean':
			keys_boolean.push(dupla);
			break;
		default:
			keys_others.push(dupla);
			break;
		}
		keys.push(dupla);
		i++;
	}

	for (var e in _vars) {
		dupla = [];
		dupla.push(_vars[e]);
		dupla.push(_vars[e]);
		events_vars.push(dupla);
	}

	Blockly.Blocks['get_model_variable'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(new Blockly.FieldDropdown(keys), "modelvariables");
			this.setOutput(true, null);
			this.setColour(290);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['set_model_variable'] = {
		init: function () {
			this.appendValueInput("NAME")
			.appendField(Blockly.Msg.LISTS_SET_INDEX_SET)
			.appendField(new Blockly.FieldDropdown(keys), "model variables")
			.appendField(Blockly.Msg.TEXT_APPEND_TO);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(120);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['get_model_variable_boolean'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(new Blockly.FieldDropdown(keys_boolean), "modelvariables1");
			this.setOutput(true, "Boolean");
			this.setColour(290);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['set_model_variable_boolean'] = {
		init: function () {
			this.appendValueInput("NAME")
			.appendField(Blockly.Msg.LISTS_SET_INDEX_SET)
			.appendField(new Blockly.FieldDropdown(keys_boolean), "model variables1")
			.appendField(Blockly.Msg.TEXT_APPEND_TO)
			.setCheck("Boolean");
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(120);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['get_model_variable_string'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(new Blockly.FieldDropdown(keys_string), "modelvariables2");
			this.setOutput(true, "Text");
			this.setColour(290);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['set_model_variable_string'] = {
		init: function () {
			this.appendValueInput("NAME")
			.appendField(Blockly.Msg.LISTS_SET_INDEX_SET)
			.appendField(new Blockly.FieldDropdown(keys_string), "model variables2")
			.appendField(Blockly.Msg.TEXT_APPEND_TO)
			.setCheck("Text");
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(120);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['get_model_variable_number'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(new Blockly.FieldDropdown(keys_number), "modelvariables3");
			this.setOutput(true, "Number");
			this.setColour(290);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['set_model_variable_number'] = {
		init: function () {
			this.appendValueInput("NAME")
			.appendField(Blockly.Msg.LISTS_SET_INDEX_SET)
			.appendField(new Blockly.FieldDropdown(keys_number), "model variables3")
			.appendField(Blockly.Msg.TEXT_APPEND_TO)
			.setCheck("Number");
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(120);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['get_model_variable_others'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(new Blockly.FieldDropdown(keys_others), "modelvariables4");
			this.setOutput(true, null);
			this.setColour(290);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['set_model_variable_others'] = {
		init: function () {
			this.appendValueInput("NAME")
			.appendField(Blockly.Msg.LISTS_SET_INDEX_SET)
			.appendField(new Blockly.FieldDropdown(keys_others), "model variables4")
			.appendField(Blockly.Msg.TEXT_APPEND_TO);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(120);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['play_lab'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpSTART);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(0);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['playStop_lab'] = {
		init: function () {
			this.appendValueInput("NAME")
			.appendField(Blockly.Msg.ExpSTARTFOR)
			.setCheck("Number");
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpSECONDS);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(0);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['pause_lab'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpPAUSE);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(0);
			this.setTooltip('');
		}
	};
	Blockly.Blocks['reset_lab'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpRESET);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(0);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['event'] = {
		init: function () {
			this.appendValueInput("NAME1")
			.appendField(Blockly.Msg.ExpWHEN)
			.appendField(new Blockly.FieldDropdown(events_vars), "events_vars")
			.appendField(Blockly.Msg.ExpIS)
			.setCheck("Number");
			this.appendStatementInput("NAME2")
			.setCheck(null)
			.appendField(Blockly.Msg.CONTROLS_REPEAT_INPUT_DO);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(60);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['fixedRelation'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpLABSTEP);
			this.appendStatementInput("NAME")
			.setCheck(null);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(60);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['wait'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpWAIT)
			.appendField(new Blockly.FieldNumber(0, 0, 600), "TIME")
			.appendField(Blockly.Msg.ExpSECONDS);
			this.setPreviousStatement(true, "null");
			this.setNextStatement(true, "null");
			this.setColour(60);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['createChartSquematic'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpCHART);
			this.appendStatementInput("NAME")
			.setCheck(null);
			this.setColour(200);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['Y Axis'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpYAXIS);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(200);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['evaluation'] = {
		init: function () {
			special = true;
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpEVAL)
			.appendField(new Blockly.FieldTextInput("abc"), "expre");
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, "null");
			this.setColour(0);
			this.setTooltip('');
			this.setCommentText("abc");
			this.comment.setBubbleSize(300, 150);
			special = false;
		},
		onchange: function(ev) {
				if(ev.element==="comment")
					this.setFieldValue(this.getCommentText(),"expre");
				if(ev.element==="field")
					this.setCommentText(this.getFieldValue('expre'));
		}
	};

	Blockly.Blocks['createChart'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpCHART)
			.appendField(new Blockly.FieldTextInput("Title"), "Chart_NAME");
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpCHARTOPTIONS);
			this.appendDummyInput()
			.setAlign(Blockly.ALIGN_RIGHT)
			.appendField(Blockly.Msg.ExpCHARTPLOT)
			.appendField(new Blockly.FieldNumber(100), "time")
			.appendField(Blockly.Msg.ExpMILLISECONDS);
			this.appendDummyInput()
			.setAlign(Blockly.ALIGN_RIGHT)
			.appendField("   ")
			.appendField(new Blockly.FieldCheckbox("False"), "checkboxOnlyLast")
			.appendField(Blockly.Msg.ExpCHARTLAST)
			.appendField(new Blockly.FieldNumber(10, 1), "numberOnlyLast")
			.appendField(Blockly.Msg.ExpVALUES);
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpCHARTDATA);
			this.appendValueInput("x")
			.setCheck(null)
			.setAlign(Blockly.ALIGN_RIGHT)
			.appendField(Blockly.Msg.ExpXAXIS)
			.appendField(new Blockly.FieldTextInput("Column Title"), "cName0");
			this.setInputsInline(false);
			this.setColour(200);
			this.setTooltip('');
			this.setMutator(new Blockly.Mutator(['Y Axis']));
		},
		/**
		 * Create XML to represent list inputs.
		 * @return {!Element} XML storage element.
		 * @this Blockly.Block
		 */
		mutationToDom: function () {
			var container = document.createElement('mutation');
			container.setAttribute('items', this.itemCount_);
			return container;
		},
		/**
		 * Parse XML to restore the list inputs.
		 * @param {!Element} xmlElement XML storage element.
		 * @this Blockly.Block
		 */
		domToMutation: function (xmlElement) {
			this.itemCount_ = parseInt(xmlElement.getAttribute('items'), 10);
			this.updateShape_();
		},
		/**
		 * Populate the mutator's dialog with this block's components.
		 * @param {!Blockly.Workspace} workspace Mutator's workspace.
		 * @return {!Blockly.Block} Root block in mutator.
		 * @this Blockly.Block
		 */
		decompose: function (workspace) {
			var containerBlock = workspace.newBlock('lists_create_with_container');
			containerBlock.initSvg();
			var connection = containerBlock.getInput('STACK').connection;
			for (var i = 0; i < this.itemCount_; i++) {
				var itemBlock = workspace.newBlock('lists_create_with_item');
				itemBlock.initSvg();
				connection.connect(itemBlock.previousConnection);
				connection = itemBlock.nextConnection;
			}
			return containerBlock;
		},
		/**
		 * Reconfigure this block based on the mutator dialog's components.
		 * @param {!Blockly.Block} containerBlock Root block in mutator.
		 * @this Blockly.Block
		 */
		compose: function (containerBlock) {
			var itemBlock = containerBlock.getInputTargetBlock('STACK');
			// Count number of inputs.
			var connections = [];
			while (itemBlock) {
				connections.push(itemBlock.valueConnection_);
				itemBlock = itemBlock.nextConnection &&
					itemBlock.nextConnection.targetBlock();
			}
			// Disconnect any children that don't belong.
			for (var i = 0; i < this.itemCount_; i++) {
				var connection = this.getInput('ADD' + i).connection.targetConnection;
				if (connection && connections.indexOf(connection) === -1) {
					connection.disconnect();
				}
			}
			this.itemCount_ = connections.length;
			this.updateShape_();
			// Reconnect any child blocks.
			for (var i = 0; i < this.itemCount_; i++) {
				Blockly.Mutator.reconnect(connections[i], this, 'ADD' + i);
			}
		},
		/**
		 * Store pointers to any connected child blocks.
		 * @param {!Blockly.Block} containerBlock Root block in mutator.
		 * @this Blockly.Block
		 */
		saveConnections: function (containerBlock) {
			var itemBlock = containerBlock.getInputTargetBlock('STACK');
			var i = 0;
			while (itemBlock) {
				var input = this.getInput('ADD' + i);
				itemBlock.valueConnection_ = input && input.connection.targetConnection;
				i++;
				itemBlock = itemBlock.nextConnection &&
					itemBlock.nextConnection.targetBlock();
			}
		},
		/**
		 * Modify this block to have the correct number of inputs.
		 * @private
		 * @this Blockly.Block
		 */
		updateShape_: function () {
			if (this.itemCount_ && this.getInput('EMPTY')) {
				this.removeInput('EMPTY');
			}
			// Add new inputs.
			for (var i = 0; i < this.itemCount_; i++) {
				if (!this.getInput('ADD' + i)) {

					var input = this.appendValueInput('ADD' + i);
					input.appendField((i + 1) + ". " + Blockly.Msg.ExpYAXIS);
					input.appendField(new Blockly.FieldTextInput("Column Title" + (i + 1)), "nameY" + i);
					input.setAlign(Blockly.ALIGN_RIGHT)
				}
			}
			// Remove deleted inputs.
			while (this.getInput('ADD' + i)) {
				this.removeInput('ADD' + i);
				i++;
			}
		}

	};

	Blockly.Blocks['start_rec'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpSTARTRECORD);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(200);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['stop_rec'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpSTOPRECORD);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(200);
			this.setTooltip('');
		}
	};

	// EQUATION
	Blockly.Blocks['resultado'] = {
		init: function () {
			this.appendValueInput("statement1")
			.setCheck("Number")
			.appendField("equation =");
			this.setColour(210);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['initialize_lab'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpINITIALIZE);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(0);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['replacefunc'] = {
		init: function () {
			this.appendDummyInput()
			.appendField(Blockly.Msg.ExpREPLACE)
			.appendField(new Blockly.FieldDropdown(keys_others), "original");
			this.appendDummyInput()
			.setAlign(Blockly.ALIGN_RIGHT)
			.appendField("", "aux")
			.appendField("", "params")
			/*this.appendDummyInput()
			.setAlign(Blockly.ALIGN_RIGHT)
			.appendField(Blockly.Msg.ExpNEWVAR)
			.appendField(new Blockly.FieldTextInput(""), "newVars");*/
			this.appendStatementInput("code")
			.setCheck(null)
			.appendField(Blockly.Msg.ExpCODE);
			this.appendValueInput("return")
			.setCheck(null)
			.setAlign(Blockly.ALIGN_RIGHT)
			.appendField(Blockly.Msg.PROCEDURES_DEFRETURN_RETURN);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(0);
			this.setTooltip('');
			this.setHelpUrl('');
		},
		onchange: function(ev) {
			var params = getParamNames(getValueModel(this.getFieldValue('original')));
			paramsList = paramsList+params;
			if (/\S/.test(params)){
				this.setFieldValue(Blockly.Msg.ExpINPUT+":","aux");
				this.setFieldValue(getParamNames(getValueModel(this.getFieldValue('original'))),"params");
				}
			else{
				this.setFieldValue("","aux");
				this.setFieldValue("","params");
				}
		}
		
	};
}

var condition = false;

function loadJavaScriptModelBlocks() {

	Blockly.JavaScript['start_rec'] = function (block) {
		return "rec(true);\n";
	};

	Blockly.JavaScript['stop_rec'] = function (block) {
		return "rec(false);\n";
	};

	Blockly.JavaScript['createChart'] = function (block) {
		var name = block.getFieldValue('Chart_NAME');
		var time = block.getFieldValue('time');
		var check = block.getFieldValue('checkboxOnlyLast') == 'TRUE';
		var numb = block.getFieldValue('numberOnlyLast');
		var value_name = Blockly.JavaScript.valueToCode(block, 'x', Blockly.JavaScript.ORDER_ATOMIC);
		var c0name = block.getFieldValue('cName0');
		//alert(block.itemCount_);
		var chartInfo2 = [];
		chartInfo2.push({
			"name": c0name,
			"value": value_name,
			"checkBox": check,
			"number": numb,
			"title": name,
			"time": time
		});
		//var yaxis = "";
		for (var i = 0; i < block.itemCount_; i++) {
			var cname = block.getFieldValue('nameY' + i);
			var y = Blockly.JavaScript.valueToCode(block, ('ADD' + i), Blockly.JavaScript.ORDER_ATOMIC);
			//var y = block..getInput('ADD' + i);
			//yaxis=yaxis+",";
			//yaxis=yaxis+"{\""+cname+"\":"+y+"}";
			chartInfo2.push({
				"name": cname,
				"value": y
			});
		}
		chartInfo.push(chartInfo2);
		var code = "createChart("+(chartInfo.length-1)+");\n";
		return code;
	};

	Blockly.JavaScript['event'] = function (block) {
		condition = true;
		var dropdown_d = block.getFieldValue("events_vars");
		var statements_name1 = Blockly.JavaScript.valueToCode(block, 'NAME1');
		var statements_name2 = Blockly.JavaScript.statementToCode(block, 'NAME2');
		condition = false;
		// TODO: Assemble JavaScript into code variable.
		var condString = statements_name1 + " - " + dropdown_d;
		events.push({
			"num": num_events,
			"cond": condString,
			"act": statements_name2
		});
		code = "addEvent(" + num_events + ");\n";
		num_events++;
		return code;
	};

	Blockly.JavaScript['fixedRelation'] = function (block) {
		condition = true;
		var statements_name1 = Blockly.JavaScript.statementToCode(block, 'NAME');
		condition = false;
		fixedStatements.push(statements_name1);
		code = "addFixedRelation(" + (fixedStatements.length - 1) + ");\n";
		return code;
	};

	Blockly.JavaScript['wait'] = function (block) {
		var number_name = block.getFieldValue('TIME');
		number_name = Number(number_name) * 1000;
		//var statements_name = Blockly.JavaScript.statementToCode(block, 'CODE');
		//return 'setTimeout(function() {\n'+statements_name+'\n}, '+number_name+');'+'\n';
		return 'setTimeStep(' + number_name + ');\n';
	};

	function getJS(block, text) {
		var dropdown_d = block.getFieldValue(text);
		var code;
		if ((!condition)&&(!remote))
			code = "getValueModel('" + dropdown_d + "')";
		else
			code = dropdown_d;
		return [code, Blockly.JavaScript.NONE];

	}

	/* function setJS(block,text){
	var func = false;
	var dropdown_d = block.getFieldValue(text);
	var value_name = Blockly.JavaScript.valueToCode(block, 'NAME', Blockly.JavaScript.ORDER_ATOMIC);
	alert(dropdown_d);
	alert(value_name);
	var code;
	var obj = _model._userSerialize();
	alert("ib");
	if((typeof(obj[dropdown_d])).localeCompare("function")==0){
	value_name=value_name.replace(")","");
	value_name=value_name.replace("(","");
	value_name=value_name.replace("()","");
	//func = true;
	}
	alert("ibvvv");
	if(!condition)
	code = "setValueModel('"+dropdown_d+"', "+value_name+");\n";
	else
	code = dropdown_d+" = "+value_name+";\n";
	alert(code);
	return [code, Blockly.JavaScript.NONE];

	};*/

	function setJS(block, text) {
		var dropdown_d = block.getFieldValue(text);
		var value_name = Blockly.JavaScript.valueToCode(block, 'NAME', Blockly.JavaScript.ORDER_ATOMIC);
		var code;
		var obj = _model._userSerializePublic();
		if ((typeof(obj[dropdown_d])).localeCompare("function") === 0) {
			value_name = value_name.replace("()", "");
		}
		if ((!condition)&&(!remote))
			code = "setValueModel('" + dropdown_d + "', " + value_name + ");\n";
		else
			code = dropdown_d + " = " + value_name + ";\n";
		return code;

	}

	Blockly.JavaScript['get_model_variable_boolean'] = function (block) {
		return getJS(block, "modelvariables1");
	};

	Blockly.JavaScript['set_model_variable_boolean'] = function (block) {
		return setJS(block, "model variables1");
	};

	Blockly.JavaScript['get_model_variable_string'] = function (block) {
		return getJS(block, "modelvariables2");
	};
	Blockly.JavaScript['set_model_variable_string'] = function (block) {
		return setJS(block, "model variables2");
	};

	Blockly.JavaScript['get_model_variable_number'] = function (block) {
		return getJS(block, "modelvariables3");
	};

	Blockly.JavaScript['set_model_variable_number'] = function (block) {
		return setJS(block, "model variables3");
	};

	Blockly.JavaScript['get_model_variable_others'] = function (block) {
		return getJS(block, "modelvariables4");
	};

	Blockly.JavaScript['set_model_variable_others'] = function (block) {
		return setJS(block, "model variables4");
	};

	Blockly.JavaScript['get_model_variable'] = function (block) {
		return getJS(block, "modelvariables");
	};

	Blockly.JavaScript['set_model_variable'] = function (block) {
		return setJS(block, "model variables");
	};

	Blockly.JavaScript['play_lab'] = function (block) {
		return "play();\n";
	};

	Blockly.JavaScript['playStop_lab'] = function (block) {
		var value_name = Blockly.JavaScript.valueToCode(block, 'NAME', Blockly.JavaScript.ORDER_ATOMIC);
		code = "playStop(" + value_name + ");\n";
		return code;
	};
	Blockly.JavaScript['pause_lab'] = function (block) {
		return "pause();\n";
	};
	Blockly.JavaScript['reset_lab'] = function (block) {
		return "reset();\n";
	};

	//Blockly.JavaScript.init=function(a){Blockly.JavaScript.definitions_=Object.create(null);Blockly.JavaScript.functionNames_=Object.create(null);Blockly.JavaScript.variableDB_?Blockly.JavaScript.variableDB_.reset():Blockly.JavaScript.variableDB_=new Blockly.Names(Blockly.JavaScript.RESERVED_WORDS_);var b=[];a=a.variableList;if(a.length){for(var c=0;c<a.length;c++)b[c]=Blockly.JavaScript.variableDB_.getName(a[c],Blockly.Variables.NAME_TYPE);Blockly.JavaScript.definitions_.variables="var "+b.join(", ")+";"}};

	Blockly.JavaScript.init = function (a) {
		Blockly.JavaScript.definitions_ = Object.create(null);
		Blockly.JavaScript.functionNames_ = Object.create(null);
		Blockly.JavaScript.variableDB_ ? Blockly.JavaScript.variableDB_.reset() : Blockly.JavaScript.variableDB_ = new Blockly.Names(Blockly.JavaScript.RESERVED_WORDS_);
		var b = [];
		a = a.variableList;
		if (a.length) {
			for (var c = 0; c < a.length; c++)
			{
				b[c] = "asdsdiuhsgiud"+Blockly.JavaScript.variableDB_.getName(a[c], Blockly.Variables.NAME_TYPE);
				if(remoteLab){
					var regex = new RegExp('\\b' + b[c] + '\\b');
					if(paramsList.search(regex)===-1)
						declared_variables_remote.push(b[c]);
					}
			}
			//Blockly.JavaScript.definitions_.variables = 'window["'+b.join('=0, ') + '"]=0;';
			Blockly.JavaScript.definitions_.variables = "define('"+b+"');";
			
		}
	};
	
	Blockly.JavaScript.variables={};
	
	/*Blockly.JavaScript.variables_get = function (a) {
		//if(!condition)
		return [Blockly.JavaScript.variableDB_.getName(a.getFieldValue("VAR"), Blockly.Variables.NAME_TYPE), Blockly.JavaScript.ORDER_ATOMIC]
		//else
		//return ["window."+Blockly.JavaScript.variableDB_.getName(a.getFieldValue("VAR"),Blockly.Variables.NAME_TYPE),Blockly.JavaScript.ORDER_ATOMIC]

	};

	Blockly.JavaScript.variables_set = function (a) {
		var b = Blockly.JavaScript.valueToCode(a, "VALUE", Blockly.JavaScript.ORDER_ASSIGNMENT) || "0";
		//if(!condition)
		return Blockly.JavaScript.variableDB_.getName(a.getFieldValue("VAR"), Blockly.Variables.NAME_TYPE) + " = " + b + ";\n"
		//else
		//return "window."+Blockly.JavaScript.variableDB_.getName(a.getFieldValue("VAR"),Blockly.Variables.NAME_TYPE)+" = "+b+";\n"
	};*/

	Blockly.JavaScript.variables_get=function(a){
		if(replacing){
			return ["asdsdiuhsgiud"+Blockly.JavaScript.variableDB_.getName(a.getFieldValue("VAR"), Blockly.Variables.NAME_TYPE), Blockly.JavaScript.ORDER_ATOMIC]}
		else
			return ["getVarExp('"+"asdsdiuhsgiud"+Blockly.JavaScript.variableDB_.getName(a.getFieldValue("VAR"),Blockly.Variables.NAME_TYPE)+"')",Blockly.JavaScript.ORDER_ATOMIC];
	};
	
	Blockly.JavaScript.variables_set=function(a){
		var b=Blockly.JavaScript.valueToCode(a,"VALUE",Blockly.JavaScript.ORDER_ASSIGNMENT)||"0"; 
		if(replacing)
			return "asdsdiuhsgiud"+Blockly.JavaScript.variableDB_.getName(a.getFieldValue("VAR"),Blockly.Variables.NAME_TYPE)+"="+b+";\n";
		else
			return "setVarExp('"+"asdsdiuhsgiud"+Blockly.JavaScript.variableDB_.getName(a.getFieldValue("VAR"),Blockly.Variables.NAME_TYPE)+"',"+b+");\n";
	};

	Blockly.JavaScript['resultado'] = function (block) {
		condition = true;
		var value_name = Blockly.JavaScript.valueToCode(block, 'statement1', Blockly.JavaScript.ORDER_ATOMIC);
		condition = false;
		return value_name + "\n";
	};

	Blockly.JavaScript['evaluation'] = function (block) {
		var value_name = block.getFieldValue('expre');
		value_name = value_name.replace(/(\r\n|\n|\r)/gm, "");
		return "evaluate(\"" + value_name + "\");\n";
	};

	Blockly.JavaScript['replacefunc'] = function (block) {
		var dropdown_original = block.getFieldValue('original');
		var text_params = block.getFieldValue('params');
		replacing = true;
		remote = true && remoteLab;
		var statements_code = Blockly.JavaScript.statementToCode(block, 'code');
		remote = false;
		replacing = false;
		paramsList = "";
		var value_name = Blockly.JavaScript.valueToCode(block, 'return', Blockly.JavaScript.ORDER_ATOMIC);
		// TODO: Assemble JavaScript into code variable.
		statements_code = statements_code ? statements_code.toString() : '';
		value_name = value_name ? value_name.toString() : '';
		statements_code = statements_code.replace(/(\r\n|\n|\r)/gm, "");
		statem.push(statements_code.toString());
		if(remoteLab)
			var code = "";
		else
			var code = 'replaceFunction("' + dropdown_original + '","' + text_params + '","' + value_name + '");\n';
		function_code_remote = statements_code ;//+' return ' + value_name + ';';
		return code;
	};

	Blockly.JavaScript['initialize_lab'] = function (block) {
		return "initialize();\n";
	};

}

////////// CHARTS /////////////////////////////////////////////

var tabs = document.getElementById('slideshow-wrapper');
var actual_chart = 0;
var chartArray = [];
var chartInfo = [];
var id = 1;
var intervals = [];

function nextChart() {
	hideAllCharts();
	actual_chart++;
	if (actual_chart >= chartArray.length)
		actual_chart = 0;
	showChart(document.getElementById(chartArray[actual_chart]["fragment"]));
}

function prevChart() {
	hideAllCharts();
	actual_chart--;
	if (actual_chart === -1)
		actual_chart = chartArray.length-1;
	showChart(document.getElementById(chartArray[actual_chart]["fragment"]));
}

function paintChart(){
    if (chartArray.length === 1) {
        document.getElementById("slideshow").style.display = "block";
    } else if (chartArray.length === 2) {
		document.getElementById("buttons_charts").style.display="block";
	}
	if (chartArray.length >= 1) 
		hideAllCharts();
	showChart(document.getElementById("fragment-"+id));
}

function addTab(textName) {
	var iDiv = document.createElement('div');
	iDiv.id = "fragment-"+id;
	iDiv.style = 'max-height: 600px; max-width: 800px;' ;
	var iCanvas = document.createElement('canvas');
	iCanvas.id = 'myChart' + id;
	iDiv.appendChild(iCanvas);
	tabs.appendChild(iDiv);
	showChart(document.getElementById("fragment-"+id));
    if (chartArray.length === 1) {
        document.getElementById("slideshow").style.display = "block";
    } else if (chartArray.length === 2) {
		document.getElementById("buttons_charts").style.display="block";
	}
	if (chartArray.length >= 1) {
		hideAllCharts();
	}
	return iCanvas.id;
}


function createChart(number) {
	var exists = -1;
	var textName = chartInfo[number][0]["title"];
	var time = chartInfo[number][0]["time"];
	for (var n = 0;n<chartArray.length;n++) {
		if (textName === chartArray[n]["name"]) {
			exists = n;
			break;
		}
	}
    if (exists === -1) {
		initChart(number, addTab(textName), textName, time);
	} else {
		addtoChart(number, exists, time);
	}
}

function addtoChart(number, exists, time) {
	var lengthData = chartArray[exists]["chart"].data.datasets.length;
	for (var i = 1; i < chartInfo[number].length; i++) {
		var ejey = chartInfo[number][i];
		var dataSet = {
			fill: false,
			borderColor: "rgba(" + randomScalingFactor() + "," + randomScalingFactor() + "," + randomScalingFactor() + ",1)",
			label: ejey["name"],
			data: []
		};
		chartArray[exists]["chart"].data.datasets.push(dataSet);
	}
	chartArray[exists]["chart"].update();
	var c =window.setInterval(getData, time, chartArray[exists], chartInfo[number], lengthData);
	intervals.push(c);
}

function initChart(number, place, textName, time) {
	var ctx = document.getElementById(place).getContext('2d');
	var ejex = chartInfo[number][0];
	var config = {
		type: 'line',
		data: {},
		options: {
			responsive: true,
			animation: false,
			title: {
				display: true,
				text: textName
			},
			tooltips: {
				mode: 'index'
			},
			hover: {
				mode: 'index'
			},
			scales: {
				xAxes: [{
						type: 'linear',
						position: 'bottom',
						scaleLabel: {
							display: true,
							labelString: ejex["name"]
						}
					}
				],
				yAxes: [{
						display: true,
						scaleLabel: {
							display: true,
							labelString: 'Value'
						}
					}
				]
			}
		}
	};
	var chart = new Chart(ctx, config);
	for (var i = 1; i < chartInfo[number].length; i++) {
		var ejey = chartInfo[number][i];
		var dataSet = {
			fill: false,
			borderColor: "rgba(" + randomScalingFactor() + "," + randomScalingFactor() + "," + randomScalingFactor() + ",1)",
			label: ejey["name"],
			data: []
		};
		chart.data.datasets.push(dataSet);
		chart.update();
	}
	chartArray.push({"name":textName,"timer":null,"chart":chart,"fragment": ("fragment-"+id)});
	actual_chart = chartArray.length-1;
	paintChart(); // Before change id
	id++;
	var c = window.setInterval(getData,time,chartArray[chartArray.length-1], chartInfo[number],0);
	intervals.push(c);
}

var getData = function (chart,info,dataSetNumber) {
		if(record){
			var x = eval(info[0]["value"]);
			for (var i = 1; i < info.length; i++) {
				var val = info[i];
				var y = eval(val["value"]);
				chart["chart"].data.datasets[i - 1 + dataSetNumber].data[chart["chart"].data.datasets[i - 1 + dataSetNumber].data.length] = {
					x:x,
					y:y
				};
				if ((info[0]["checkBox"])) {
					if (i === 1)
						info[0]["number"] = info[0]["number"] - 1;
					if (info[0]["number"] < 0)
						chart["chart"].data.datasets[i - 1 + dataSetNumber].data.splice(0, 1); // remove first data point
				}
			}
			chart["chart"].update();
		}
};

var randomScalingFactor = function () {
	return Math.round(Math.random() * 255) + 1
};

function rec(bool) {
	document.getElementById('clean_chart').disabled = true;
	record = bool;
	if (!bool) {
		for (var i in intervals) 
			 window.clearInterval(intervals[i]);
		intervals = [];
		document.getElementById('clean_chart').disabled = false;
	}
}

/**
 * Exports and triggers download of chart data as CSV file
 */
/*function toCSV(data) {
	var csvContent;
	csvContent = "data:text/csv;charset=utf-8\n";
	for (var i = 0; i < data.length; i++) {
		var elemento = data[i];
		csvContent = csvContent + elemento.x + ", " + elemento.y + "\n";
	}

	var encodedUri = encodeURI(csvContent);
	window.open(encodedUri);
}*/



function hideAllCharts() {
	for (var k = 0; k < chartArray.length; k++) {
		document.getElementById(chartArray[k]["fragment"]).style.display = "none";
	}
	if(chartArray.length<1){
        document.getElementById("slideshow").style.display = "none";
		//document.getElementById("buttons_charts").style.display="none";
	}
	if(chartArray.length==1){
        //document.getElementById("slideshow").style.display = "none";
		document.getElementById("buttons_charts").style.display="none";
	}
}

function showChart(elem) {
	elem.style.display = "block";
}

/////////////////////// TOOLBOX CONFIGURATION
strings = function (workspace) {
	var xmlList = [];
	if (keys_string.length > 0) {
		var blockText = '<xml>' + '<block type="set_model_variable_string"></block>' + '</xml>';
		var block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);
		blockText = '<xml>' + '<block type="get_model_variable_string"></block>' + '</xml>';
		block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);
	}
	return xmlList;
};

numbers = function (workspace) {
	var xmlList = [];
	if (keys_number.length > 0) {
		var blockText = '<xml>' + '<block type="set_model_variable_number"></block>' + '</xml>';
		var block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);
		blockText = '<xml>' + '<block type="get_model_variable_number"></block>' + '</xml>';
		block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);
	}
	return xmlList;
};

booleans = function (workspace) {
	var xmlList = [];
	if (keys_boolean.length > 0) {
		var blockText = '<xml>' + '<block type="set_model_variable_boolean"></block>' + '</xml>';
		var block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);
		blockText = '<xml>' + '<block type="get_model_variable_boolean"></block>' + '</xml>';
		block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);
	}
	return xmlList;
};

others = function (workspace) {
	var xmlList = [];
	if (keys_others.length > 0) {
		var blockText = '<xml>' + '<block type="set_model_variable_others"></block>' + '</xml>';
		var block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);
		blockText = '<xml>' + '<block type="get_model_variable_others"></block>' + '</xml>';
		block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);
	}
	return xmlList;
};


functions = function (workspace) {
	var xmlList = [];
	var blockText = '<xml>' + '<block type="evaluation"></block>' + '</xml>';
	var block = Blockly.Xml.textToDom(blockText).firstChild;
	xmlList.push(block);
	if (keys_others.length > 0) {
		blockText = '<xml>' + '<block type="replacefunc"><value name="return"><shadow type="math_number"><field name="NUM">0</field></shadow></value></block>' + '</xml>';
		block = Blockly.Xml.textToDom(blockText).firstChild;
		xmlList.push(block);
	}
	return xmlList;
};

///////////////// ONLOAD //////////////////////
window.onload = function () {
	if (typeof _model !== 'undefined') // Any scope
	{
		///////////////////// BLOCKLY BLOCKS ///////////////////////////
		loadModelBlocks();
		///////////////////// JAVASCRIPT CODE FOR BLOCKLY ///////////////////////////
		loadJavaScriptModelBlocks();
	}
	///////////// INIT CHART ///////////////////////////////
	//cleanChart(); // To initialize the chart;
	//alert(toolbox);
	//toolbox = toolbox.replace("<category name="Boolean"><block type="set_model_variable_boolean"></block><block type="get_model_variable_boolean"></block></category>);
	workspace = Blockly.inject('blocklyDiv', {
		grid:
          {spacing: 25,
           length: 3,
           colour: '#ccc',
           snap: true},
		media: 'blockly/media/',
		toolbox: toolbox,
		collapse : true, 
		zoom:
           {controls: true}
		});
	workspace.registerToolboxCategoryCallback('strings', strings);
	workspace.registerToolboxCategoryCallback('numbers', numbers);
	workspace.registerToolboxCategoryCallback('booleans', booleans);
	workspace.registerToolboxCategoryCallback('others', others);
	workspace.registerToolboxCategoryCallback('functions', functions);
	if (typeof initial !== 'undefined') {
		var xmlDom = Blockly.Xml.textToDom(initial);
		Blockly.Xml.domToWorkspace(xmlDom, workspace);
	}

};