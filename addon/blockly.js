document.addEventListener('DOMContentLoaded', function(){
	// When the user clicks on <span> (x), close the modal
	document.getElementsByClassName("close")[0].onclick = function() {
		if(jsOpenType===0)
			jsCodesGeneral[jsOpenedGeneral].code = editorJS.getValue();
		else
			jsCodesEvents[jsOpenedEvents].code = editorJS.getValue();
		document.getElementById('myModal').style.display = "none";
	};

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
		if (event.target == document.getElementById('myModal')) {
			if(jsOpenType===0)
				jsCodesGeneral[jsOpenedGeneral].code = editorJS.getValue();
			else
				jsCodesEvents[jsOpenedEvents].code = editorJS.getValue();
			document.getElementById('myModal').style.display = "none";
		}
	};

	var start = setInterval(function() {
		if (typeof _model !== "undefined") {
			if(!chartsBlockly) {
				document.getElementById('chartsDropdown').style.display = "none";
			}
			if(!eventsBlockly) {
				document.getElementById('eventsDropdown').style.display = "none";
			}
			if(!controllerBlockly) {
				document.getElementById('controllersDropdown').style.display = "none";
			}
			initAux();
			loadVariables();
			preparePage();
			initJSFrame("_javaScriptFrame");

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
			workspace.registerButtonCallback("createVariablePressed", createVariable,"");
			workspace.registerToolboxCategoryCallback('generalVars', generalVars);
			workspace.registerToolboxCategoryCallback('jss', jss);
			//workspace.registerButtonCallback('jsButtonPressed', jsButton);
			//workspace.registerButtonCallback('loadjsButtonPressed', loadjsButton);
			workspace.addChangeListener(myUpdateFunction);
			clearInterval(start);
		}
	}, 200);
}, false);

function initAux(){
	controllerUseBlockly = false;
	if(controllerFunctionLanguage==='blockly')
	{
		controllerUseBlockly = true;
		workspaceControllers = null;
	}
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
	functions ="";
	conditionFixed = [];
	intrp = null;
	record = false;
	recordedVariables={};
	recordedVariables.names = [];
	recordedVariables.datas = [];
	controllerEditor = null;
	experimentsList = [];
	chartsList = [];
	eventsList = [];
	controllersList = [];
	codeOfData = [];
	codeOfEvents = [];
	codeOfControllers = [];
	experimentOpen=-1;
	chartOpen=-1;
	eventOpen=-1;
	controllerOpen=-1;
	errorInterval=null;
	chartId = 0;
	experimentSelected="";
	eventSelected="";
	chartSelected="";
	controllerSelected="";

}

