function prepareControllerCode(code){
	statements_code = cleanFromComments(code);
	statements_code = statements_code.replace(/(\r\n|\n|\r)/gm, "");
	var res = getInfoFromFunctionName(functionToReplace);
	ret="";
	if(res[1]!==""){
		ret = "return "+res[1]+";";
	}
	params = res[2];
	var textoparametros="";
	for(var k = 0;k<params.length;k++){
		textoparametros+='window["'+params[k]+'"] = '+params[k]+';';
	}

	control = new Function(res[0], textoparametros+'evaluarConContexto("'+statements_code+ret+'");' );
	/*u=4.0*(beta-betaRef) - 35.0*alpha + 1.5*dbeta - 3.0*dalpha;
	(4 * ((beta) - (betaRef)) - (35 * (alpha) + (1.5 * (dbeta) - 3 * (dalpha))))
	control = new Function(res[0], textoparametros+'evaluarConContexto("u=(4 * ((beta) - (betaRef)) - 35 * (alpha) + (1.5 * (dbeta) - 3 * (dalpha)))");' );*/

	/*statem.push(statements_code.toString());
	var res = getInfoFromFunctionName(functionToReplace);
	var ret="";
	if(res[1]!==""){
		ret = "return "+res[1]+";";
	}
	fun = new Function(res[0], statements_code +ret );
	console.log(fun);*/
	return "replaceFunction();\n"
}

function playCodeDet(value0,value1,value2,value3) {
	var blocklyExp="";blockyEvent="";blocklyChart="";blocklyController="";
	if(value0!=="Select experiment" && value0!==""){
		workspace.clear();
		Blockly.Xml.domToWorkspace(Blockly.Xml.textToDom(getCodeFromName(experimentsList,value0)[0]), workspace);
		var code = Blockly.JavaScript.workspaceToCode(workspace);
		if(code!==null)
			blocklyExp =code;
	}
	if(value1!=="Select chart"  && value1!==""){
		workspaceCharts.clear();
		Blockly.Xml.domToWorkspace(Blockly.Xml.textToDom(getCodeFromName(chartsList,value1)[0]), workspaceCharts);
		blocklyChart=Blockly.JavaScript.workspaceToCode(workspaceCharts);
	}
	if(value2!=="Select event"  && value2!==""){
		workspaceEvents.clear();
		Blockly.Xml.domToWorkspace(Blockly.Xml.textToDom(getCodeFromName(eventsList,value2)[0]), workspaceEvents);
		blockyEvent=Blockly.JavaScript.workspaceToCode(workspaceEvents);
	}
	if(value3!=="Select controller"  && value3!==""){
		if(controllerBlockly){
			var semicode="";
			var code = getCodeFromName(controllersList,value3)[0];
			if(controllerUseBlockly)
			{
				workspaceControllers.clear();
				Blockly.Xml.domToWorkspace(Blockly.Xml.textToDom(getCodeFromName(controllersList,value3)[0]), workspaceControllers);
				semicode=Blockly.JavaScript.workspaceToCode(workspaceControllers);

			}
			else{
				semicode=code;
			}
			if(remoteController){
				_model.sendToRemoteController(semicode);
				blocklyController="";
			} else {
				blocklyController = prepareControllerCode(semicode);
			}
		}
	}

	if((code===null) || (blocklyExp==="") )
		return ["","","",""];
	/* CONTROLLER NOT YET Blockly.Xml.domToWorkspace(getCodeFromName(experimentsList,$('select')[3].value), workspaceEvents); */
	return [blocklyExp,blocklyChart,blockyEvent,blocklyController];
}

function playCodeFromOutside(){
	playCode(chartsBlockly, eventsBlockly, controllerBlockly);
}

