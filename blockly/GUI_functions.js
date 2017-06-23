///////////////

/**
 * Construct the blocks required by the flyout for the colours category.
 * @param {!Blockly.Workspace} workspace The workspace this flyout is for.
 * @return {!Array.<!Element>} Array of XML block elements.
 */
strings = function(workspace) {
  var xmlList = [];
  if (keys_string.length>0) {
	  var blockText = '<xml>' +  '<block type="set_model_variable_string"></block>' + '</xml>';
	  var block = Blockly.Xml.textToDom(blockText).firstChild;
	  xmlList.push(block);
	  blockText = '<xml>' +  '<block type="get_model_variable_string"></block>' + '</xml>';
	  block = Blockly.Xml.textToDom(blockText).firstChild;
	  xmlList.push(block);
  }
  return xmlList;
};

numbers = function(workspace) {
  var xmlList = [];
  if (keys_number.length>0) {
	  var blockText = '<xml>' +  '<block type="set_model_variable_number"></block>' + '</xml>';
	  var block = Blockly.Xml.textToDom(blockText).firstChild;
	  xmlList.push(block);
	  blockText = '<xml>' +  '<block type="get_model_variable_number"></block>' + '</xml>';
	  block = Blockly.Xml.textToDom(blockText).firstChild;
	  xmlList.push(block);
  }
  return xmlList;
};

booleans = function(workspace) {
  var xmlList = [];
  if (keys_boolean.length>0) {
	  var blockText = '<xml>' +  '<block type="set_model_variable_boolean"></block>' + '</xml>';
	  var block = Blockly.Xml.textToDom(blockText).firstChild;
	  xmlList.push(block);
	  blockText = '<xml>' +  '<block type="get_model_variable_boolean"></block>' + '</xml>';
	  block = Blockly.Xml.textToDom(blockText).firstChild;
	  xmlList.push(block);
  }
  return xmlList;
};

others = function(workspace) {
  var xmlList = [];
  if (keys_others.length>0) {
	  var blockText = '<xml>' +  '<block type="set_model_variable_others"></block>' + '</xml>';
	  var block = Blockly.Xml.textToDom(blockText).firstChild;
	  xmlList.push(block);
	  blockText = '<xml>' +  '<block type="get_model_variable_others"></block>' + '</xml>';
	  block = Blockly.Xml.textToDom(blockText).firstChild;
	  xmlList.push(block);
  }
  return xmlList;
};

functions = function(workspace) {
  var xmlList = [];
  var blockText = '<xml>' +  '<block type="play_lab"></block>' + '</xml>';
  var block = Blockly.Xml.textToDom(blockText).firstChild;
  xmlList.push(block);
  var blockText = '<xml>' +  '<block type="pause_lab"></block>' + '</xml>';
  var block = Blockly.Xml.textToDom(blockText).firstChild;
  xmlList.push(block);
  var blockText = '<xml>' +  '<block type="reset_lab"></block>' + '</xml>';
  var block = Blockly.Xml.textToDom(blockText).firstChild;
  xmlList.push(block);
  var blockText = '<xml>' +  '<block type="evaluation"><value name="expre"><shadow type="text"><field name="TEXT">abc</field></shadow></value></block>' + '</xml>';
  var block = Blockly.Xml.textToDom(blockText).firstChild;
  xmlList.push(block);
  if (keys_others.length>0) {
	  var blockText = '<xml>' +  '<block type="replacefunc"></block>' + '</xml>';
	  var block = Blockly.Xml.textToDom(blockText).firstChild;
	  xmlList.push(block);
  }
  return xmlList;
};
  
 window.onload = function() 
  {
	if (typeof _model != 'undefined') // Any scope
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
		workspace = Blockly.inject('blocklyDiv',{media: 'blockly/media/',toolbox: toolbox});
		workspace.registerToolboxCategoryCallback('strings', strings);
		workspace.registerToolboxCategoryCallback('numbers', numbers);
		workspace.registerToolboxCategoryCallback('booleans', booleans);
		workspace.registerToolboxCategoryCallback('others', others);
		workspace.registerToolboxCategoryCallback('functions', functions);
		if(typeof initial != 'undefined'){
			var xmlDom = Blockly.Xml.textToDom(initial);
			Blockly.Xml.domToWorkspace(xmlDom,workspace);
		}
		
		
		
		
  }
  

  
