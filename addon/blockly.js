


	document.addEventListener('DOMContentLoaded', function(){
		/* When the user clicks on <span> (x), close the modal */
		document.getElementsByClassName("close")[0].onclick = function() {
			if(jsOpenType===0)
				jsCodesGeneral[jsOpenedGeneral].code = editorJS.getValue();
			else
				jsCodesEvents[jsOpenedEvents].code = editorJS.getValue();
			document.getElementById('myModal').style.display = "none";
		};

		/* When the user clicks anywhere outside of the modal, close it */
		window.onclick = function(event) {
			if (event.target == document.getElementById('myModal')) {
				if(jsOpenType===0)
					jsCodesGeneral[jsOpenedGeneral].code = editorJS.getValue();
				else
					jsCodesEvents[jsOpenedEvents].code = editorJS.getValue();
				document.getElementById('myModal').style.display = "none";
			}
		};

		require(['mod_ejsapp/blockly_conf'], function (BlocklyConf) {
			var start = setInterval(function() {
				if (typeof _model !== "undefined") {
					if (!BlocklyConf.returnChartsBlockly()) {
						document.getElementById('chartsDropdown').style.display = "none";
					}
					if (!BlocklyConf.returnEventsBlockly()) {
						document.getElementById('eventsDropdown').style.display = "none";
					}
					if (!BlocklyConf.returnControllerBlockly()) {
						document.getElementById('functionsDropdown').style.display = "none";
					}
					initAux();
					loadVariables();
					prepareToolBox();
					prepareBlocks(newImplement, keys_boolean, keys_boolean_output, keys_boolean_input, keys_number, keys_number_output, keys_number_input, visualJSGeneral, visualJSEvents, events_vars, function_from_ejss, function_from_ejss_with_return);
					prepareJavaScript(jsCodesGeneral, jsCodesEvents, conditionFixed, chartId, chartInfo, function_from_ejss, function_from_ejss_with_return);
					preparePage();
					initJSFrame("_javaScriptFrame");

					require(['mod_ejsapp/vendor/blockly/blockly_compressed'], function(Blockly) {

						workspace = Blockly.inject('blocklyDivExperiments', {
							grid:
								{
									spacing: 25,
									length: 3,
									colour: '#ccc',
									snap: true
								},
							media: './vendor/blockly/media/',
							toolbox: toolbox,
							collapse: true,
							zoom: {controls: true}
						});
						workspace.registerButtonCallback("createVariablePressed", createVariable, "");
						workspace.registerToolboxCategoryCallback('generalVars', generalVars);
						workspace.registerToolboxCategoryCallback('ejssFunctions', ejssFunctions);
						workspace.registerToolboxCategoryCallback('jss', jss);
						/*workspace.registerButtonCallback('jsButtonPressed', jsButton);
                        workspace.registerButtonCallback('loadjsButtonPressed', loadjsButton);*/
						workspace.addChangeListener(myUpdateFunction);
					});
					clearInterval(start);
				}
			}, 200);
		});
	}, false);

	//function_from_ejss = [{'name':'funcion_prueba','params':['x','y','z'],'code':'console.log("Aleluya "+x+" "+y+" "+z);'}]; // PRUEBA
	//function_from_ejss_with_return = [{'name':'funcion_prueba2','params':['x','y'],'code':'console.log("Aleluya "+x+" "+y+" "+z);'}]; // PRUEBA




	require(['mod_ejsapp/blockly_conf'], function (BlocklyConf) {
		initAux = function() {
			toolbox = "";
			toolboxEvents = "";
			toolboxCharts = "";
			toolboxControllers = "";
			// SWAP ELEMENTS
			dragElems = ['dragBlockly_navbar', 'dragLab', 'dragBlockly'];
			dragElems2 = ['UpperEJsS', "ChartBox"];
			enableDragItems = false;
			dragableElements(dragElems, dragElems2, 2);
			//////
			evaluatefuns = [];
			function_from_ejss = [];
			function_from_ejss_with_return = [];
			functionUseBlockly = false;
			if (BlocklyConf.returnControllerFunctionLanguage() === 'blockly') {
				functionUseBlockly = true;
				workspaceFunctions = null;
			}
			functionsVariableList = [];
			blocklyVariablesList = [];
			modelVariablesList = [];
			workspace = null;
			workspaceEvents = null;
			workspaceCharts = null;
			keys_boolean = [];
			keys_number = [];
			keys_others = [];
			events_vars = [];
			keys_number_input = [];
			keys_number_output = [];
			keys_boolean_input = [];
			keys_boolean_output = [];
			keys_others_input = [];
			keys_others_output = [];
			keys_input = [];
			keys_output = [];
			flags = null;
			interval = null;
			jsCodesGeneral = [];
			jsCodesEvents = [];
			jsOpenType = -1;
			visualJSGeneral = [];
			visualJSEvents = [];
			jsOpenedGeneral = -1;
			jsOpenedEvents = -1;
			javaScriptsNamesListGeneral = [];
			javaScriptsNamesListEvents = [];
			functions = "";
			conditionFixed = [];
			intrp = null;
			record = false;
			recordedVariables = {};
			recordedVariables.names = [];
			recordedVariables.datas = [];
			functionEditor = null;
			experimentsList = [];
			chartsList = [];
			eventsList = [];
			functionsList = [];
			errorFunctionsList = [];
			functionOpen = [];
			functionSelected = [];
			for (var i = 0; i < BlocklyConf.returnFunctionToReplace().length; i++) {
				functionsList.push([]);
				errorFunctionsList.push([]);
				functionOpen[i] = -1;
				functionSelected[i] = "";
			}
			codeOfData = [];
			codeOfEvents = [];
			codeOfControllers = [];
			experimentOpen = -1;
			chartOpen = -1;
			eventOpen = -1;
			errorInterval = null;
			chartId = 0;
			experimentSelected = "";
			eventSelected = "";
			chartSelected = "";
			codeForRemoteFunctions = [];
			functionsFromEvents = "";
			divForFunctions = [];
		}
	});


	var STRIP_COMMENTS = /(\/\/.*$)|(\/\*[\s\S]*?\*\/)|(\s*=[^,\)]*(('(?:\\'|[^'\r\n])*')|("(?:\\"|[^"\r\n])*"))|(\s*=[^,\)]*))/mg;
	var ARGUMENT_NAMES = /([^\s,]+)/g;
	function getParamNames(func) {
		var fnStr = func.toString().replace(STRIP_COMMENTS, '');
		var result = fnStr.slice(fnStr.indexOf('(')+1, fnStr.indexOf(')')).match(ARGUMENT_NAMES);
		if(result === null)
			result = [];
		return result;
	}

	require(['mod_ejsapp/blockly_conf'], function (BlocklyConf) {
		loadFunctionsNamesFromModel = function (_model) {
			var obj = _model._userSerialize();
			var result = [];
			if (obj !== null) {
				if (typeof _model._userSerializePublic !== "undefined" && _model._userSerializePublic !== null) {
					for (var k in obj) {
						switch (typeof obj[k]) {
							case 'function':
								if (BlocklyConf.returnFunctionToReplace() !== k) {
									result.push(k);
								}
								break;
						}
					}

				} else if (typeof _model._inputAndPublicParameters !== "undefined") {
					var inputAux = _model._inputAndPublicParameters;
					for (var k in inputAux) {
						if (inputAux[k].includes(':')) {
							inputAux[k] = inputAux[k].slice(0, inputAux[k].indexOf(' '));
							switch (typeof obj[inputAux[k]]) {
								case 'function':
									if (BlocklyConf.returnFunctionToReplace() !== inputAux[k]) {
										result.push(inputAux[k]);
									}
									break;
							}
						}
					}
				}
			}
			return result;
		}
	});

	require(['mod_ejsapp/blockly_conf'], function (BlocklyConf) {
		loadVariables = function() {
			var _vars = "";
			if (typeof _model.getOdes() !== "undefined")
				if (typeof _model.getOdes()[0] !== "undefined")
					_vars = _model.getOdes()[0]._getOdeVars();

			var obj = _model._userSerialize();
			var dupla;
			if (typeof _model._userSerializePublic !== "undefined" && _model._userSerializePublic !== null) {
				newImplement = false;
				keys = [];
				for (var k in obj) {
					if (!variableExists(k)) modelVariablesList.push(k);
					dupla = [];
					dupla.push(k);
					dupla.push(k);
					switch (typeof obj[k]) {
						case 'number':
							keys_number.push(dupla);
							break;
						case 'boolean':
							keys_boolean.push(dupla);
							break;

						case 'function':
							if (BlocklyConf.returnFunctionToReplace() !== k) {
								if ((obj[k].toString().includes('return ')))
									function_from_ejss_with_return.push({'name': k, 'params': getParamNames(obj[k])});
								else
									function_from_ejss.push({'name': k, 'params': getParamNames(obj[k])});
							}
							break;
						default:
							keys_others.push(dupla);
							break;
					}
				}
			} else if (typeof _model._inputAndPublicParameters !== "undefined") { /* NEW IMPLEMENTATION */
				newImplement = true;
				var inputAux = _model._inputAndPublicParameters;
				var outputAux = _model._outputAndPublicParameters;
				for (var k in inputAux) {
					if (inputAux[k].includes(':')) {
						inputAux[k] = inputAux[k].slice(0, inputAux[k].indexOf(' '));
					}
					dupla = [];
					dupla.push(inputAux[k]);
					dupla.push(inputAux[k]);
					switch (typeof obj[inputAux[k]]) {
						case 'number':
							keys_number_input.push(dupla);
							break;
						case 'boolean':
							keys_boolean_input.push(dupla);
							break;
						case 'function':
							if (BlocklyConf.returnFunctionToReplace() !== inputAux[k]) {
								if ((inputAux[k].toString().includes('return ')))
									function_from_ejss_with_return.push({
										'name': inputAux[k],
										'params': getParamNames(obj[inputAux[k]])
									});
								else
									function_from_ejss.push({'name': inputAux[k], 'params': getParamNames(obj[inputAux[k]])});
							}
							break;
						default:
							keys_others_input.push(dupla);
							break;
					}
				}
				for (k in outputAux) {
					if (outputAux[k].includes(':')) {
						outputAux[k] = outputAux[k].slice(0, outputAux[k].indexOf(' '));
					}
					dupla = [];
					dupla.push(outputAux[k]);
					dupla.push(outputAux[k]);
					switch (typeof obj[outputAux[k]]) {
						case 'number':
							keys_number_output.push(dupla);
							break;
						case 'boolean':
							keys_boolean_output.push(dupla);
							break;
						default:
							//console.log('Output');
							//console.log(obj[outputAux[k]]+' '+typeof obj[outputAux[k]]);
							keys_others_output.push(dupla);
							break;
					}
				}
			}
			if (_vars !== "") {
				for (var e in _vars) {
					dupla = [];
					dupla.push(_vars[e]);
					dupla.push(_vars[e]);
					events_vars.push(dupla);
				}
			}
		}
	});

	function preparePage(){
		if(detectMob()){
			document.getElementById("enable_drag").style.display=none;
		}
		require(['mod_ejsapp/vendor/blockly/blockly_compressed','mod_ejsapp/blockly_conf'], function (Blockly,BlocklyConf) {
			if (BlocklyConf.returnEventsBlockly()) {
				workspaceEvents = Blockly.inject('blocklyDivEvents', {
					grid:
						{
							spacing: 25,
							length: 3,
							colour: '#ccc',
							snap: true
						},
					media: './vendor/blockly/media/',
					toolbox: toolboxEvents,
					collapse: true,
					zoom: {controls: true}
				});
				workspaceEvents.registerButtonCallback("createVariablePressed", createVariable, "");
				workspaceEvents.registerToolboxCategoryCallback('controls', controls);
				workspaceEvents.registerToolboxCategoryCallback('jss2', jss2);
				/*workspaceEvents.registerButtonCallback('jsButtonPressed2', jsButton2);
                workspaceEvents.registerButtonCallback('loadjsButtonPressed2', loadjsButton2);*/
				workspaceEvents.registerToolboxCategoryCallback('generalVars', generalVars);
				workspaceEvents.addChangeListener(checkEventsBlocks);
			}
			if (BlocklyConf.returnChartsBlockly()) {
				workspaceCharts = Blockly.inject('blocklyDivCharts', {
					grid:
						{
							spacing: 25,
							length: 3,
							colour: '#ccc',
							snap: true
						},
					media: './vendor/blockly/media/',
					toolbox: toolboxCharts,
					collapse: true,
					zoom: {controls: true}
				});
				workspaceCharts.registerButtonCallback("createVariablePressed", createVariable, "");
				workspaceCharts.registerToolboxCategoryCallback('generalVars', generalVars);
				workspaceCharts.addChangeListener(checkChartsBlocks);
			}

			if(BlocklyConf.returnControllerBlockly()){
				createControllerPanel();
			}
		});
	}

	require(['mod_ejsapp/vendor/blockly/blockly_compressed','mod_ejsapp/blockly_conf'], function (Blockly,BlocklyConf) {
		updateControllerPanelDiv = function(num) {
			var element = document.getElementById('ControllerHeader');
			if (BlocklyConf.returnRemoteController()[num]) {
				element.innerHTML = '<h4 id="ControllerHeader" style="margin-top:0; text-align=center">' + Blockly.ControllerRemote + '</h4>';
			} else {
				var texto = getInfoFromFunctionName(BlocklyConf.returnFunctionToReplace()[num]);
				if (texto[1] !== '')
					element.innerHTML = '<h4 id="ControllerHeader" style="margin-top:0; text-align=center">' + Blockly.ControllerTextInitial +
						'<span style="color: green">' + BlocklyConf.returnFunctionToReplace()[num] + '</span>' + Blockly.ControllerTextSecond + '<span style="color: red"><br>' +
						texto[0] + '</span>' + Blockly.ControllerTextThird + '<span style="color: blue">' + texto[1] + '</span>.</h4>';
				else
					element.innerHTML = '<h4 id="ControllerHeader" style="margin-top:0; text-align=center">' + Blockly.ControllerTextInitial +
						'<span style="color: green">' + BlocklyConf.returnFunctionToReplace()[num] + '</span>' + Blockly.ControllerTextSecond + '<span style="color: red"><br>' +
						texto[0] + '</span>' + Blockly.ControllerTextThird + Blockly.ControllerTextForth + '</h4>';
			}
		}
	});

	require(['mod_ejsapp/blockly_conf'], function (BlocklyConf) {
		createControllerPanel = function() {
			if (BlocklyConf.returnControllerFunctionLanguage() === "blockly") {
				require(['mod_ejsapp/vendor/blockly/blockly_compressed'], function(Blockly) {
					workspaceFunctions = Blockly.inject('blocklyDivController', {
						grid:
							{
								spacing: 25,
								length: 3,
								colour: '#ccc',
								snap: true
							},
						media: './vendor/blockly/media/',
						toolbox: toolboxControllers,
						collapse: true,
						zoom: {controls: true}
					});
				});
				workspaceFunctions.registerButtonCallback("createVariablePressed", createVariable, "");
				workspaceFunctions.registerToolboxCategoryCallback('generalVars', generalVars);
				workspaceFunctions.addChangeListener(checkFunctionsBlocks);
			} else {
				functionEditor = ace.edit("blocklyDivController");
				functionEditor.resize();
				functionEditor.$blockScrolling = Infinity;
				functionEditor.setTheme("ace/theme/xcode");
				functionEditor.getSession().setMode("ace/mode/" + BlocklyConf.returnControllerFunctionLanguage());
				document.getElementById('blocklyDivController').addEventListener("focusout", focusOutFunction);
			}
			document.getElementById('ControllerDiv').insertAdjacentHTML('afterbegin',
				'<h4 id="ControllerHeader"></h4>');
		}
	});

	function getCodeFromName(list,name){
		for( var i = 0; i < list.length; i++){
			   if ( list[i].name === name)
				 return [list[i].code,i];
		}
		return null;
	}

	function getCodeFromNameFunctions(list,name){
		for( var i = 0; i < list.length; i++){
			for(var j = 0; j<list[i].length;j++) {
				if (list[i][j].name === name)
					return [list[i][j].code, list[i][j].name];
			}
		}
		return null;
	}

	function variableExists(name){
		for(i in modelVariablesList){
			if(modelVariablesList[i]===name) return true;
		}
		return false;
	}

	/* CALLBACKS */

	function jsButton(text){
		var result;
		if(typeof(text)!=='string')
			result = prompt(Blockly.Msg["NewJavascript"], "");
		else
			result = prompt(text, "");

		if(result!==null){
			if(javaScriptsNamesListGeneral.indexOf(result)===-1)
				addJs(result, "// This is JavaScript named " + result + "\n", jsCodesGeneral, visualJSGeneral,
					workspace, javaScriptsNamesListGeneral);
			else
				jsButton(Blockly.Msg["ForbiddenName"]);
		}
	}

	function addJs(name,texto,jsCodeList,jsVisual,workspc,javaScriptsNamesList){
		jsVisual.push([name,name]);
		jsCodeList.push({"name":name,"code":texto});
		if(jsCodeList.length===1)
			workspc.toolbox_.refreshSelection();
		javaScriptsNamesList.push(name);
	}

	function jsButton2(texto){
		var result;
		if(typeof(texto)!=='string')
			result = prompt(Blockly.Msg["NewJavascript"], "");
		else
			result = prompt(texto, "");

		if(result!==null){
			if(javaScriptsNamesListEvents.indexOf(result)===-1)
				addJs(result, "// This is JavaScript2 named " + result + "\n", jsCodesEvents, visualJSEvents,
					workspaceEvents, javaScriptsNamesListEvents);
			else
				jsButton2(Blockly.Msg["ForbiddenName"]);
		}
	}

	function loadjsButton(){
		loadjsb(jsCodesGeneral,visualJSGeneral,javaScriptsNamesListGeneral);
	}

	function loadjsButton2(){
		loadjsb(jsCodesEvents,visualJSEvents,javaScriptsNamesListEvents);
	}

	function loadjsb(jsList,jsVisualList,javaScriptsNamesList){
		var input = document.createElement('input');
		input.type = 'file';
		input.accept='.js';

		/*input.onchange = e => {
		   // getting a hold of the file reference
		   var file = e.target.files[0];
		   // setting up the reader
		   var reader = new FileReader();
		   reader.readAsText(file,'UTF-8');

		   // here we tell the reader what to do when it's done reading...
		   reader.onload = readerEvent => {
			  if(javaScriptsNamesList.indexOf(file.name)===-1){
				  javaScriptsNamesList.push(file.name);
				  var content = readerEvent.target.result; // this is the content!
				  jsVisualList.push([file.name,file.name]);
				  jsList.push({"name":file.name,"code":content});
				  if(jsList.length===1)
						workspace.toolbox_.refreshSelection();
			  }
			  else{
				  printError("File already uploaded");
			  }
		   }
		}*/

		console.log("File loaded");
		input.click();
	}

	function createVariable(texto){
		var result;
		if(typeof(texto)!=='string')
			result = prompt(Blockly.Msg["NewVar"], "");
		else
			result = prompt(texto, "");
		if(result!==null){
			if((modelVariablesList.indexOf(result)===-1) && (blocklyVariablesList.indexOf(result)===-1)){
				addVariable(result);
			}
			else{
				createVariable(Blockly.Msg["ForbiddenName"]);
			}
		}
	}

	function addVariable(result){
		if((modelVariablesList.indexOf(result)===-1) && (blocklyVariablesList.indexOf(result)===-1)) {
			blocklyVariablesList.push(result);
			if (!newImplement) {
				keys_number.push([result, result]);
				keys_boolean.push([result, result]);
			} else {
				keys_number_input.push([result, result]);
				keys_number_output.push([result, result]);
				keys_boolean_input.push([result, result]);
				keys_boolean_output.push([result, result]);
			}
		}
	}

	remove = function(ary, elem) {
		var i = ary.indexOf(elem);
		if (i >= 0) ary.splice(i, 1);
		return ary;
	};

	function removeVariable(result){
		if (!newImplement) {
			keys_number=remove(keys_number,result);
			keys_boolean=remove(keys_boolean,result);
		} else {
			keys_number_input=remove(keys_number_input,result);
			keys_number_output=remove(keys_number_output,result);
			keys_boolean_input=remove(keys_boolean_input,result);
			keys_boolean_output=remove(keys_boolean_output,result);
		}

	}

	function getNamesFromBlocklyVariables(arrayVariables){
		var names = [];
		for(var i = 0;i<arrayVariables.length;i++){
			names.push(arrayVariables[i].name);
		}
		return names;
	}

	function myUpdateFunction(event){
		require(['mod_ejsapp/vendor/blockly/blockly_compressed'], function (Blockly) {
			if (event.type == Blockly.Events.CHANGE || Blockly.Events.CHANGE) {
				if (experimentOpen !== -1) {
					var xml = Blockly.Xml.workspaceToDom(workspace);
					experimentsList[experimentOpen].code = Blockly.Xml.domToText(xml);
				}
				// Function parameters to variables
				if (experimentOpen !== -1) {

					for (var i = 0; i < workspace.getAllVariables().length; i++) {
						addVariable(workspace.getAllVariables()[i].name);
						functionsVariableList.push(workspace.getAllVariables()[i].name);
					}
					for (var i = 0; i < functionsVariableList.length; i++) {
						if ((getNamesFromBlocklyVariables(workspaceEvents.getAllVariables()).indexOf(functionsVariableList[i]) === -1) && (getNamesFromBlocklyVariables(workspace.getAllVariables()).indexOf(functionsVariableList[i]))) {
							removeVariable(functionsVariableList[i]);
						}
					}
				} else if (eventOpen !== -1) {
					for (var i = 0; i < workspaceEvents.getAllVariables().length; i++) {
						addVariable(workspaceEvents.getAllVariables()[i].name);
						functionsVariableList.push(workspaceEvents.getAllVariables()[i].name);
					}
					for (var i = 0; i < functionsVariableList.length; i++) {
						if ((getNamesFromBlocklyVariables(workspaceEvents.getAllVariables()).indexOf(functionsVariableList[i]) === -1) && (getNamesFromBlocklyVariables(workspace.getAllVariables()).indexOf(functionsVariableList[i]))) {
							removeVariable(functionsVariableList[i]);
						}
					}
				}

			}
			if (event.element === "click") {
				if ((event.blockId !== null && event.blockId !== undefined) && (workspace.getBlockById(event.blockId) !== null)) {
					if ((workspace.getBlockById(event.blockId).type === 'jsUpDown') || (workspace.getBlockById(event.blockId).type === 'jsLeftRight')) {
						document.getElementById('myModal').style.display = "block";
						selectJS(0, workspace.getBlockById(event.blockId).getFieldValue("jsOption"));
					}
				} else {
					document.getElementById('myModal').style.display = "none";
				}
			}
		});
	}

	function checkEventsBlocks(event){
		if (event.element ==="click"){
			if((event.blockId!==null && event.blockId!==undefined)&&(workspaceEvents.getBlockById(event.blockId)!==null)){
				if((workspaceEvents.getBlockById(event.blockId).type === 'jsUpDown2')||( workspaceEvents.getBlockById(event.blockId).type === 'jsLeftRight2')){
					document.getElementById('myModal').style.display = "block";
					selectJS(1,workspaceEvents.getBlockById(event.blockId).getFieldValue("jsOption"));
				}
			}
			else{
				document.getElementById('myModal').style.display = "none";
			}
		}
		require(['mod_ejsapp/vendor/blockly/blockly_compressed'], function (Blockly) {
			if (event.type == Blockly.Events.CHANGE || Blockly.Events.CHANGE) {
				if (eventOpen !== -1) {
					var xml = Blockly.Xml.workspaceToDom(workspaceEvents);
					eventsList[eventOpen].code = Blockly.Xml.domToText(xml);
				}
			}
		});
	}

	function checkChartsBlocks(event){
		require(['mod_ejsapp/vendor/blockly/blockly_compressed'], function (Blockly) {
			if (event.type == Blockly.Events.CHANGE || Blockly.Events.CHANGE) {
				if (chartOpen !== -1) {
					var xml = Blockly.Xml.workspaceToDom(workspaceCharts);
					chartsList[chartOpen].code = Blockly.Xml.domToText(xml);
				}
			}
		});
	}
	require(['mod_ejsapp/blockly_conf'], function (BlocklyConf) {
		getFunctionOpen = function () {
			for (var i = 0; i < BlocklyConf.returnFunctionToReplace().length; i++) {
				if (functionOpen[i] != -1)
					return i;
			}
			return -1;
		}
	});

	function checkFunctionsBlocks(event){
		require(['mod_ejsapp/vendor/blockly/blockly_compressed'], function (Blockly) {
			if (event.type == Blockly.Events.CHANGE || Blockly.Events.CHANGE) {
				var index = getFunctionOpen();
				if (index !== -1) {
					if (functionUseBlockly) {
						var xml = Blockly.Xml.workspaceToDom(workspaceFunctions);
						functionsList[index][functionOpen[index]].code = Blockly.Xml.domToText(xml);
					}
				}
			}
		});
	}

	function getIndexFunctionOpen(){
		for(var i = 0; i< functionOpen.length;i++){
			if(functionOpen[i]!==-1)
				return i;
		}
	}

	function focusOutFunction(){
		functionsList[getIndexFunctionOpen()][functionOpen[[getIndexFunctionOpen()]]].code=functionEditor.getValue();
		errorFunctionsList[getIndexFunctionOpen()]=functionEditor.getSession().getAnnotations();
	}

	/*
	// ACE EDITOR FOR JAVASCRIPT CODE //
									  */

	function initJSFrame(place){
		editorJS = ace.edit(place);
		editorJS.$blockScrolling = Infinity ;
		editorJS.setTheme("ace/theme/xcode");
		editorJS.getSession().setMode("ace/mode/javascript");
		document.getElementById('_javaScriptFrame').style.fontSize='14px';
		jsCodesGeneral = [];
		jsCodesEvents = [];
	}

	function selectJS(n,text){
		if ( n === 0) {
			for(var i=0;i<jsCodesGeneral.length;i++){
				if(jsCodesGeneral[i].name===text){
					editorJS.setValue(jsCodesGeneral[i].code);
					jsOpenedGeneral=i;
					jsOpenType=0;
					return;
				}
			}
		} else if (n === 1) {
			for(var i=0;i<jsCodesEvents.length;i++){
				if(jsCodesEvents[i].name===text){
					editorJS.setValue(jsCodesEvents[i].code);
					jsOpenedEvents=i;
					jsOpenType=1;
					return;
				}
			}
		}
	}

	/* INTERFACE */

	/* DRAGGABLE ELEMENTS */
	function resize(){
		require(['mod_ejsapp/vendor/blockly/blockly_compressed','mod_ejsapp/blockly_conf'], function (Blockly,BlocklyConf) {
			if (experimentOpen != -1) {
				Blockly.svgResize(workspace);
			} else if (chartOpen != -1) {
				Blockly.svgResize(workspaceCharts);
			} else if (eventOpen != -1) {
				Blockly.svgResize(workspaceEvents);
			} else if (getFunctionOpen() != -1) {
				if (BlocklyConf.returnControllerFunctionLanguage() === "blockly") {
					Blockly.svgResize(workspaceFunctions);
				} else {
					functionEditor.resize();
				}
			}
		});
	}


	function returning(id){
		/*document.getElementById(id).style.width = "100%";

		document.getElementById(id).style.zIndex = "1";
		document.getElementById(id).style.position = "";
		document.getElementById("return_"+id).style.display = "none";*/
		if (document.getElementById('#'+id + "header")){
			document.getElementById('#'+id + "header").style.display = "";
		}
		document.getElementById( 'drag_#'+id).style.display = "none";
		$('#'+id).append($("#dragContent"+id).contents()); //move element into wrapper
		document.getElementById( "dragheader").style.display = "none";
		if(id==="ScriptBox"){
			document.getElementById(id).style.height = "";
			resize();
		}
	}

	/* Make the DIV element draggable: */
	function copyToDragDiv(id){
		var iDiv = document.createElement('div');
		iDiv.id = 'drag_'+id;
		var subid = id.substr(1);
		iDiv.innerHTML = '<div class="topnav-right align-self-end" style="float:right">' +
			'<i id="dragheader" class="fa fa-arrows-alt fa-2x" aria-hidden = "true" style="cursor:move;"></i>' +
			'<i id="return_drag" class="fa fa-window-restore fa-2x" aria-hidden = "true" style="display:none;margin-left:1rem;" onclick="returning(\''+subid+'\');"></i>' +
			'</div>' +
			'<div id="dragContent'+subid+'"></div>';
		var container = document.getElementById('prevDrag');
		container.insertBefore(iDiv, container.firstChild);
		/*document.getElementsByTagName('body')[0].appendChild(iDiv);*/
		dragElement(document.getElementById('drag_'+id));



		if (document.getElementById(id + "header")){
			document.getElementById(id + "header").style.display = "none";
		}
		document.getElementById( 'drag_'+id).style.display = "";
		var content = $(id).contents(); //find element
		var wrapper =$("#dragContent"+subid); //create wrapper element
		wrapper.append(content); //move element into wrapper
		document.getElementById( "dragheader").style.display = "";
		if(id==="#ScriptBox"){
			document.getElementById("dragContentScriptBox").style.height = "450px";
			document.getElementById("dragContentScriptBox").style.width = "600px";
			resize();
		}
	}

	function dragElement(elmnt) {
	  var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
		document.getElementById( elmnt.id).style.display = "none";
	  if (document.getElementById("dragheader")){
		/* if present, the header is where you move the DIV from: */
		document.getElementById("dragheader").onmousedown = dragMouseDown;
		document.getElementById("dragheader").style.display = "none";
	  }
	  elmnt.style.width = "auto";
	  elmnt.style.zIndex = "9999";
	  elmnt.style.position = "absolute";
	  elmnt.style.background = "White";
	  document.getElementById('return_drag').style.display = "";

	  function dragMouseDown(e) {
		e = e || window.event;
		e.preventDefault();
		/* get the mouse cursor position at startup: */
		pos3 = e.clientX;
		pos4 = e.clientY;
		document.onmouseup = closeDragElement;
		/* call a function whenever the cursor moves: */
		document.onmousemove = elementDrag;
	  }



	  function elementDrag(e) {
		e = e || window.event;
		e.preventDefault();
		/* calculate the new cursor position: */
		pos1 = pos3 - e.clientX;
		pos2 = pos4 - e.clientY;
		pos3 = e.clientX;
		pos4 = e.clientY;
		/* set the element's new position: */
		elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
		elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
	  }

	  function closeDragElement() {
		/* stop moving when mouse button is released: */
		document.onmouseup = null;
		document.onmousemove = null;
		elmnt.style.background_color = "";
	  }
	}


	Element.prototype.remove = function() {
		this.parentElement.removeChild(this);
	};

	NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
		for(var i = this.length - 1; i >= 0; i--) {
			if(this[i] && this[i].parentElement) {
				this[i].parentElement.removeChild(this[i]);
			}
		}
	};

	removeAndCloseScript = function (id,funindex){
		if(experimentOpen!==-1)	{
			if(experimentsList[experimentOpen].name === id) {
				document.getElementById("ScriptBox").style.display="none";
				experimentOpen=-1;
			}
		}
		else if(chartOpen!==-1) {
			if(chartsList[chartOpen].name === id) {
				document.getElementById("ScriptBox").style.display="none";
				chartOpen=-1;
			}
		}
		else if(eventOpen!==-1) {
			if(eventsList[eventOpen].name === id) {
				document.getElementById("ScriptBox").style.display="none";
				eventOpen=-1;
			}
		}
		else if(functionOpen[funindex]!==-1) {
			if(functionsList[funindex][functionOpen[funindex]].name === id) {
				document.getElementById("ScriptBox").style.display="none";
				functionOpen[funindex]=-1;
			}
		}
		removeScript(id,funindex);
	};

	removeScript = function (id,funindex){
		document.getElementById(id).remove();
		//document.getElementById(id+"list").remove();
		for(var i = 0; i < experimentsList.length; i++){
		   if ( experimentsList[i].name === id) {
			if(experimentSelected===id) {experimentSelected="";}
			 experimentsList.splice(i, 1);
			   experimentSelected="";
			   if(experimentOpen>0) {
				   if (experimentsList[experimentOpen] == null)
					   experimentOpen--;
			   }
			 return;
		   }
		}

		for(var i = 0; i < chartsList.length; i++){
		   if ( chartsList[i].name === id) {
			   if(chartSelected===id) {chartSelected="";}
			 chartsList.splice(i, 1);
			   if(chartOpen>0) {
				   if (chartsList[chartOpen] == null)
					   chartOpen--;
			   }
			 return;
		   }
		}

		for(var i = 0; i < eventsList.length; i++){
		   if ( eventsList[i].name === id) {
			   if(eventSelected===id) {eventSelected="";}
			 eventsList.splice(i, 1);
			   if(eventOpen>0) {
				   if (eventsList[eventOpen] == null)
					   eventOpen--;
			   }
			 return;
		   }
		}

		for(var l = 0; l < functionsList[funindex].length; l++) {
			if (functionsList[funindex][l].name === id) {
				if (functionSelected[funindex] === id) {
					functionSelected[funindex] = "";
				}
				functionsList[funindex].splice(l, 1);
				if(functionOpen[funindex]>0) {
					if (functionsList[funindex][functionOpen[funindex]] == null)
						functionOpen[funindex]--;
				}
				return;
			}
		}

	}

	/*function addLabelToDropDown(id2,name,list,image){
		var d2 = document.getElementById(id2);
		if(image){
			d2.insertAdjacentHTML('beforeend','<option id="new" value="' + name + '">' + name + '</option>');
		}
		else{
			d2.insertAdjacentHTML('beforeend','<option id="' + name + 'list" value="' + name + '">' + name + '</option>');
		}
	}*/

	addnewScript = function (num,name,code,id,id2,list,funindex){
		var d1 = document.getElementById(id);
		if(num!=4) {
			d1.insertAdjacentHTML('beforeend', '<div id="' + name + '" style="display:flex;cursor:pointer;' +
				'justify-content:space-between">' +
				'<a onclick="colorSelection(' + num + ',\'' + name + '\',true)"><i class="fa fa-play" style="margin-left: .5rem"></i></a>' +
				'<a onclick="showScript(' + num + ',\'' + name + '\');">' + ' ' + name + '</a>' + '<div class="topnav-right">' +
				'<a onclick="removeAndCloseScript(\'' + name + '\')"><i  class="fa fa-times" style="margin-right: .5rem"></i></a>' + '</div></div>');
			list.push({"name": name, "code": code});
			//addLabelToDropDown(id2,name,list,false);
			showScript(num, name);
		}
		else{
			d1.insertAdjacentHTML('beforeend', '<div id="' + name + '" style="display:flex;cursor:pointer;' +
				'justify-content:space-between">' +
				'<a onclick="colorSelection(' + num + ',\'' + name + '\',true,'+funindex+')"><i class="fa fa-play" style="margin-left: .5rem"></i></a>' +
				'<a onclick="showScript(' + num + ',\'' + name + '\','+funindex+');">' + ' ' + name + '</a>' + '<div class="topnav-right">' +
				'<a onclick="removeAndCloseScript(\'' + name + '\','+funindex+')"><i  class="fa fa-times" style="margin-right: .5rem"></i></a>' + '</div></div>');
			list.push({"name": name, "code": code});
			showScript(num, name, funindex);
		}
	}

	function colorSelection(num,name,borrar,funindex){
		var elem= document.getElementById(name);
		if(num===1){
			if(experimentSelected!==""){
				var elem2= document.getElementById(experimentSelected);
				elem2.style.color='Black';
				elem2.style.fontWeight='normal';
				if(borrar && (experimentSelected===name)){
					experimentSelected="";
					return;
				}
			}
			elem.style.color='Green';
			elem.style.fontWeight='bold';
			experimentSelected=name;
		}
		else if(num===2){
			if(chartSelected!==""){
				var elem2= document.getElementById(chartSelected);
				elem2.style.color='Black';
				elem2.style.fontWeight='normal';
				if(borrar && (chartSelected===name)){
					chartSelected="";
					return;
				}
			}
			elem.style.color='Blue';
			elem.style.fontWeight='bold';
			chartSelected=name;
		}
		else if(num===3){
			if(eventSelected!==""){
				var elem2= document.getElementById(eventSelected);
				elem2.style.color='Black';
				elem2.style.fontWeight='normal';
				if(borrar && (eventSelected===name)){
					eventSelected="";
					return;
				}
			}
			elem.style.color='Red';
			elem.style.fontWeight='bold';
			eventSelected=name;
		}
		else{
			if(functionSelected[funindex]!==""){
				var elem2= document.getElementById(functionSelected[funindex]);
				elem2.style.color='Black';
				elem2.style.fontWeight='normal';
				if(borrar && (functionSelected[funindex]===name)){
					functionSelected[funindex]="";
					return;
				}
			}
			elem.style.color='Peru';
			elem.style.fontWeight='bold';
			functionSelected[funindex]=name;
		}
	}
	require(['mod_ejsapp/blockly_conf'], function (BlocklyConf) {
		newScript = function(num, funindex) {
			if (num === 1) {
				var name = prompt(Blockly.Msg["NewExperimentScript"], "Experiment " + (experimentsList.length + 1));
				if (name != null) {
					for (var i = 0; i < experimentsList.length; i++) {
						if (experimentsList[i].name === name) {
							newScript(num);
							printError(Blockly.Msg["NewExperimentScriptError"]);
							return;
						}
					}
					addnewScript(num, name, '<xml></xml>', 'experimentsScripts', 'experimentSelection', experimentsList);
				}
			} else if (num === 2) {
				var name = prompt(Blockly.Msg["NewChartScript"], "Chart " + (chartsList.length + 1));
				if (name != null) {
					for (var i = 0; i < chartsList.length; i++) {
						if (chartsList[i].name === name) {
							newScript(num);
							printError(Blockly.Msg["NewChartScriptError"]);
							return;
						}
					}
					addnewScript(num, name, '<xml></xml>', 'chartsScripts', 'chartSelection', chartsList);
				}
			} else if (num === 3) {
				var name = prompt(Blockly.Msg["NewEventScript"], "Event " + (eventsList.length + 1));
				if (name != null) {
					for (var i = 0; i < eventsList.length; i++) {
						if (eventsList[i].name === name) {
							newScript(num);
							printError(Blockly.Msg["NewEventScriptError"]);
							return;
						}
					}
					addnewScript(num, name, '<xml></xml>', 'eventsScripts', 'eventSelection', eventsList);
				}
			} else if (num === 4) {
				var name = prompt(Blockly.Msg["NewControllerScript"], BlocklyConf.returnFunctionToReplace()[funindex] + " " + (functionsList[funindex].length + 1));
				if (name != null) {
					for (var i = 0; i < functionsList[funindex].length; i++) {
						if (functionsList[funindex][i].name === name) {
							newScript(num, funindex);
							printError(Blockly.Msg["NewControllerScriptError"]);
							return;
						}
					}
					var message = "";
					if (functionUseBlockly) message = "<xml></xml>";
					addnewScript(num, name, message, 'functionsScripts' + funindex, 'controllerSelection', functionsList[funindex], funindex);

				}
			}
		}
	});

	require(['mod_ejsapp/vendor/blockly/blockly_compressed'], function (Blockly) {
	showScript = function (num,name,funindex){
		document.getElementById("ScriptBox").style.display = "none";
		document.getElementById("blocklyDivExperiments").style.display="none";
		document.getElementById("blocklyDivCharts").style.display="none";
		document.getElementById("blocklyDivEvents").style.display="none";
		document.getElementById("ControllerDiv").style.display="none";
		if((experimentOpen!==-1)&&(num===1)){if( experimentsList[experimentOpen].name === name) { experimentOpen=-1; return;}}
		if((chartOpen!==-1)&&(num===2)){if( chartsList[chartOpen].name === name) { chartOpen=-1; return;}}
		if((eventOpen!==-1)&&(num===3)){if( eventsList[eventOpen].name === name) { eventOpen=-1; return;}}
		if((functionOpen[funindex]!==-1)&&(num===4)){if( functionsList[funindex][functionOpen[funindex]].name === name) { functionOpen[funindex]=-1; return;}}
		//if((functionOpen[funindex]!==-1)&&(num===4)){if( functionsList[functionOpen].name === name) { functionOpen[funindex]=-1; return;}}
		experimentOpen=-1;
		chartOpen=-1;
		eventOpen=-1;
		for(var i=0;i<functionOpen.length;i++){
			functionOpen[i]=-1;
		}
		document.getElementById("titleScriptBox").innerHTML = name;
		document.getElementById("ScriptBox").style.display = "block";

			if (num === 1) {
				var result = getCodeFromName(experimentsList, name);
				code = Blockly.Xml.textToDom(result[0]);
				document.getElementById("blocklyDivExperiments").style.display = "inline-block";
				Blockly.svgResize(workspace);
				experimentOpen = result[1];
				workspace.clear();
				if (code !== '<xml></xml>') {
					Blockly.Xml.domToWorkspace(code, workspace);
				}
			} else if (num === 2) {
				var result = getCodeFromName(chartsList, name);
				code = Blockly.Xml.textToDom(result[0]);
				document.getElementById("blocklyDivCharts").style.display = "inline-block";
				Blockly.svgResize(workspaceCharts);
				chartOpen = result[1];
				workspaceCharts.clear();
				if (code !== '<xml></xml>') {
					Blockly.Xml.domToWorkspace(code, workspaceCharts);
				}
			} else if (num === 3) {
				var result = getCodeFromName(eventsList, name);
				code = Blockly.Xml.textToDom(result[0]);
				document.getElementById("blocklyDivEvents").style.display = "inline-block";
				Blockly.svgResize(workspaceEvents);
				eventOpen = result[1];
				workspaceEvents.clear();
				if (code !== '<xml></xml>') {
					Blockly.Xml.domToWorkspace(code, workspaceEvents);
				}
			} else if (num === 4) {
				updateControllerPanelDiv(funindex);
				var result = getCodeFromName(functionsList[funindex], name);
				document.getElementById("ControllerDiv").style.display = "inline-block";
				functionOpen[funindex] = result[1];
				//console.log("func open "+funindex+" "+result[1]);
				if (functionUseBlockly) {
					code = Blockly.Xml.textToDom(result[0]);
					Blockly.svgResize(workspaceFunctions);
					workspaceFunctions.clear();
					if (code !== '<xml></xml>') {
						Blockly.Xml.domToWorkspace(code, workspaceFunctions);
					}
				} else {
					functionEditor.setValue(result[0]);
					functionEditor.resize();
				}
			}

		colorSelection(num,name,false,funindex);
	}
	});

	function minimize(object){
		document.getElementById(object).style.display = "none";
		document.getElementById("min"+object).style.display = "none";
		document.getElementById("full"+object).style.display = "none";
		document.getElementById("max"+object).style.display = "";
	}

	function maximize(object){
		document.getElementById(object).style.display = "block";
		document.getElementById("max"+object).style.display = "none";
		document.getElementById("full"+object).style.display = "";
		document.getElementById("min"+object).style.display = "";
	}

	/*function fullscreen(object){
		if (document.getElementById(object).requestFullscreen) {
			document.getElementById(object).requestFullscreen();
		} else if (document.getElementById(object).mozRequestFullScreen) {
			document.getElementById(object).mozRequestFullScreen();
		} else if (document.getElementById(object).webkitRequestFullscreen) {
			document.getElementById(object).webkitRequestFullscreen();
		} else if (document.getElementById(object).msRequestFullscreen) {
			document.getElementById(object).msRequestFullscreen();
		}
	}*/

	function allHidden(){
		return ((document.getElementById('1').style.display === "none") &&
			(document.getElementById('2').style.display === "none") &&
			(document.getElementById('3').style.display === "none") &&
			(document.getElementById('4').style.display === "none"))
	}

	function showLog(){
		if(document.getElementById('footer').style.display === "none")
			document.getElementById('footer').style.display = "block";
		else
			document.getElementById('footer').style.display = "none";
	}


	function detectMob() {
		return ( ( window.innerWidth <= 800 ) && ( window.innerHeight <= 600 ) );
	}



	require(['mod_ejsapp/vendor/sortable'], function(sortable) {
		dragableElements = function (dragElems,dragElems2,blockly){
			if(!detectMob()) {
				for (var i = 0; i < dragElems2.length; i++) {
					var elem = document.getElementById(dragElems2[i]);
					new sortable.create(elem, {group: 'shared2',handle: ".my_handle", ghostClass: "ghost", sort:false, swap: true, animation: 150});
				}
				for (var i = 0; i < dragElems.length; i++) {
					var elem = document.getElementById(dragElems[i]);
					new sortable.create(elem, {group: 'shared', handle: ".my_handle", ghostClass: "ghost", sort:false, swap: true, animation: 150});
					if (blockly == i) {
						elem.onmouseup = function() {
							resize();
						};
						elem.onfocusout = function() {
							resize();
						};
						elem.ondragend = function() {
							resize();
						};
					}
				}

			}
		};
	});

	function enableDrag(){
		var elements = document.getElementsByClassName('my_handle');
		enableDragItems = !enableDragItems;
		if(enableDragItems) {
			document.getElementById('enable_drag').style.background = 'lightgrey';
			for (var i=0; i < elements.length; i++){
				elements[i].style.display = '';
			}
			for (var i = 0; i < dragElems.length; i++) {
				document.getElementById(dragElems[i]).style.outlineOffset = '2px';
				document.getElementById(dragElems[i]).style.outline = '2px dashed lightsalmon';
			}

			for (var i = 0; i < dragElems2.length; i++) {
				document.getElementById(dragElems2[i]).style.outlineOffset = '2px';
				document.getElementById(dragElems2[i]).style.outline = '2px dashed lightblue';
			}
		}
		else {
			document.getElementById('enable_drag').style.background = 'white';
			for (var i=0; i < elements.length; i++) {
				elements[i].style.display = 'none';
			}
			for (var i = 0; i < dragElems.length; i++) {
				document.getElementById(dragElems[i]).style.outlineOffset = '';
				document.getElementById(dragElems[i]).style.outline = '';
			}

			for (var i = 0; i < dragElems2.length; i++) {
				document.getElementById(dragElems2[i]).style.outlineOffset = '';
				document.getElementById(dragElems2[i]).style.outline = '';
			}
		}
	}



	function printError(textError){
		if (typeof errorInterval !== 'undefined') {
			if(errorInterval!==null)
				clearInterval(errorInterval);
		}
		if(document.getElementById('footer').style.display==="none") showLog();
		var d = new Date();
		var n = d.toLocaleTimeString();
		document.getElementById('errorArea').value=document.getElementById('errorArea').value+'\n   '+n+" "+textError;
		document.getElementById('errorArea').scrollTop = document.getElementById('errorArea').scrollHeight;
		errorInterval = setInterval(changeError, 10000);
	}

	function changeError() {
		if(document.getElementById('footer').style.display!=="none") showLog();
		clearInterval(errorInterval);
	}

	/* <SAVE & LOAD FILES> */

	function saveCode() {
		json = JSON.stringify(codeToSave());
		_model.saveText(null, 'blk', json);
	}

	function codeToSave(){
		var saveExp=[];
		for(var i=0;i<experimentsList.length;i++){
			saveExp.push({"name":experimentsList[i].name,"code":experimentsList[i].code});
		}
		var saveEvents=[];
		for(var i=0;i<eventsList.length;i++){
			saveEvents.push({"name":eventsList[i].name,"code":eventsList[i].code});
		}
		var saveCharts=[];
		for(var i=0;i<chartsList.length;i++){
			saveCharts.push({"name":chartsList[i].name,"code":chartsList[i].code});
		}
		var saveFunctions=[];
		for(var i=0;i<functionsList.length;i++){
			saveFunctions.push([]);
			for(var j=0;j<functionsList[i].length;j++){
				saveFunctions[i].push({"name":functionsList[i][j].name,"code":functionsList[i][j].code});
			}
		}
		return {experiments:saveExp, events:saveEvents, charts:saveCharts, functions:saveFunctions,
			vars:blocklyVariablesList, javaScriptsNamesListGeneral:javaScriptsNamesListGeneral,
			javaScriptsNamesListEvents:javaScriptsNamesListEvents, jsCodesGeneral:jsCodesGeneral,
			jsCodesEvents:jsCodesEvents, visualJSGeneral:visualJSGeneral, visualJSEvents:visualJSEvents};
	}

	function loadCode() {
		_model.readText(null, '.blk',
			function(json) {
				if (json) {
					setLoadedWorkspace(json);
				}
			});
	}

	require(['mod_ejsapp/blockly_conf'], function (BlocklyConf) {
		setLoadedWorkspace = function (json) {
			if (json) {
				workspace.clear();
				if (BlocklyConf.returnEventsBlockly())
					workspaceEvents.clear();
				if (BlocklyConf.returnChartsBlockly())
					workspaceCharts.clear();
				if (functionUseBlockly) workspaceFunctions.clear();

				var vars = JSON.parse(json).vars;
				removeVariablesFromBlockly();
				for (var i in vars)
					addVariable(vars[i]);
				javaScriptsNamesListGeneral = JSON.parse(json).javaScriptsNamesListGeneral;
				javaScriptsNamesListEvents = JSON.parse(json).javaScriptsNamesListEvents;
				jsCodesGeneral = JSON.parse(json).jsCodesGeneral;
				jsCodesEvents = JSON.parse(json).jsCodesEvents;
				visualJSEvents = JSON.parse(json).visualJSEvents;
				visualJSGeneral = JSON.parse(json).visualJSGeneral;

				for (var i = 0; i < experimentsList.length; i++) {
					removeScript(experimentsList[i].name);
				}
				experimentsList = [];
				for (var i = 0; i < chartsList.length; i++) {
					removeScript(chartsList[i].name);
				}
				chartsList = [];
				for (var i = 0; i < eventsList.length; i++) {
					removeScript(eventsList[i].name);
				}
				eventsList = [];
				for (var i = 0; i < functionsList.length; i++) {
					for (var j = 0; j < functionsList[i].length; j++) {
						removeScript(functionsList[i][j].name);
					}
				}
				functionsList = [];
				for (var i = 0; i < BlocklyConf.returnFunctionToReplace().length; i++) {
					functionsList.push([]);
				}

				var saveExp = JSON.parse(json).experiments;
				for (var i = 0; i < saveExp.length; i++) {
					/*var code = (new DOMParser()).parseFromString(saveExp[i].code, "text/xml");
                    experimentsList.push({"name":saveExp[i].name,"code":code});*/
					addnewScript(1, saveExp[i].name, saveExp[i].code, 'experimentsScripts', 'experimentSelection', experimentsList);
				}
				var saveCharts = JSON.parse(json).charts;
				for (var i = 0; i < saveCharts.length; i++) {
					/*var code = (new DOMParser()).parseFromString(saveCharts[i].code, "text/xml");
                    chartsList.push({"name":saveCharts[i].name,"code":code});*/
					addnewScript(2, saveCharts[i].name, saveCharts[i].code, 'chartsScripts', 'chartSelection', chartsList);
				}
				var saveEvents = JSON.parse(json).events;
				for (var i = 0; i < saveEvents.length; i++) {
					/*var code = (new DOMParser()).parseFromString(saveEvents[i].code, "text/xml");
                    eventsList.push({"name":saveExp[i].name,"code":code});*/
					addnewScript(3, saveEvents[i].name, saveEvents[i].code, 'eventsScripts', 'eventSelection', eventsList);
				}
				var saveFunctions = JSON.parse(json).functions;
				for (var i = 0; i < saveFunctions.length; i++) {
					for (var j = 0; j < saveFunctions[i].length; j++) {
						/*var code = (new DOMParser()).parseFromString(saveEvents[i].code, "text/xml");
                        eventsList.push({"name":saveExp[i].name,"code":code});*/
						addnewScript(4, saveFunctions[i][j].name, saveFunctions[i][j].code, 'functionsScripts' + i, 'controllerSelection', functionsList[i], i);
					}
				}


				document.getElementById("_javaScriptFrame").style.visibility = "hidden";
			}
		}
	});

	function saveCSV(num){
		var csvContent = "data:text/csv;charset=utf-8\n";
		if (num === 0){ // Charts
			for(var i = 0; i<chartArray.length; i++){
				if (document.getElementById(chartArray[i].fragment).style.display !== "none") {
					var arrayDatos = chartArray[i].chart.data.datasets;
					csvContent = csvContent + "CHART " + chartArray[i].name + "\n";
					for (var j = 0; j < arrayDatos.length; j++) {
						var linea = arrayDatos[j].data;
						csvContent = csvContent + chartInfo[i][0].name + " " + chartInfo[i][0].value + ", " + chartInfo[i][1].name + " " + chartInfo[i][1].value + "\n";
						for (var k = 0; k < linea.length; k++) {
							csvContent = csvContent + linea[k].x + ", " + linea[k].y + "\n";
						}
					}
					break;
				}
			}
		} else if (num === 1){ /* Data */
			if(recordedVariables.names.length<0)
				return;
			for(var r in recordedVariables.names){
				csvContent = csvContent + "DATA\n";
				csvContent = csvContent + recordedVariables.names[r][0]+ ", " +recordedVariables.names[r][1]+ "\n";
				for(var j in recordedVariables.datas[r]){
					csvContent = csvContent + recordedVariables.datas[r][j][0]+ ", " +recordedVariables.datas[r][j][1]+ "\n";
				}
			}
		}
		_model.saveText(null, 'txt', csvContent);
	}

	function saveImg(moodle_upload_file) {
		for(var i = 0; i<chartArray.length; i++) {
			if (document.getElementById(chartArray[i].fragment).style.display !== "none") {
				var canvas = document.getElementById('myChart' + chartArray[i].fragment.slice(-1));
				var data_url = canvas.toDataURL();
				EJSS_INTERFACE.BoxPanel.showInputDialog("Choose a name for the file", function (name) {
					sendSnapshot(data_url, name, moodle_upload_file);
				});
				break;
			}
		}
	}

	function sendSnapshot(data_url, user_file, moodle_upload_file) {
		var http = new XMLHttpRequest();
		var params = "user_file="+user_file+"&file="+encodeURIComponent(data_url)+"&type=png"+"&context_id="+_model.getContextID()+
			"&user_id="+_model.getUserID()+"&ejsapp_id="+_model.getActivityID();
		http.open("POST", moodle_upload_file, true);

		/* Send the proper header information along with the request */
		http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		http.send(params);
	}

	/* </SAVE & LOAD FILES> */

	function removeVariablesFromBlockly(){
		for(var v in blocklyVariablesList){
			var elem = blocklyVariablesList[v];
			if (!newImplement) {
				removeFromArray(elem,keys_number);
				removeFromArray(elem,keys_boolean);
			}
			else{
				removeFromArray(elem,keys_number_input);
				removeFromArray(elem,keys_number_output);
				removeFromArray(elem,keys_boolean_input);
				removeFromArray(elem,keys_boolean_output);
			}
		}
		blocklyVariablesList = [];
	}

	function removeFromArray(elem, array){
		for(var i=0;i<array.length;i++){
			if(array[i][0]===elem){
				array.splice(i, 1);
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

	/* CONTROLLER */
	var STRIP_COMMENTS = /((\/\/.*$)|(\/\*[\s\S]*?\*\/))/mg;
	var ARGUMENT_NAMES = /([^\s,]+)/g;
	function getInfoFromFunctionName(func) {
		func = getValueModel(func);
		var fnStr = func.toString().replace(STRIP_COMMENTS, '');
		var result = fnStr.slice(fnStr.indexOf('(')+1, fnStr.indexOf(')')).match(ARGUMENT_NAMES);
		var parm=[];
		/*result = result.toString().replace(/,/g, ' , ');*/
		if(result === null) result = [];
		else{
			parm = result;
			result = result.toString().replace(/,/g, ' , ');
		}
		var dondeReturn = fnStr.indexOf('return');
		if(dondeReturn===-1)
			return [result,'',parm];
		else
			return [result,fnStr.substring(dondeReturn+7, fnStr.lastIndexOf(';')),parm];
	}