function playCode(chartsBlockly, eventsBlockly, controllerBlockly) {
	var a="Select chart";
	var b="Select event";
	var c="Select controller";
	var replaytext = Blockly.Msg.Log1 +' <span style="color:green">'+experimentSelected+'</span>';
	if(chartsBlockly) {
		if(chartSelected!==""){
			a = chartSelected;
			replaytext += Blockly.Msg.Log2+'<span style="color:blue">'+ a +'</span>';
		}
	}
	if(eventsBlockly) {
		if(eventSelected!==""){
			b = eventSelected;
			replaytext += Blockly.Msg.Log3+'<span style="color:red">'+ b +'</span>';
		}
	}
	if(controllerBlockly) {
		if(controllerSelected!==""){
			c = controllerSelected;
			replaytext += Blockly.Msg.Log4+'<span style="color:peru">'+ c +'</span>';
		}
	}
	var result= playCodeDet(experimentSelected,a,b,c);
	if(result[0]!==""){
		document.getElementById('executionLogGen').style.display="block";
		document.getElementById('executionLog').insertAdjacentHTML('beforeend', '<div class="textsmall">' +
			'<i onclick=" var result=replayCode(\''+$('select')[0].value+'\',\''+a+'\',\''+b+'\',\''+c+'\');" ' +
			'class="fa fa-repeat"></i>'+replaytext+'</div>');
		parseCode(result[0],result[1],result[2],result[3]);
		inter = setInterval(stepCode, time_step);
	}
	else{
		printError(Blockly.Msg.ExpError);
	}
}

function replayCode(value0,value1,value2,value3){
	var result=playCodeDet(value0,value1,value2,value3);
	parseCode(result[0],result[1],result[2],result[3]);
	inter = setInterval(stepCode, time_step);
}

function parseCode(blocklyExp,blocklyChart,blockyEvent) {
	Blockly.JavaScript.addReservedWords('LoopTrap');
	Blockly.JavaScript.STATEMENT_PREFIX = '';
	Blockly.JavaScript.addReservedWords('highlightBlock');
	Blockly.JavaScript.STATEMENT_PREFIX = 'highlightBlock(%1);\n';
	var code = "reInitLab();\n"+blocklyChart+blocklyController+"var set = false;\n"+blockyEvent+blocklyExp;
	functions = "function pause(){_model.pause();} function reset(){_model.reset();} function initialize(){_model.initialize();} function play(){_model.play();}\n";
	/*var code2 = code;

	var continueSearch = true;
	while (continueSearch) {
		console.log(code2);
		var pos = code2.search("function ");
		if (pos === -1)
			continueSearch = false;
		else {
			var pos2 = code2.search("}\n");
			functions = functions + "\n" + code2.slice(pos, pos2 + 1);
			code2 = code2.slice(pos2 + 1, code2.length);
		}
	}*/
	myInterpreter = new Interpreter(code, initApi);
	highlightPause = false;
	workspace.highlightBlock(null);
}

function stepCode() {
	if (!interval) {
		try {
			var set = myInterpreter.getValueFromScope('set');

			if((set===undefined)||(set!==true))
				revisarInicio();
			var ok = myInterpreter.step();
		}
		catch(error) {
			printError(error);
		}
		finally {
			if (!ok) {
				/* Program complete, no more code to execute. */
				workspace.highlightBlock(null);
				clearInterval(inter);
				return;
			}
		}
		if (highlightPause) {
			/* A block has been highlighted.  Pause execution here. */
			highlightPause = false;
		} else {
			/* Keep executing until a highlight statement is reached. */
			stepCode();
		}
	}
}

