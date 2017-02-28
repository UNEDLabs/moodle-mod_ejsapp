///////////////


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
	  
	  
		workspace = Blockly.inject('blocklyDiv',{media: 'blockly/media/',toolbox: toolbox});
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
		  
   // Add an API function for the initialize block.
      var wrapper = function() {
        return interpreter.createPrimitive(initialize());
      };
      interpreter.setProperty(scope, 'initialize',
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
        else
        	p2 = parseFloat(p2);
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
		  
		//reateChart("+name+","+time+",[{"+c0name+":"+value_name+"}"+yaxis+"]);\n";
  // Add an API function for the createChart() block.
  var wrapper = function(text,number,columns) {
        text = text ? text.toString() : '';
        return interpreter.createPrimitive(createChart(text,number,columns));
      };
      interpreter.setProperty(scope, 'createChart',
          interpreter.createNativeFunction(wrapper));
		  
   // Add an API function for the setTimeStep block.
      var wrapper = function(number) {
        return interpreter.createPrimitive(setTimeStep(number));
      };
      interpreter.setProperty(scope, 'setTimeStep',
          interpreter.createNativeFunction(wrapper));

    }

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


/////////////// INTERPRETER

 var highlightPause = false;

function highlightBlock(id) {
      workspace.highlightBlock(id);
      highlightPause = true;
}


  var functions;
  
function parseCode() {
      // Generate JavaScript code and parse it.
      Blockly.JavaScript.STATEMENT_PREFIX = 'highlightBlock(%1);\n';
      Blockly.JavaScript.addReservedWords('highlightBlock');
      var code = Blockly.JavaScript.workspaceToCode(workspace);
	  
		var code2 = code;
		/// BUSCAR FUNCIONES /////
		functions ="function pause(){_model.pause();} function reset(){_model.reset();} function initialize(){_model.initialize();} function play(){_model.play();}\n";
		var continueSearch=true;
		while(continueSearch){
			var pos = code2.search("function ");
			if(pos==-1) continueSearch = false;
			else{
				var pos2 = code2.search("}\n");
				functions = functions+"\n"+code2.slice(pos,pos2+1); 
				code2=code2.slice(pos2+1,code2.length);
			}
		}
		//////////////////////////
	  console.log("Code: "+code);
      //Blockly.JavaScript.INFINITE_LOOP_TRAP = null;
      myInterpreter = new Interpreter(code, initApi);
      highlightPause = false;
      workspace.highlightBlock(null);
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