


function playStop(number) {
	_model.addEvent(number + "-t", "_model.pause()", "");
	_model._play();
}

function evaluate(code) {
	eval(code);
}

function makeEvalContext(declarations) {
	eval(declarations);
	return function (str) {
		eval(str);
	}
}

function initialize() {
	_model.initialize();
}

function reInitLab() {
	_model.removeEvents();
	for (var i in conditionFixed)
		conditionFixed[i] = false;
}

function selectEvent(number) {
	for (k in events) {
		if (events[k].num == number) {
			var cond = events[k].cond;
			var act = events[k].act;
			//_model.addEvent(cond,act);
			_model.getOdes()[0]._addEvent(cond, act, EJSS_ODE_SOLVERS.EVENT_TYPE.CROSSING_EVENT, EJSS_ODE_SOLVERS.EVENT_METHOD.BISECTION, 100, 1.0e-5, true);
			return;
		}
	}

}

function getValueModel(p1) {
	var obj = _model._userSerialize();
	for (var k in obj) {
		if (k.localeCompare(p1) == 0) {
			return obj[k];
		}
	}
	return '';
}

function get(pl) {
	return getValueModel(pl);
}

function set(p1, p2) {
	setValueModel(p1, p2);
}

function setValueModel(p1, p2) {
	aux = {};
	aux[p1] = p2;
	_model._userUnserialize(aux);
}

function addFixedRelation(number) {
	conditionFixed.push(true);
	var text2 = fixedStatements[number];
	var text = "if(conditionFixed[" + (conditionFixed.length - 1) + "])" + "{" + text2 + "}";
	_model.addFixedRel(text);
}

function play() {
	_model.play();
}

function pause() {
	_model.pause();
}

function addEvent(number) {
	selectEvent(number);
}

function reset() {
	_model.reset();
}

var flags;
function setTimeStep(num) {
	interval = true;
	flags = setInterval(changeInterval, num);
}


var STRIP_COMMENTS = /((\/\/.*$)|(\/\*[\s\S]*?\*\/))/mg;
var ARGUMENT_NAMES = /([^\s,]+)/g;
function getParamNames(func) {
  var fnStr = func.toString().replace(STRIP_COMMENTS, '');
  var result = fnStr.slice(fnStr.indexOf('(')+1, fnStr.indexOf(')')).match(ARGUMENT_NAMES);
  result = result.toString().replace(/,/g , ' , ');
  if(result === null)
     result = [];
  else
	 result = result.toString().replace(/,/g , ' , ');
  return result;
}


function replaceFunction(dropdown_original, text_params, value_name) {
	var statements_code = statem[0].toString();
	statem.splice(0, 1);
	var text_vars = "";
	//var text_vars2 = "";
	var array = text_params.split(',');
	if(!remote){
		if(text_params!==""){
			for (var i in array) {
				text_vars = text_vars + array[i]+"=getValueModel('"+array[i]+"');\n";
				//text_vars2 = text_vars2 + "setValueModel('"+array[i]+"',"+array[i]+");\n";
			}
		}
	}
	var fill = new Function(text_params, text_vars + statements_code +' return ' + value_name + ';');
	setValueModel(dropdown_original, fill);
}

function getVarExp(name){
	return window[name];
}

function setVarExp(name,value){
	if(name.toString().includes(","))
	{
		var array = name.toString().split(',');
		for (var i in array) {
			window[array[i]]=value;
		}
	}
	else{
		window[name]=value;
	}
}

//////// INTERPRETER ///////////////////