function initApi(interpreter, scope) {
	/* Add an API function for highlighting blocks. */
	var wrapper = function() {
		return interpreter.createPrimitive(revisarInicio());
	};
	interpreter.setProperty(scope, 'revIni', interpreter.createNativeFunction(wrapper));

	/* Add an API function for highlighting blocks. */
	var wrapper = function() {
		return interpreter.createPrimitive(revisarFin());
	};
	interpreter.setProperty(scope, 'revFin', interpreter.createNativeFunction(wrapper));

	/* Add an API function for alert blocks. */
	var wrapper = function(id) {
		id = id ? id.toString() : '';
		return interpreter.createPrimitive(alert(id));
	};
	interpreter.setProperty(scope, 'alert', interpreter.createNativeFunction(wrapper));

	/* Add an API function for highlighting blocks. */
	var wrapper = function(id) {
		id = id ? id.toString() : '';
		return interpreter.createPrimitive(highlightBlock(id));
	};
	interpreter.setProperty(scope, 'highlightBlock', interpreter.createNativeFunction(wrapper));


	/* Add an API function for the play block. */
	var wrapper = function() {
		return interpreter.createPrimitive(_model.play());
	};
	interpreter.setProperty(scope, 'play', interpreter.createNativeFunction(wrapper));

	/* Add an API function for the pause block. */
	var wrapper = function() {
		return interpreter.createPrimitive(_model.pause());
	};
	interpreter.setProperty(scope, 'pause',interpreter.createNativeFunction(wrapper));

	/* Add an API function for the reset block. */
	var wrapper = function() {
		return interpreter.createPrimitive(reset());
	};
	interpreter.setProperty(scope, 'reset',	interpreter.createNativeFunction(wrapper));

	/* Add an API function for the initialize block. */
	var wrapper = function() {
		return interpreter.createPrimitive(_model.initialize());
	};
	interpreter.setProperty(scope, 'initialize', interpreter.createNativeFunction(wrapper));

	/* Add an API function for the setTimeStep block. */
	var wrapper = function(number) {
		return interpreter.createPrimitive(setTimeStep(number));
	};
	interpreter.setProperty(scope, 'setTimeStep', interpreter.createNativeFunction(wrapper));

	/* Add an API function for the addEvent() block. */
	var wrapper = function(cond,statement) {
		cond = cond ? cond.toString() : '';
		statement = statement ? statement.toString() : '';
		return interpreter.createPrimitive(addEvent(cond,statement));
	};
	interpreter.setProperty(scope, 'addEvent', interpreter.createNativeFunction(wrapper));

	/* Add an API function for the rec() block. */
	var wrapper = function(bool) {
		if ((bool.toString().localeCompare("true") == 0)) bool = true;
		else if ((bool.toString().localeCompare("false") == 0))	bool = false;
		return interpreter.createPrimitive(rec(bool));
	};
	interpreter.setProperty(scope, 'rec', interpreter.createNativeFunction(wrapper));

	/* Add an API function for reInitLab blocks. */
	var wrapper = function() {
		return interpreter.createPrimitive(reInitLab());
	};
	interpreter.setProperty(scope, 'reInitLab',	interpreter.createNativeFunction(wrapper));

	/* Add an API function for the addFixedRelation() block. */
	var wrapper = function(number,statement) {
		statement = statement ? statement.toString() : '';
		return interpreter.createPrimitive(addFixedRelation(number,statement));
	};
	interpreter.setProperty(scope, 'addFixedRelation', interpreter.createNativeFunction(wrapper));

	/* Add an API function for the createChart() block. */
	var wrapper = function (number) {
		return interpreter.createPrimitive(createChart(number));
	};
	interpreter.setProperty(scope, 'createChart', interpreter.createNativeFunction(wrapper));

	/* Add an API function for record_var blocks. */
	var wrapper = function(id,id2) {
		id = id ? id.toString() : '';
		id2 = id2 ? id2.toString() : '';
		return interpreter.createPrimitive(record_var(id,id2));
	};
	interpreter.setProperty(scope, 'record_var', interpreter.createNativeFunction(wrapper));

	var wrapper = function() {
		return interpreter.createPrimitive(replaceFunction());
	};
	interpreter.setProperty(scope, 'replaceFunction', interpreter.createNativeFunction(wrapper));

} /* End of initApi */

function revisarFin(){
	/* Return variables */
	var aux = {};
	var obj = _model._userSerialize();
	for (var k in obj) {
		var value = myInterpreter.getValueFromScope(k);
		if (value !== undefined) {
			if(k!==functionToReplace){
				aux = {};
				aux[k] = value;
				_model._userUnserialize(aux);
			}
		}
	}
	_model.resetSolvers();
}

function revisarInicio(){
	var obj = _model._userSerialize();
	var values = [];
	for (var k in obj) {
		myInterpreter.setValueToScope(k,obj[k]);
	}
}

function revisarFin2(){
	/* Return variables */
	var aux = {};
	var obj = _model._userSerialize();
	for (var k in obj) {
		var value = intrp.getValueFromScope(k);
		if (value !== undefined) {
			aux = {};
			aux[k] = value;
			_model._userUnserialize(aux);
		}
	}
	_model.resetSolvers();
}

function revisarInicio2(){
	var obj = _model._userSerialize();
	for (var k in obj) {
		intrp.setValueToScope(k,obj[k]);
	}
}

function highlightBlock(id) {
	workspace.highlightBlock(id);
	highlightPause = true;
}



function setTimeStep(num) {
	interval = true;
	flags = setInterval(changeInterval, num);
}

function changeInterval() {
	interval = false;
	clearInterval(flags);
}

function reset(){
	_model.reset();
}