var workspace;
var myInterpreter;
var code;

myInterpreter = null;
  

 function initApi(interpreter, scope) {
 
  
  // Add an API function for the record() block.
  var wrapper = function(text,number) {
        text = text ? text.toString() : '';
        return interpreter.createPrimitive(recordvar(text,number));
      };
      interpreter.setProperty(scope, 'recordvar',
          interpreter.createNativeFunction(wrapper));
  
	// Add an API function for the rec() block.
      var wrapper = function(bool) {
		if((bool.toString().localeCompare("true")==0))
        	bool=true;
        else if((bool.toString().localeCompare("false")==0))
        	bool=false;
        return interpreter.createPrimitive(rec(bool));
      };
      interpreter.setProperty(scope, 'rec',
          interpreter.createNativeFunction(wrapper));
		  
       // Add an API function for the initialize block.
      var wrapper = function() {
        return interpreter.createPrimitive(initialize());
      };
      interpreter.setProperty(scope, 'initialize',
          interpreter.createNativeFunction(wrapper));
		  
	 // Add an API function for the createChart() block.
     /* var wrapper = function(text) {
        text = text ? text.toString() : '';
        return interpreter.createPrimitive(buildDocument(text));
      };
      interpreter.setProperty(scope, 'createChart',
          interpreter.createNativeFunction(wrapper));*/
		  
      // Add an API function for the alert() block.
      var wrapper = function(text) {
        text = text ? text.toString() : '';
        return interpreter.createPrimitive(alert(text));
      };
      interpreter.setProperty(scope, 'alert',
          interpreter.createNativeFunction(wrapper));

      // Add an API function for the prompt() block.
      var wrapper = function(text) {
        text = text ? text.toString() : '';
        return interpreter.createPrimitive(prompt(text));
      };
      interpreter.setProperty(scope, 'prompt',
          interpreter.createNativeFunction(wrapper));
      
   // Add an API function for the addEvent() block.
      var wrapper = function(number) {
        return interpreter.createPrimitive(selectEvent(number));
      };
      interpreter.setProperty(scope, 'addEvent',
          interpreter.createNativeFunction(wrapper));

   // Add an API function for the getValueModel() block.
      var wrapper = function(text) {
        text = text ? text.toString() : '';
        return interpreter.createPrimitive(getValueModel(text));
      };
      interpreter.setProperty(scope, 'getValueModel',
          interpreter.createNativeFunction(wrapper));
      
	  // Add an API function for the addFixedRelation() block.
      var wrapper = function(number) {
        return interpreter.createPrimitive(addFixedRelation(number));
      };
      interpreter.setProperty(scope, 'addFixedRelation',
          interpreter.createNativeFunction(wrapper));
      
	  
      // Add an API function for the play block.
      var wrapper = function() {
        return interpreter.createPrimitive(play());
      };
      interpreter.setProperty(scope, 'play',
          interpreter.createNativeFunction(wrapper));
      
	  
      // Add an API function for the playStop block.
      var wrapper = function(number) {
        return interpreter.createPrimitive(playStop(number));
      };
      interpreter.setProperty(scope, 'playStop',
          interpreter.createNativeFunction(wrapper));
	  
   // Add an API function for the pause block.
      var wrapper = function() {
        return interpreter.createPrimitive(pause());
      };
      interpreter.setProperty(scope, 'pause',
          interpreter.createNativeFunction(wrapper));
      
   // Add an API function for the reset block.
      var wrapper = function() {
        return interpreter.createPrimitive(_model.reset());
      };
      interpreter.setProperty(scope, 'reset',
          interpreter.createNativeFunction(wrapper));
      
   // Add an API function for the setValueModel() block.
      var wrapper = function(text,p2) {
        text = text ? text.toString() : '';
        if((p2.toString().localeCompare("true")==0))
        	p2=true;
        else if((p2.toString().localeCompare("false")==0))
        	p2=false;
        /*else
        	p2 = parseFloat(p2);*/
        return interpreter.createPrimitive(setValueModel(text,p2));
      };
      interpreter.setProperty(scope, 'setValueModel',
          interpreter.createNativeFunction(wrapper));
      
      // Add an API function for highlighting blocks.
      var wrapper = function(id) {
        id = id ? id.toString() : '';
        return interpreter.createPrimitive(highlightBlock(id));
      };
      interpreter.setProperty(scope, 'highlightBlock',
          interpreter.createNativeFunction(wrapper));
		  
     // Add an API function for eval blocks.
      var wrapper = function(text) {
         text = text ? text.toString() : '';
        return interpreter.createPrimitive(evaluate(text));
      };
      interpreter.setProperty(scope, 'evaluate',
          interpreter.createNativeFunction(wrapper));
		  
	  // Add an API function for reInitLab blocks.
      var wrapper = function() {
        return interpreter.createPrimitive(reInitLab());
      };
      interpreter.setProperty(scope, 'reInitLab',
          interpreter.createNativeFunction(wrapper));
		  
	 // Add an API function for the setTimeStep block.
      var wrapper = function(number) {
        return interpreter.createPrimitive(setTimeStep(number));
      };
      interpreter.setProperty(scope, 'setTimeStep',
          interpreter.createNativeFunction(wrapper));
		  
       // Add an API function for reInitLab blocks.
      var wrapper = function(a,b,c,d) {
		  a = a ? a.toString() : '';
		  b = b ? b.toString() : '';
		  c = c ? c.toString() : '';
        return interpreter.createPrimitive(replaceFunction(a,b,c,d));
      };
      interpreter.setProperty(scope, 'replaceFunction',
          interpreter.createNativeFunction(wrapper));
		 
		  
		//reateChart("+name+","+time+",[{"+c0name+":"+value_name+"}"+yaxis+"]);\n";
  // Add an API function for the createChart() block.
  var wrapper = function(text,number,columns) {
        text = text ? text.toString() : '';
        return interpreter.createPrimitive(createChart(text,number,columns));
      };
      interpreter.setProperty(scope, 'createChart',
          interpreter.createNativeFunction(wrapper));

    }
	

	