function initApi(interpreter, scope) {
	// Add an API function for the record() block.
	var wrapper = function (text, number) {
		text = text ? text.toString() : '';
		return interpreter.createPrimitive(recordvar(text, number));
	};
	interpreter.setProperty(scope, 'recordvar',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for the rec() block.
	var wrapper = function (bool) {
		if ((bool.toString().localeCompare("true") == 0))
			bool = true;
		else if ((bool.toString().localeCompare("false") == 0))
			bool = false;
		return interpreter.createPrimitive(rec(bool));
	};
	interpreter.setProperty(scope, 'rec',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for the initialize block.
	var wrapper = function () {
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
	var wrapper = function (text) {
		text = text ? text.toString() : '';
		return interpreter.createPrimitive(alert(text));
	};
	interpreter.setProperty(scope, 'alert',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for the prompt() block.
	var wrapper = function (text) {
		text = text ? text.toString() : '';
		return interpreter.createPrimitive(prompt(text));
	};
	interpreter.setProperty(scope, 'prompt',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for the addEvent() block.
	var wrapper = function (number) {
		return interpreter.createPrimitive(selectEvent(number));
	};
	interpreter.setProperty(scope, 'addEvent',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for the getValueModel() block.
	var wrapper = function (text) {
		text = text ? text.toString() : '';
		return interpreter.createPrimitive(getValueModel(text));
	};
	interpreter.setProperty(scope, 'getValueModel',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for the addFixedRelation() block.
	var wrapper = function (number) {
		return interpreter.createPrimitive(addFixedRelation(number));
	};
	interpreter.setProperty(scope, 'addFixedRelation',
		interpreter.createNativeFunction(wrapper));
	
	// Add an API function for the define() block.
	var wrapper = function (name) {
		return interpreter.createPrimitive(setVarExp(name,0));
	};
	interpreter.setProperty(scope, 'define',
		interpreter.createNativeFunction(wrapper));
	
	// Add an API function for the setVarExp() block.
	var wrapper = function (name,value) {
		return interpreter.createPrimitive(setVarExp(name,value));
	};
	interpreter.setProperty(scope, 'setVarExp',
		interpreter.createNativeFunction(wrapper));
		
	// Add an API function for the getVarExp() block.
	var wrapper = function (name) {
		return interpreter.createPrimitive(getVarExp(name));
	};
	interpreter.setProperty(scope, 'getVarExp',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for the play block.
	var wrapper = function () {
		return interpreter.createPrimitive(play());
	};
	interpreter.setProperty(scope, 'play',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for the playStop block.
	var wrapper = function (number) {
		return interpreter.createPrimitive(playStop(number));
	};
	interpreter.setProperty(scope, 'playStop',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for the pause block.
	var wrapper = function () {
		return interpreter.createPrimitive(pause());
	};
	interpreter.setProperty(scope, 'pause',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for the reset block.
	var wrapper = function () {
		return interpreter.createPrimitive(_model.reset());
	};
	interpreter.setProperty(scope, 'reset',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for the setValueModel() block.
	var wrapper = function (text, p2) {
		text = text ? text.toString() : '';
		return interpreter.createPrimitive(setValueModel(text, p2));
	};
	interpreter.setProperty(scope, 'setValueModel',
		interpreter.createNativeFunction(wrapper));
		
	// Add an API function for the setValueModel() block.
	var wrapper = function (text, p2,type) {
		text = text ? text.toString() : '';
		
		switch(type.toString()){
				case "boolean":
					if ((p2.toString().localeCompare("true") == 0))
						p2 = true;
					else if ((p2.toString().localeCompare("false") == 0))
						p2 = false;
					break;
				case "number":
					p2 = parseFloat(p2);
					break;
				case "string":
					p2 = p2.toString();
					
			}
		
		return interpreter.createPrimitive(setValueModel(text, p2));
	};
	interpreter.setProperty(scope, 'setValueModel',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for highlighting blocks.
	var wrapper = function (id) {
		id = id ? id.toString() : '';
		return interpreter.createPrimitive(highlightBlock(id));
	};
	interpreter.setProperty(scope, 'highlightBlock',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for eval blocks.
	var wrapper = function (text) {
		text = text ? text.toString() : '';
		return interpreter.createPrimitive(evaluate(text));
	};
	interpreter.setProperty(scope, 'evaluate',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for reInitLab blocks.
	var wrapper = function () {
		return interpreter.createPrimitive(reInitLab());
	};
	interpreter.setProperty(scope, 'reInitLab',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for the setTimeStep block.
	var wrapper = function (number) {
		return interpreter.createPrimitive(setTimeStep(number));
	};
	interpreter.setProperty(scope, 'setTimeStep',
		interpreter.createNativeFunction(wrapper));

	// Add an API function for reInitLab blocks.
	var wrapper = function (a, b, c) {
		a = a ? a.toString() : '';
		b = b ? b.toString() : '';
		c = c ? c.toString() : '';
		return interpreter.createPrimitive(replaceFunction(a, b, c));
	};
	interpreter.setProperty(scope, 'replaceFunction',
		interpreter.createNativeFunction(wrapper));

	//reateChart("+name+","+time+",[{"+c0name+":"+value_name+"}"+yaxis+"]);\n";
	// Add an API function for the createChart() block.
	var wrapper = function (number) {
		return interpreter.createPrimitive(createChart(number));
	};
	interpreter.setProperty(scope, 'createChart',
		interpreter.createNativeFunction(wrapper));

}

function highlightBlock(id) {
	workspace.highlightBlock(id);
	highlightPause = true;
}

var functions;
window.LoopTrap = 10;
function parseCode() {
	declared_variables_remote = [];
	// Generate JavaScript code and parse it.
	if(!remoteLab){
		Blockly.JavaScript.STATEMENT_PREFIX = 'highlightBlock(%1);\n';
		Blockly.JavaScript.addReservedWords('highlightBlock');
		Blockly.JavaScript.addReservedWords('LoopTrap');
	}
	else
		function_code_remote ="";
	var code = Blockly.JavaScript.workspaceToCode(workspace);
	//Blockly.JavaScript.INFINITE_LOOP_TRAP = null;
	code = "reInitLab();\n" + code;
	var code2 = code;
	functions = "function pause(){_model.pause();} function reset(){_model.reset();} function initialize(){_model.initialize();} function play(){_model.play();}\n";
	var continueSearch = true;
	while (continueSearch) {
		var pos = code2.search("function ");
		if (pos == -1)
			continueSearch = false;
		else {
			var pos2 = code2.search("}\n");
			functions = functions + "\n" + code2.slice(pos, pos2 + 1);
			code2 = code2.slice(pos2 + 1, code2.length);
		}
	}
	//////////////////////////
	console.log("Code: " + code);
	//Blockly.JavaScript.INFINITE_LOOP_TRAP = null;
	myInterpreter = new Interpreter(code, initApi);
	highlightPause = false;
	workspace.highlightBlock(null);
	count_chart = 0;
}

function changeInterval() {
	interval = false;
	clearInterval(flags);
}

function stepCode() {

	if (!interval) {
		try {
			var ok = myInterpreter.step();
		}
		finally {
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