function rec(bool) {
	document.getElementById('clean_chart').disabled = true;
	record = bool;
	if (!bool) {
		for (var i in intervals) {
			window.clearInterval(intervals[i]);
		}
		intervals = [];
		document.getElementById('clean_chart').disabled = false;
	}
}

function addEvent(cond,act) {
	_model.getOdes()[0]._addEvent(cond, act, EJSS_ODE_SOLVERS.EVENT_TYPE.CROSSING_EVENT, EJSS_ODE_SOLVERS.EVENT_METHOD.BISECTION, 10000, 0.00001, true);
	_model.reset();
}

function reInitLab() {
	_model.removeEvents();
	for (var i in conditionFixed)
		conditionFixed[i] = false;
}

function calculaExpresion(expresion){
	revisarInicio();
	intrp= new Interpreter('');
	intrp.stateStack[0].scope = myInterpreter.global;
	intrp.appendCode(expresion);
	intrp.run();
	return intrp.value;
}

intrp2= new Interpreter('');
function calculaExpresion2(expresion){
	revisarInicio();
	intrp2.stateStack[0].scope = myInterpreter.global;
	intrp2.appendCode(statements_code+ret);
	intrp2.run();
	revisarFin();
}

intrp= new Interpreter('');
function addFixedRelation(number, statement) {
	conditionFixed.push(true);
	_model.addToEvolution(function() {
		if(conditionFixed[number]){
			calculaExpresion(statement);
			revisarFin();
		}
	});
}

var reVarInter = function(id,id2) {
		if (record) {
			var x = calculaExpresion(id);
			var y = calculaExpresion(id2);
			var index = -1;
			for(var r in recordedVariables.names){
				if((recordedVariables.names[r][0]===id)&&(recordedVariables.names[r][1]===id2)){
					index = r;
					var lastValue  = x-1;
					if(recordedVariables.datas.length>0)
						lastValue = recordedVariables.datas[r][recordedVariables.datas[r].length-1][0];
					else
						recordedVariables.datas[r]=[];
					if(x!==lastValue){
						recordedVariables.datas[r].push([x,y]);
					}
					return;
				}
			}
		}
};

function record_var(id,id2){
	recordedVariables.names.push([id,id2]);
	var interv = setInterval(reVarInter, 100,id,id2);
	intervals.push(interv);
}


function replaceFunction() {
	/*var statements_code = statem[0].toString();
	console.log("statements_code "+statements_code);
	statem.splice(0, 1);
	var fill = new Function(text_params, statements_code);
	if(value_name!=='')
		fill = new Function(text_params, statements_code + ' return ' + value_name + ';');
	setValueModel(dropdown_original, fill);
	console.log(getValueModel(dropdown_original));*/
	setValueModel(functionToReplace,control);
}

function setValueModel(p1, p2) {
	aux = {};
	aux[p1] = p2;
	_model._userUnserialize(aux);
}

function cleanFromComments(textblock) {
	var lines = textblock.split('\n');
	/* Remove one line, starting at the first position */
	for (var i = lines.length - 1; i >= 0; i--) {
		if (lines[i].indexOf('//') >= 0) {
			lines.splice(i, 1);
		}
	}
	/* Join the array back into a single string */
	return lines.join('\n');
}

function evaluarConContexto(code) {
	var resultado = -1000;
		/* VARIABLES FROM BLOCKLY */
		var context = {};
		for (var i = 0; i < blocklyVariablesList.length; i++) {
			var val = myInterpreter.getValue(blocklyVariablesList[i]);
			if(val!==undefined)
				window[blocklyVariablesList[i]] = myInterpreter.getValueFromScope(blocklyVariablesList[i]);
		}
		/* VARIABLES FROM EJSS */
		var obj = _model._userSerialize();
		var values = [];
		for (var k in obj) {
			if(params.indexOf(k)===-1){
				if(typeof obj[k] === 'string') window[k] = obj[k].toString();
				else window[k] = obj[k];
				values[k] = obj[k];
			}
		}

		/* CODIGO BLOCKLY A EJECUTAR */
		resultado = eval(code);

		/* DEVOLVEMOS VARIABLES */
		var aux = {};
		for (var k in obj) {
			if(params.indexOf(k)===-1){
				var value = window[k];
				if (values[k] !== value) {
					aux[k] = value;
				}
			}
		}
		_model._readParameters(aux);
		_model.resetSolvers();

	return resultado;
}