/////////////// INTERPRETER

 var highlightPause = false;

    function highlightBlock(id) {
      workspace.highlightBlock(id);
      highlightPause = true;
    }

window.LoopTrap = 10;
    function parseCode() {
      // Generate JavaScript code and parse it.
      Blockly.JavaScript.STATEMENT_PREFIX = 'highlightBlock(%1);\n';
      Blockly.JavaScript.addReservedWords('highlightBlock');
      Blockly.JavaScript.addReservedWords('LoopTrap');
      var code = Blockly.JavaScript.workspaceToCode(workspace);
      //Blockly.JavaScript.INFINITE_LOOP_TRAP = null;
	  code = "reInitLab();\n" + code ;
	  
      myInterpreter = new Interpreter(code, initApi);

      alert('Ready to execute this code:\n\n' + code);
      document.getElementById('stepButton').disabled = '';
      highlightPause = false;
      workspace.highlightBlock(null);
	  count_chart = 0;
    }
	
function changeInterval() {
	interval = false;
	clearInterval(flags);
}

function stepCode() {
	
	if(!interval){
      try {
        var ok = myInterpreter.step();
      } finally {
        if (!ok) {
          // Program complete, no more code to execute.
          workspace.highlightBlock(null);
		  clearInterval(inter);
          return;
        }
      }
      if (highlightPause) {
        // A block has been highlighted.  Pause execution here.
        highlightPause = false;
      } else {
        // Keep executing until a highlight statement is reached.
        stepCode();
      }
	}
}

var interval = false;
var inter;
function playCode() {
	parseCode();
	inter=setInterval(stepCode, time_step);
}


 
  
  function saveWorkspace() {
		var xmlDom = Blockly.Xml.workspaceToDom(workspace);
		var xmlText = Blockly.Xml.domToPrettyText(xmlDom);
		var file = (document.getElementById("myText").value+".xml");
		localStorage.setItem(file, xmlText);
	}

	function loadWorkspace() {
		var file = (document.getElementById("myText").value+".xml");
		var xmlText = localStorage.getItem(file);
		if (xmlText) {
			workspace.clear();
			xmlDom = Blockly.Xml.textToDom(xmlText);
			Blockly.Xml.domToWorkspace(xmlDom,workspace);
		}
	}
		
	