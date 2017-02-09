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

    }
	

	
	
 
	
	