function loadVariables(){
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
			if(!variableExists(k)) modelVariablesList.push(k);
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
				default:
					keys_others.push(dupla);
					break;
			}
		}
	} else if (typeof _model._inputAndPublicParameters !== "undefined") { // NEW IMPLEMENTATION
		newImplement = true;
		var inputAux = _model._inputAndPublicParameters;
		var outputAux = _model._outputAndPublicParameters;
		for (var k in inputAux) {
			if(inputAux[k].includes(':')){
				inputAux[k]= inputAux[k].slice(0, inputAux[k].indexOf(' '));
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
				default:
					keys_others_input.push(dupla);
					break;
			}
		}
		for (k in outputAux) {
			if(outputAux[k].includes(':')){
				outputAux[k]= outputAux[k].slice(0, outputAux[k].indexOf(' '));
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

function preparePage(){
	if(eventsBlockly){
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
		workspaceEvents.registerButtonCallback("createVariablePressed", createVariable,"");
		workspaceEvents.registerToolboxCategoryCallback('controls', controls);
		workspaceEvents.registerToolboxCategoryCallback('jss2', jss2);
		//workspaceEvents.registerButtonCallback('jsButtonPressed2', jsButton2);
		//workspaceEvents.registerButtonCallback('loadjsButtonPressed2', loadjsButton2);
		workspaceEvents.registerToolboxCategoryCallback('generalVars', generalVars);
		workspaceEvents.addChangeListener(checkEventsBlocks);
	}
	if(chartsBlockly){
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
		workspaceCharts.registerButtonCallback("createVariablePressed", createVariable,"");
		workspaceCharts.registerToolboxCategoryCallback('generalVars', generalVars);
		workspaceCharts.addChangeListener(checkChartsBlocks);
	}
	if(controllerBlockly){
		createControllerPanel();
	}
}

function createControllerPanel(){
	if(controllerFunctionLanguage==="blockly"){
		workspaceControllers = Blockly.inject('blocklyDivController', {
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
		workspaceControllers.registerButtonCallback("createVariablePressed", createVariable,"");
		workspaceControllers.registerToolboxCategoryCallback('generalVars', generalVars);
		workspaceControllers.addChangeListener(checkControllersBlocks);
	}
	else{
		controllerEditor = ace.edit("blocklyDivController",{maxLines: 25, minLines: 5});
		controllerEditor.$blockScrolling = Infinity ;
		controllerEditor.setTheme("ace/theme/xcode");
		controllerEditor.getSession().setMode("ace/mode/"+controllerFunctionLanguage);
		document.getElementById('blocklyDivController').addEventListener("focusout", focusOutController);
	}
	var texto = getInfoFromFunctionName(functionToReplace);
	if(texto[1]!=='')
		document.getElementById('ControllerDiv').insertAdjacentHTML('afterbegin',
			'<h4 style="margin-top:0em; text-align=center">You are going to overwrite the function called  ' +
			'<span style="color: green">' + functionToReplace +'</span> which has <span style="color: red"><br>' +
			texto[0] + '</span> as input parameters and returns <span style="color: blue">'+texto[1]+'</span>.</h4>');
	else
		document.getElementById('ControllerDiv').insertAdjacentHTML('afterbegin',
			'<h4 style="margin-top:0em; text-align=center">You are going to overwrite the function called  ' +
			'<span style="color: green">' + functionToReplace +'</span> which has <span style="color: red"><br>' +
			texto[0] + '</span> as input parameters and returns nothing.</h4>');
}

function getCodeFromName(list,name){
	for( var i = 0; i < list.length; i++){
		   if ( list[i].name === name)
			 return [list[i].code,i];
	}
	return null;
}

function variableExists(name){
	for(i in modelVariablesList){
		if(modelVariablesList[i]===name) return true;
	}
	return false;
}

// CALLBACKS

function jsButton(text){
	var result;
	if(typeof(text)!=='string')
		result = prompt("New JavaScript code name:", "");
	else
		result = prompt(text, "");

	if(result!==null){
		if(javaScriptsNamesListGeneral.indexOf(result)===-1)
			addJs(result, "// This is JavaScript named " + result + "\n", jsCodesGeneral, visualJSGeneral,
				workspace, javaScriptsNamesListGeneral);
		else
			jsButton("Forbidden name, please choose a new JavaScript name:");
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
		result = prompt("New JavaScript code name:", "");
	else
		result = prompt(texto, "");

	if(result!==null){
		if(javaScriptsNamesListEvents.indexOf(result)===-1)
			addJs(result, "// This is JavaScript2 named " + result + "\n", jsCodesEvents, visualJSEvents,
				workspaceEvents, javaScriptsNamesListEvents);
		else
			jsButton2("Forbidden name, please choose a new JavaScript name:");
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
		result = prompt("New variable name:", "");
	else
		result = prompt(texto, "");
	if(result!==null){
		if((modelVariablesList.indexOf(result)===-1) && (blocklyVariablesList.indexOf(result)===-1)){
			addVariable(result);
		}
		else{
			createVariable("Forbidden name, please choose a new variable name:");
		}
	}
}

function addVariable(result){
	blocklyVariablesList.push(result);
	if (!newImplement) {
		keys_number.push([result,result]);
		keys_boolean.push([result,result]);
	}
	else{
		keys_number_input.push([result,result]);
		keys_number_output.push([result,result]);
		keys_boolean_input.push([result,result]);
		keys_boolean_output.push([result,result]);
	}
}

function myUpdateFunction(event){
	if (event.type == Blockly.Events.CHANGE || Blockly.Events.CHANGE){
		if(experimentOpen!==-1){
			var xml = Blockly.Xml.workspaceToDom(workspace);
			experimentsList[experimentOpen].code=Blockly.Xml.domToText(xml);
		}
	}
	if (event.element ==="click"){
		if((event.blockId!==null && event.blockId!==undefined)&&(workspace.getBlockById(event.blockId)!==null)){
			if((workspace.getBlockById(event.blockId).type === 'jsUpDown')||( workspace.getBlockById(event.blockId).type === 'jsLeftRight')){
				document.getElementById('myModal').style.display = "block";
				selectJS(0,workspace.getBlockById(event.blockId).getFieldValue("jsOption"));
			}
		}
		else{
			document.getElementById('myModal').style.display = "none";
		}
	 }
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
	if (event.type == Blockly.Events.CHANGE || Blockly.Events.CHANGE){
		 if(eventOpen!==-1){
			 var xml = Blockly.Xml.workspaceToDom(workspaceEvents);
			eventsList[eventOpen].code=Blockly.Xml.domToText(xml);
		 }
	}
}

function checkChartsBlocks(event){
	if (event.type == Blockly.Events.CHANGE || Blockly.Events.CHANGE){
		if(chartOpen!==-1){
			var xml = Blockly.Xml.workspaceToDom(workspaceCharts);
			chartsList[chartOpen].code=Blockly.Xml.domToText(xml);
	 	}
	}
}

function checkControllersBlocks(event){
	if (event.type == Blockly.Events.CHANGE || Blockly.Events.CHANGE){
		if(controllerOpen!==-1){
			if(controllerUseBlockly){
				var xml = Blockly.Xml.workspaceToDom(workspaceControllers);
				controllersList[controllerOpen].code=Blockly.Xml.domToText(xml);
			}
		}
	}
}

function focusOutController(){
	controllersList[controllerOpen].code=controllerEditor.getValue();
}

////////////////////////////////////
// ACE EDITOR FOR JAVASCRIPT CODE //
////////////////////////////////////

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

// INTERFACE

// DRAGGABLE ELEMENTS
function resize(){
	if(experimentOpen!=-1){Blockly.svgResize(workspace);}
	if(chartOpen!=-1){Blockly.svgResize(workspaceCharts);}
	if(eventOpen!=-1){Blockly.svgResize(workspaceEvents);}
	if(controllerOpen!=-1){
		if(controllerFunctionLanguage==="blockly"){Blockly.svgResize(workspaceControllers);}
		else {controllerEditor.resize();}
	}
}


function returning(id){
	document.getElementById(id).style.width = "100%";

	document.getElementById(id).style.zIndex = "1";
	document.getElementById(id).style.position = "";
	document.getElementById("return_"+id).style.display = "none";
	if(id==="ScriptBox"){
		document.getElementById(id).style.height = "";
		resize();
	}
}

// Make the DIV element draggable:
dragElement(document.getElementById("EJsS"));
dragElement(document.getElementById("ChartBox"));
dragElement(document.getElementById("ScriptBox"));

function dragElement(elmnt) {
  var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
  if (document.getElementById(elmnt.id + "header")){
    // if present, the header is where you move the DIV from:
	document.getElementById(elmnt.id + "header").onmousedown = dragMouseDown;}

  function dragMouseDown(e) {
	elmnt.style.width = "auto";
	elmnt.style.zIndex = "1010";
	elmnt.style.position = "absolute";
	elmnt.style.background = "White";
	document.getElementById('return_'+elmnt.id).style.display = "inline-block";
	  if(elmnt.id==="ScriptBox"){
		  document.getElementById(elmnt.id).style.height = "450px";
		  document.getElementById(elmnt.id).style.width = "600px";
		  resize();
	  }
    e = e || window.event;
    e.preventDefault();
    // get the mouse cursor position at startup:
    pos3 = e.clientX;
    pos4 = e.clientY;
    document.onmouseup = closeDragElement;
    // call a function whenever the cursor moves:
    document.onmousemove = elementDrag;
  }



  function elementDrag(e) {
    e = e || window.event;
    e.preventDefault();
    // calculate the new cursor position:
    pos1 = pos3 - e.clientX;
    pos2 = pos4 - e.clientY;
    pos3 = e.clientX;
    pos4 = e.clientY;
    // set the element's new position:
    elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
    elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
  }

  function closeDragElement() {
    // stop moving when mouse button is released:
    document.onmouseup = null;
    document.onmousemove = null;
	elmnt.style.background_color = "";
  }
}

////////////////////////
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

function removeAndCloseScript(id){
	if(experimentOpen!==-1)	{
		if(experimentsList[experimentOpen].name === id) {
			document.getElementById("ScriptBox").style.display="none";
			experimentOpen=-1;
			experimentSelected="";
		}
	}
	else if(chartOpen!==-1) {
		if(chartsList[chartOpen].name === id) {
			document.getElementById("ScriptBox").style.display="none";
			chartOpen=-1;
			chartSelected="";
		}
	}
	else if(eventOpen!==-1) {
		if(eventsList[eventOpen].name === id) {
			document.getElementById("ScriptBox").style.display="none";
			eventOpen=-1;
			eventSelected="";
		}
	}
	else if(controllerOpen!==-1) {
		if(controllersList[controllerOpen].name === id) {
			document.getElementById("ScriptBox").style.display="none";
			controllerOpen=-1;
			controllerSelected="";
		}
	}
	removeScript(id);
}

function removeScript(id){
	document.getElementById(id).remove();
	//document.getElementById(id+"list").remove();
	for(var i = 0; i < experimentsList.length; i++){
	   if ( experimentsList[i].name === id) {
	   	if(experimentSelected===id) {experimentSelected="";}
		 experimentsList.splice(i, 1);
		 return;
	   }
	}

	for(i = 0; i < chartsList.length; i++){
	   if ( chartsList[i].name === id) {
		   if(chartSelected===id) {chartSelected="";}
		 chartsList.splice(i, 1);
		 return;
	   }
	}

	for(i = 0; i < eventsList.length; i++){
	   if ( eventsList[i].name === id) {
		   if(eventSelected===id) {eventSelected="";}
		 eventsList.splice(i, 1);
		 return;
	   }
	}

	for(i = 0; i < controllersList.length; i++){
	   if ( controllersList[i].name === id) {
		   if(controllerSelected===id) {controllerSelected="";}
		 controllersList.splice(i, 1);
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

function addnewScript(num,name,code,id,id2,list){
	var d1 = document.getElementById(id);
	d1.insertAdjacentHTML('beforeend', '<div id="' + name + '" style="display:flex;cursor:pointer;' +
		'justify-content:space-between">' +	'<a onclick="showScript(' + num + ',\'' + name + '\');">' +
		'<i class="fa fa-eye"></i>' + ' ' + name +'</a>' + '<div class="topnav-right">' +
		'<a onclick="removeAndCloseScript(\'' + name + '\')"><i  class="fa fa-times"></i></a>' + '</div></div>');
	list.push({"name":name,"code":code});
	//addLabelToDropDown(id2,name,list,false);
	showScript(num,name);
}

function colorSelection(num,name){
	var elem= document.getElementById(name);
	if(num===1){
		if(experimentSelected!==""){
			var elem2= document.getElementById(experimentSelected);
			elem2.style.color='Black';
			elem2.style.fontWeight='normal';
			if(experimentSelected===name){
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
			if(chartSelected===name){
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
			if(eventSelected===name){
				eventSelected="";
				return;
			}
		}
		elem.style.color='Red';
		elem.style.fontWeight='bold';
		eventSelected=name;
	}
	else{
		if(controllerSelected!==""){
			var elem2= document.getElementById(controllerSelected);
			elem2.style.color='Black';
			elem2.style.fontWeight='normal';
			if(controllerSelected===name){
				controllerSelected="";
				return;
			}
		}
		elem.style.color='Peru';
		elem.style.fontWeight='bold';
		controllerSelected=name;
	}
}

function newScript(num){
	if(num===1){
		var name = prompt("Please enter the experiment script name", "Experiment "+(experimentsList.length+1));
		if (name != null) {
			for( var i = 0; i < experimentsList.length; i++){
				if ( experimentsList[i].name === name) {
					newScript(num);
					printError("Experiment name already used");
					return;
				}
			}
			addnewScript(num,name,'<xml></xml>','experimentsScripts','experimentSelection',experimentsList);
		}
	}
	else if(num===2){
		var name = prompt("Please enter the chart script name", "Chart "+(chartsList.length+1));
		if (name != null) {
			for( var i = 0; i < chartsList.length; i++){
				if ( chartsList[i].name === name) {
					newScript(num);
					printError("Chart name already used");
					return;
				}
			}
			addnewScript(num,name,'<xml></xml>','chartsScripts','chartSelection',chartsList);
		}
	}
	else if(num===3){
		var name = prompt("Please enter the event script name", "Event "+(eventsList.length+1));
		if (name != null) {
			for( var i = 0; i < eventsList.length; i++){
				if ( eventsList[i].name === name) {
					newScript(num);
					printError("Event name already used");
					return;
				}
			}
			addnewScript(num,name,'<xml></xml>','eventsScripts','eventSelection',eventsList);
		}
	}
	else if(num===4){
		var name = prompt("Please enter the controller script name", "Controller "+(controllersList.length+1));
		if (name != null) {
			for( var i = 0; i < controllersList.length; i++){
				if ( controllersList[i].name === name) {
					newScript(num);
					printError("Controller name already used");
					return;
				}
			}
			var message = "";
			if(controllerUseBlockly) message = "<xml></xml>";
			addnewScript(num,name,message,'controllersScripts','controllerSelection',controllersList);

		}
	}
}

function showScript(num,name){
	document.getElementById("titleScriptBox").innerHTML = name;
	document.getElementById("ScriptBox").style.display = "block";
	document.getElementById("blocklyDivExperiments").style.display="none";
	document.getElementById("blocklyDivCharts").style.display="none";
	document.getElementById("blocklyDivEvents").style.display="none";
	document.getElementById("ControllerDiv").style.display="none";
	document.getElementById("blocklyDivController").style.display="none";
	experimentOpen=-1;
	chartOpen=-1;
	eventOpen=-1;
	controllerOpen=-1;

	if(num===1){
		 var result= getCodeFromName(experimentsList,name);
		 code=Blockly.Xml.textToDom(result[0]);
		 document.getElementById("blocklyDivExperiments").style.display="inline-block";
		 Blockly.svgResize(workspace);
		 experimentOpen=result[1];
		 workspace.clear();
		 if(code!=='<xml></xml>'){
			 Blockly.Xml.domToWorkspace(code, workspace);
		 }
	}
	else if	(num===2){
		 var result= getCodeFromName(chartsList,name);
		 code=Blockly.Xml.textToDom(result[0]);
		 document.getElementById("blocklyDivCharts").style.display="inline-block";
		 Blockly.svgResize(workspaceCharts);
		 chartOpen=result[1];
		 workspaceCharts.clear();
		 if(code!=='<xml></xml>'){
			 Blockly.Xml.domToWorkspace(code, workspaceCharts);
		 }
	}
	else if	(num===3){
		 var result= getCodeFromName(eventsList,name);
		 code=Blockly.Xml.textToDom(result[0]);
		 document.getElementById("blocklyDivEvents").style.display="inline-block";
		 Blockly.svgResize(workspaceEvents);
		 eventOpen=result[1];
		 workspaceEvents.clear();
		 if(code!=='<xml></xml>'){
			 Blockly.Xml.domToWorkspace(code, workspaceEvents);
		 }
	}
	else if	(num===4){
		if(controllerUseBlockly){
			var result= getCodeFromName(controllersList,name);
			code=Blockly.Xml.textToDom(result[0]);
			document.getElementById("ControllerDiv").style.display="inline-block";
			document.getElementById("blocklyDivController").style.display="inline-block";
			Blockly.svgResize(workspaceControllers);
			controllerOpen=result[1];
			workspaceControllers.clear();
			if(code!=='<xml></xml>'){
				Blockly.Xml.domToWorkspace(code, workspaceControllers);
			}
		}
		else{
			var result= getCodeFromName(controllersList,name);
			document.getElementById("ControllerDiv").style.display="inline-block";
			document.getElementById("blocklyDivController").style.display="inline-block";
			controllerOpen=result[1];
			controllerEditor.setValue(result[0]);
			controllerEditor.resize();
		}
	}
	colorSelection(num,name);
}

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

/////////////

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
	errorInterval = setInterval(changeError, 2000);
}


function changeError() {
	if(document.getElementById('footer').style.display!=="none") showLog();
	clearInterval(errorInterval);
}

/////////// <SAVE & LOAD FILES>

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
	for(i=0;i<eventsList.length;i++){
		saveEvents.push({"name":eventsList[i].name,"code":eventsList[i].code});
	}
	var saveCharts=[];
	for(i=0;i<chartsList.length;i++){
		saveCharts.push({"name":chartsList[i].name,"code":chartsList[i].code});
	}
	var saveControllers=[];
	for(i=0;i<controllersList.length;i++){
		saveControllers.push({"name":controllersList[i].name,"code":controllersList[i].code});
	}
	return {experiments:saveExp, events:saveEvents, charts:saveCharts, controllers:saveControllers,
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

function setLoadedWorkspace(json){
	if (json) {
		workspace.clear();
		if(eventsBlockly)
			workspaceEvents.clear();
		if(chartsBlockly)
			workspaceCharts.clear();
		if(controllerUseBlockly) workspaceControllers.clear();

		var vars = JSON.parse(json).vars;
		removeVariablesFromBlockly();
		for(var i in vars)
			addVariable(vars[i]);
		javaScriptsNamesListGeneral  = JSON.parse(json).javaScriptsNamesListGeneral;
		javaScriptsNamesListEvents = JSON.parse(json).javaScriptsNamesListEvents;
		jsCodesGeneral = JSON.parse(json).jsCodesGeneral;
		jsCodesEvents = JSON.parse(json).jsCodesEvents;
		visualJSEvents = JSON.parse(json).visualJSEvents;
		visualJSGeneral = JSON.parse(json).visualJSGeneral;

		for(i=0;i<experimentsList.length;i++){
			removeScript(experimentsList[i].name);
		}
		experimentsList=[];
		for(i=0;i<chartsList.length;i++){
			removeScript(chartsList[i].name);
		}
		chartsList=[];
		for(i=0;i<eventsList.length;i++){
			removeScript(eventsList[i].name);
		}
		eventsList=[];
		for(i=0;i<controllersList.length;i++){
			removeScript(controllersList[i].name);
		}
		controllersList=[];

		var saveExp=JSON.parse(json).experiments;
		for(i=0;i<saveExp.length;i++){
			//var code = (new DOMParser()).parseFromString(saveExp[i].code, "text/xml");
			//experimentsList.push({"name":saveExp[i].name,"code":code});
			addnewScript(1,saveExp[i].name,saveExp[i].code,'experimentsScripts','experimentSelection',experimentsList);
		}
		var saveCharts=JSON.parse(json).charts;
		for(i=0;i<saveCharts.length;i++){
			//var code = (new DOMParser()).parseFromString(saveCharts[i].code, "text/xml");
			//chartsList.push({"name":saveCharts[i].name,"code":code});
			addnewScript(2,saveCharts[i].name,saveCharts[i].code,'chartsScripts','chartSelection',chartsList);
		}
		var saveEvents=JSON.parse(json).events;
		for(i=0;i<saveEvents.length;i++){
			//var code = (new DOMParser()).parseFromString(saveEvents[i].code, "text/xml");
			//eventsList.push({"name":saveExp[i].name,"code":code});
			addnewScript(3,saveEvents[i].name,saveEvents[i].code,'eventsScripts','eventSelection',eventsList);
		}
		var saveControllers=JSON.parse(json).controllers;
		for(i=0;i<saveControllers.length;i++){
			//var code = (new DOMParser()).parseFromString(saveEvents[i].code, "text/xml");
			//eventsList.push({"name":saveExp[i].name,"code":code});
			addnewScript(4,saveControllers[i].name,saveControllers[i].code,'controllersScripts','controllerSelection',controllersList);
		}

		//addnewScript(num,name,null,'ControllerScripts','controllerSelection',controllersList);

		//Blockly.Xml.domToWorkspace(xmlDom1, workspace);
		//Blockly.Xml.domToWorkspace(xmlDom2, workspaceEvents);
		//Blockly.Xml.domToWorkspace(xmlDom3, workspaceCharts);
		document.getElementById("_javaScriptFrame").style.visibility = "hidden";
	}
}

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
	} else if (num === 1){ // Data
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
	var canvas = document.getElementById('myChart1');
	var data_url = canvas.toDataURL();
	EJSS_INTERFACE.BoxPanel.showInputDialog("Choose a name for the file", function(name) {
		sendSnapshot(data_url, name, moodle_upload_file);
	});
}

function sendSnapshot(data_url, user_file, moodle_upload_file) {
	var http = new XMLHttpRequest();
	var params = "user_file="+user_file+"&file="+encodeURIComponent(data_url)+"&type=png"+"&context_id="+_model.getContextID()+
		"&user_id="+_model.getUserID()+"&ejsapp_id="+_model.getActivityID();
	http.open("POST", moodle_upload_file, true);

	//Send the proper header information along with the request
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

	http.send(params);
}

/////////// </SAVE & LOAD FILES>

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

/// CONTROLLER
var STRIP_COMMENTS = /((\/\/.*$)|(\/\*[\s\S]*?\*\/))/mg;
var ARGUMENT_NAMES = /([^\s,]+)/g;
function getInfoFromFunctionName(func) {
	func = getValueModel(func);
	var fnStr = func.toString().replace(STRIP_COMMENTS, '');
	var result = fnStr.slice(fnStr.indexOf('(')+1, fnStr.indexOf(')')).match(ARGUMENT_NAMES);
	var parm=[];
	//result = result.toString().replace(/,/g, ' , ');
	if(result === null) result = [];
	else{
		parm = result;
		result = result.toString().replace(/,/g, ' , ');
	}
	var dondeReturn = fnStr.indexOf('return')
	if(dondeReturn===-1)
		return [result,'',parm];
	else
		return [result,fnStr.substring(dondeReturn+7, fnStr.lastIndexOf(';')),parm];
}