
var keys_boolean = [];
var keys_number = [];
var keys_string = [];
var keys_others = [];
var keys_functions = [];
	 
function loadModelBlocks(){
	var obj = _model._userSerialize();
	var keys = [];
	var condition = false;
	var i = 1;
	for(var k in obj){
		var dupla = []
		dupla.push(k);
		dupla.push(k);
		switch (typeof obj[k]){
			case 'string':
				keys_string.push(dupla);
				break;
			case 'number':
				keys_number.push(dupla);
				break;
			case 'boolean':
				keys_boolean.push(dupla);
				break;
			case 'function':
				keys_functions.push(dupla);
				break;
			default:
				keys_others.push(dupla);
				break;
		}
		keys.push(dupla);
		i++;
	}
	
	Blockly.Blocks['wait'] = {
	  init: function() {
		this.appendDummyInput()
			.appendField("wait")
			.appendField(new Blockly.FieldNumber(0, 0, 600), "TIME")
			.appendField("seconds to do");
		this.appendStatementInput("CODE")
			.setCheck(null);
		this.setPreviousStatement(true, "null");
		this.setNextStatement(true, "null");
		this.setColour(60);
		this.setTooltip('');
	  }
	};
	
	Blockly.Blocks['get_model_variable'] = {
    		  init: function() {
    		    this.appendDummyInput()
    		        .appendField("get")
    		        .appendField(new Blockly.FieldDropdown(keys), "modelvariables");
    		    this.setOutput(true, null);
    		    this.setColour(290);
    		    this.setTooltip('');
    		  }
    		};
    

      Blockly.Blocks['set_model_variable'] = {
    		  init: function() {
    		    this.appendValueInput("NAME")
    		        .appendField("set")
    		        .appendField(new Blockly.FieldDropdown(keys), "model variables")
    		        .appendField("to");
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(120);
    		    this.setTooltip('');
    		  }
    		};
			
	if(keys_boolean.length > 0){
      Blockly.Blocks['get_model_variable_boolean'] = {
    		  init: function() {
    		    this.appendDummyInput()
    		        .appendField("get")
    		        .appendField(new Blockly.FieldDropdown(keys_boolean), "modelvariables1");
    		    this.setOutput(true, "Boolean");
    		    this.setColour(290);
    		    this.setTooltip('');
    		  }
    		};
      
      Blockly.Blocks['set_model_variable_boolean'] = {
    		  init: function() {
    		    this.appendValueInput("NAME")
    		        .appendField("set")
    		        .appendField(new Blockly.FieldDropdown(keys_boolean), "model variables1")
    		        .appendField("to")
    		    	.setCheck("Boolean");
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(120);
    		    this.setTooltip('');
    		  }
    		};
    }  
	if(keys_string.length > 0){
      Blockly.Blocks['get_model_variable_string'] = {
    		  init: function() {
    		    this.appendDummyInput()
    		        .appendField("get")
    		        .appendField(new Blockly.FieldDropdown(keys_string), "modelvariables2");
    		    this.setOutput(true, "Text");
    		    this.setColour(290);
    		    this.setTooltip('');
    		  }
    		};
      
      Blockly.Blocks['set_model_variable_string'] = {
    		  init: function() {
    		    this.appendValueInput("NAME")
    		        .appendField("set")
    		        .appendField(new Blockly.FieldDropdown(keys_string), "model variables2")
    		        .appendField("to")
    		    	.setCheck("Text");
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(120);
    		    this.setTooltip('');
    		  }
    		};
	}		
	
	if(keys_number.length > 0){
      Blockly.Blocks['get_model_variable_number'] = {
    		  init: function() {
    		    this.appendDummyInput()
    		        .appendField("get")
    		        .appendField(new Blockly.FieldDropdown(keys_number), "modelvariables3");
    		    this.setOutput(true, "Number");
    		    this.setColour(290);
    		    this.setTooltip('');
    		  }
    		};
      
      Blockly.Blocks['set_model_variable_number'] = {
    		  init: function() {
    		    this.appendValueInput("NAME")
    		        .appendField("set")
    		        .appendField(new Blockly.FieldDropdown(keys_number), "model variables3")
    		        .appendField("to")
    		    	.setCheck("Number");
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(120);
    		    this.setTooltip('');
    		  }
    		};
	}
	if(keys_others.length > 0){
      Blockly.Blocks['get_model_variable_others'] = {
    		  init: function() {
    		    this.appendDummyInput()
    		        .appendField("get")
    		        .appendField(new Blockly.FieldDropdown(keys_others), "modelvariables4");
    		    this.setOutput(true, null);
    		    this.setColour(290);
    		    this.setTooltip('');
    		  }
    		};
      
      Blockly.Blocks['set_model_variable_others'] = {
    		  init: function() {
    		    this.appendValueInput("NAME")
    		        .appendField("set")
    		        .appendField(new Blockly.FieldDropdown(keys_others), "model variables4")
    		        .appendField("to")
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(120);
    		    this.setTooltip('');
    		  }
    		};
	}
	
	if(keys_functions.length > 0){
	  Blockly.Blocks['get_model_variable_funs'] = {
    		  init: function() {
    		    this.appendDummyInput()
    		        .appendField("get")
    		        .appendField(new Blockly.FieldDropdown(keys_functions), "modelvariables5");
    		    this.setOutput(true, null);
    		    this.setColour(290);
    		    this.setTooltip('');
    		  }
    		};
      
      Blockly.Blocks['set_model_variable_funs'] = {
    		  init: function() {
    		    this.appendValueInput("NAME")
    		        .appendField("set")
    		        .appendField(new Blockly.FieldDropdown(keys_functions), "model variables5")
    		        .appendField("to")
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(120);
    		    this.setTooltip('');
    		  }
    		};
    }  
      Blockly.Blocks['play_lab'] = {
    		  init: function() {
    		    this.appendDummyInput()
    		        .appendField("start the lab");
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(0);
    		    this.setTooltip('');
    		  }
      };
	  
	  Blockly.Blocks['playStop_lab'] = {
    		  init: function() {
    		    this.appendValueInput("NAME")
    		        .appendField("play the lab for")
    		    	.setCheck("Number");
				this.appendDummyInput()
    		        .appendField("seconds");
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(0);
    		    this.setTooltip('');
    		  }
      };
	  
      Blockly.Blocks['pause_lab'] = {
    		  init: function() {
    		    this.appendDummyInput()
    		        .appendField("pause the lab");
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(0);
    		    this.setTooltip('');
    		  }
      };
	  
      Blockly.Blocks['initialize_lab'] = {
    		  init: function() {
    		    this.appendDummyInput()
    		        .appendField("initialize the lab");
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(0);
    		    this.setTooltip('');
    		  }
      };
      Blockly.Blocks['reset_lab'] = {
    		  init: function() {
    		    this.appendDummyInput()
    		        .appendField("reset the lab");
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(0);
    		    this.setTooltip('');
    		  }
      };
	  
      
      Blockly.Blocks['event'] = {
    		  init: function() {
    		    this.appendValueInput("NAME1")
    		        .appendField("When ")
    	        	.setCheck("Number")
				this.appendDummyInput()
					.appendField("becomes 0");
    		    this.appendStatementInput("NAME2")
    		        .setCheck(null)
    		        .appendField("do");
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(60);
    		    this.setTooltip('');
    		  }
      };
	  
	  Blockly.Blocks['fixedRelation'] = {
    		  init: function() {
				this.appendDummyInput()
					.appendField("In every step do");
    		    this.appendStatementInput("NAME")
    		        .setCheck(null);
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(60);
    		    this.setTooltip('');
    		  }
      };
	  
	  
	  Blockly.Blocks['createChartSquematic'] = {
		  init: function() {
			this.appendDummyInput()
				.appendField("Create Chart");
			this.appendStatementInput("NAME")
				.setCheck(null);
			this.setColour(200);
			this.setTooltip('');
		  }
		};
	  
	  Blockly.Blocks['Y Axis'] = {
		  init: function() {
			this.appendDummyInput()
				.appendField("Y Axis");
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(200);
			this.setTooltip('');
		  }
		};
	  
	  
	  Blockly.Blocks['createChart'] = {
	  init: function() {
		this.appendDummyInput()
			.appendField("Create Chart")
			.appendField(new Blockly.FieldTextInput("name"), "Chart_NAME");
		this.appendDummyInput()
			.appendField("for every")
			.appendField(new Blockly.FieldNumber(0), "time")
			.appendField("milliseconds");
		this.appendValueInput("x")
			.setCheck(null)
			.setAlign(Blockly.ALIGN_RIGHT)
			.appendField("0. X Axis")
			.appendField(new Blockly.FieldTextInput("columnName0"), "cName0");
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
  mutationToDom: function() {
    var container = document.createElement('mutation');
    container.setAttribute('items', this.itemCount_);
    return container;
  },
  /**
   * Parse XML to restore the list inputs.
   * @param {!Element} xmlElement XML storage element.
   * @this Blockly.Block
   */
  domToMutation: function(xmlElement) {
    this.itemCount_ = parseInt(xmlElement.getAttribute('items'), 10);
    this.updateShape_();
  },
  /**
   * Populate the mutator's dialog with this block's components.
   * @param {!Blockly.Workspace} workspace Mutator's workspace.
   * @return {!Blockly.Block} Root block in mutator.
   * @this Blockly.Block
   */
  decompose: function(workspace) {
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
  compose: function(containerBlock) {
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
      if (connection && connections.indexOf(connection) == -1) {
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
  saveConnections: function(containerBlock) {
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
  updateShape_: function() {
    if (this.itemCount_ && this.getInput('EMPTY')) {
      this.removeInput('EMPTY');
    } 
    // Add new inputs.
    for (var i = 0; i < this.itemCount_; i++) {
      if (!this.getInput('ADD' + i)) {
	  
        var input = this.appendValueInput('ADD' + i);
          input.appendField((i+1)+". Y Axis");
		  input.appendField(new Blockly.FieldTextInput("columnName"+(i+1)), "nameY"+i);
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
    		  init: function() {
    		    this.appendDummyInput()
    		        .appendField("start recording data");
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(200);
    		    this.setTooltip('');
    		  }
      };
	  
	  Blockly.Blocks['stop_rec'] = {
    		  init: function() {
    		    this.appendDummyInput()
    		        .appendField("stop recording data");
    		    this.setPreviousStatement(true, null);
    		    this.setNextStatement(true, null);
    		    this.setColour(200);
    		    this.setTooltip('');
    		  }
      };
	  
	  Blockly.Blocks['record_var'] = {
	  init: function() {
		this.appendDummyInput()
    		        .appendField("record")
    		        .appendField(new Blockly.FieldDropdown(keys), "modelvariables")
					.appendField("every")
					.appendField(new Blockly.FieldNumber(0, 1, 99999), "NAME1")
					.appendField("milliseconds");
		this.setColour(200);
		this.setTooltip('');
	  }
	};
	
	// EQUATION
	Blockly.Blocks['resultado'] = {
	  init: function() {
		this.appendValueInput("statement1")
			.setCheck("Number")
			.appendField("equation =");
		this.setColour(210);
		this.setTooltip('');
	  }
	};
	
	
	
}




var condition = false;
var chartNumber = 0;
var chartInfo = [];

function loadJavaScriptModelBlocks(){
	
	  Blockly.JavaScript['start_rec'] = function(block) {return "rec(true);\n";}; 
      Blockly.JavaScript['stop_rec'] = function(block) {return "rec(false);\n";}; 
      Blockly.JavaScript['record_var'] = function(block) {
			 var dropdown_d = block.getFieldValue("modelvariables");
			 var number_name = block.getFieldValue('NAME1');
          	code = "recordvar(\""+dropdown_d+"\","+number_name+");\n";
          return [code, Blockly.JavaScript.NONE];
	}; 
	
     /* Blockly.JavaScript['createChart'] = function(block) {
			 var name = Blockly.JavaScript.valueToCode(block, 'NAME');
          	code = "createChart("+name+");\n";
          return [code, Blockly.JavaScript.NONE];
	}; */
	  
	  
	  Blockly.JavaScript['createChart'] = function(block) {
    	  var name = block.getFieldValue('Chart_NAME');
    	  var time = block.getFieldValue('time');
		  var value_name = Blockly.JavaScript.valueToCode(block, 'x', Blockly.JavaScript.ORDER_ATOMIC);
		  var c0name = block.getFieldValue('cName0');
		  //alert(block.itemCount_);
		  var chartInfo2=[];
		  chartInfo2.push({"name":c0name,"value":value_name});
		  //var yaxis = "";
		  for (var i = 0; i < block.itemCount_; i++) {
			var cname = block.getFieldValue('nameY'+i);
			var y = Blockly.JavaScript.valueToCode(block,('ADD' + i), Blockly.JavaScript.ORDER_ATOMIC);
			//var y = block..getInput('ADD' + i);
			//yaxis=yaxis+",";
			//yaxis=yaxis+"{\""+cname+"\":"+y+"}";
			chartInfo2.push({"name":cname,"value":y});
		  }
		  chartInfo.push(chartInfo2);
		  var code = "createChart(\""+name+"\", "+time+", "+chartNumber+");\n";
		  chartNumber++;
		  return [code, Blockly.JavaScript.NONE];
		};
	  
	  
	  
      Blockly.JavaScript['event'] = function(block) {
    	  condition= true;
		  var statements_name1 = Blockly.JavaScript.valueToCode(block, 'NAME1');
		  var statements_name2 = Blockly.JavaScript.statementToCode(block, 'NAME2');
		  condition = false;
		  // TODO: Assemble JavaScript into code variable.
		  var condString = statements_name1+";";
		  var actString = statements_name2;
		  events.push({"num":num_events,"cond":condString,"act":actString});
		  code = "addEvent("+num_events+");\n";
		  num_events++;
		  return code;
		};
		
		
	  Blockly.JavaScript['fixedRelation'] = function(block) {
    	  condition= true;
		  var statements_name1 = Blockly.JavaScript.statementToCode(block, 'NAME');
		  condition = false;
		  fixedStatements.push(statements_name1);
		  code = "addFixedRelation("+(fixedStatements.length-1)+");\n";
		  return code;
		};
      
      
      
      function getJS(block,text){
          var dropdown_d = block.getFieldValue(text);
          var code;
          if(!condition)
          	code = "getValueModel(\""+dropdown_d+"\")";
          else
          	code = dropdown_d;
          return [code, Blockly.JavaScript.NONE];
    	  
      };
      function setJS(block,text){
    	  var dropdown_d = block.getFieldValue(text);
          var value_name = Blockly.JavaScript.valueToCode(block, 'NAME', Blockly.JavaScript.ORDER_ATOMIC);
		  var code;
		  var obj = _model._userSerialize();
		  if((typeof(obj[dropdown_d])).localeCompare("function")==0){
			  value_name=value_name.replace("()","");
		  }
		  if(!condition)
			code = "setValueModel('"+dropdown_d+"', "+value_name+");\n";
		  else
				code = dropdown_d+" = "+value_name+";\n";
          return code;
    	  
      };
    
      Blockly.JavaScript['get_model_variable_boolean'] = function(block) {return getJS(block,"modelvariables1"); };
      Blockly.JavaScript['set_model_variable_boolean'] = function(block) {return setJS(block,"model variables1");}; 
      
      Blockly.JavaScript['get_model_variable_string'] = function(block) {return getJS(block,"modelvariables2"); };
      Blockly.JavaScript['set_model_variable_string'] = function(block) {return setJS(block,"model variables2");}; 
      
      Blockly.JavaScript['get_model_variable_number'] = function(block) {return getJS(block,"modelvariables3"); };
      Blockly.JavaScript['set_model_variable_number'] = function(block) {return setJS(block,"model variables3");}; 
      
      Blockly.JavaScript['get_model_variable_others'] = function(block) {return getJS(block,"modelvariables4"); };
      Blockly.JavaScript['set_model_variable_others'] = function(block) {return setJS(block,"model variables4");}; 
      
      Blockly.JavaScript['get_model_variable'] = function(block) {return getJS(block,"modelvariables"); };
      Blockly.JavaScript['set_model_variable'] = function(block) {return setJS(block,"model variables");}; 
      

      Blockly.JavaScript['play_lab'] = function(block) {return "play();\n";}; 
	  
      Blockly.JavaScript['playStop_lab'] = function(block){
			var value_name = Blockly.JavaScript.valueToCode(block, 'NAME', Blockly.JavaScript.ORDER_ATOMIC);
          	code = "playStop("+value_name+");\n";
          return code;
		}; 
      Blockly.JavaScript['pause_lab'] = function(block) {return "pause();\n";}; 
      Blockly.JavaScript['initialize_lab'] = function(block) {return "initialize();\n";}; 
      Blockly.JavaScript['reset_lab'] = function(block) {return "reset();\n";}; 
	
	
	//Blockly.JavaScript.init=function(a){Blockly.JavaScript.definitions_=Object.create(null);Blockly.JavaScript.functionNames_=Object.create(null);Blockly.JavaScript.variableDB_?Blockly.JavaScript.variableDB_.reset():Blockly.JavaScript.variableDB_=new Blockly.Names(Blockly.JavaScript.RESERVED_WORDS_);var b=[];a=a.variableList;if(a.length){for(var c=0;c<a.length;c++)b[c]=Blockly.JavaScript.variableDB_.getName(a[c],Blockly.Variables.NAME_TYPE);Blockly.JavaScript.definitions_.variables="var "+b.join(", ")+";"}};
	
	
	Blockly.JavaScript.init=function(a){Blockly.JavaScript.definitions_=Object.create(null);Blockly.JavaScript.functionNames_=Object.create(null);Blockly.JavaScript.variableDB_?Blockly.JavaScript.variableDB_.reset():Blockly.JavaScript.variableDB_=new Blockly.Names(Blockly.JavaScript.RESERVED_WORDS_);var b=[];a=a.variableList;if(a.length){for(var c=0;c<a.length;c++)b[c]=Blockly.JavaScript.variableDB_.getName(a[c],Blockly.Variables.NAME_TYPE);Blockly.JavaScript.definitions_.variables=b.join("=0, ")+"=0;"}};
	
	Blockly.JavaScript.variables_get=function(a){
		//if(!condition)
			return[Blockly.JavaScript.variableDB_.getName(a.getFieldValue("VAR"),Blockly.Variables.NAME_TYPE),Blockly.JavaScript.ORDER_ATOMIC]
		//else
			//return ["window."+Blockly.JavaScript.variableDB_.getName(a.getFieldValue("VAR"),Blockly.Variables.NAME_TYPE),Blockly.JavaScript.ORDER_ATOMIC]

	};
	
	Blockly.JavaScript.variables_set=function(a){
		var b=Blockly.JavaScript.valueToCode(a,"VALUE",Blockly.JavaScript.ORDER_ASSIGNMENT)||"0";
		//if(!condition)
			return Blockly.JavaScript.variableDB_.getName(a.getFieldValue("VAR"),Blockly.Variables.NAME_TYPE)+" = "+b+";\n"
		//else
			//return "window."+Blockly.JavaScript.variableDB_.getName(a.getFieldValue("VAR"),Blockly.Variables.NAME_TYPE)+" = "+b+";\n"
	};
	
	
	 Blockly.JavaScript['resultado'] = function(block) {
    	  condition= true;
		  var value_name = Blockly.JavaScript.valueToCode(block, 'statement1', Blockly.JavaScript.ORDER_ATOMIC);
		  condition = false;
		  return value_name+"\n";
		};
	
	Blockly.JavaScript['wait'] = function(block) {
		  var number_name = block.getFieldValue('TIME');
		  number_name = Number(number_name)*1000;
		  var statements_name = Blockly.JavaScript.statementToCode(block, 'CODE');
		  var code = 'setTimeout(function() {\n'+statements_name+'\n}, '+number_name+');'+'\n';
		  return code;
		};
	
}