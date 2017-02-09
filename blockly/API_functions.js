 	  var checkedValue;
  var vars_to_record = [];
  var old_data;
  var data;
  var functions;
  var events =[{}];
  var fixedStatements = [];
  var num_events = 0;
  var timers =[];
  var data;// = [];
  var arrayColumn = [1];

  var chart; 
  var record = false;
  var dataTables = [];
  var conditionFixed =[]
  var  options = {
					hAxis: {
						title: 'Time'
					},
					vAxis: {
						title: 'Value'
					},
					interpolateNulls: true
				};
				
				
	function getData(number,record2){
		if(record){
			var row = [];
			for(var k in chartInfo[number]){
				var key = k;
				var val = chartInfo[number][k];
					row.push(eval(val["value"]));
			}
			dataTables[number].addRow(row);
			dibuja();
		}
	}
	
	function createChart(name,time,columns){
	/*
	var chartNumber = 0;
	var chartInfo = [];
	*/
		var dataTable = new google.visualization.DataTable();
		for(var k in chartInfo[columns]){
			var key = k;
			var val = chartInfo[columns][k];
			dataTable.addColumn('number', val["name"]);
		}
		 for(var i in chart_names){
			if(String(name)===String(chart_names[i]))
				return;
		 }
		dataTables.push(dataTable);
		var inter=setInterval(getData, time,columns,record);
		timers.push(inter);
		chart_names.push(name.toString());
		addList(name,columns);
	}


	
	function playStop(number){
		_model.addEvent(number+"-t","_model.pause()","");
		_model._play();
	}
	
	
	
	function cleanChart(){
		chart = new google.visualization.LineChart(document.getElementById('chart_div')); 
	}
	
	function dibuja(){
		if(selectedChart==-1){
			var dataTableZero = new google.visualization.DataTable();
			dataTableZero.addColumn('number', "null1");
			dataTableZero.addColumn('number', "");
			chart.draw(dataTableZero, options);
		}
		else{
			chart.draw(dataTables[selectedChart], options);
		}
	}
	
	/*function dibuja(first,indice,tiempo,valor,variable){
	
		if( old_data.getNumberOfColumns()>0)
		{
			indice = indice + old_data.getNumberOfColumns()-1;
		}
		if(first){
			if( old_data.getNumberOfColumns()>0)
			{
				data = old_data.clone();
				if(checkedValue) chart.draw(data, options);
				alert("dibujado primera");
			}
			else
			{
				data = new google.visualization.DataTable();
				alert("sin dibujado primera");
			}
		}
		else{
			var celda = [];
			if(data.getNumberOfColumns()==0)
			{
				data.addColumn('number', 'Time');
				data.addColumn('number', variable);
			}
			if((data.getNumberOfColumns()-1)<=indice)
				data.addColumn('number', variable);
				
			celda.push(tiempo);
			for(var i = 1;i<data.getNumberOfColumns();i++){
				if(i==(indice+1)) celda.push(valor);
				else celda.push(null);
			}
			data.addRow(celda);
			if(checkedValue) chart.draw(data, options);
		}
	}*/

  
  /*function getData(eq,name,k){
		var dato = eval(eq);
		var time = eval("getValueModel(\"t\");");
			 dibuja(false,k,time,dato,name);
		return ;
	  }*/
  
  function rec(bool){
		/*if(bool){
			dibuja(true,-1,-1,-1,"");
			for(var k in vars_to_record){
				alert(vars_to_record.length);
				if(typeof vars_to_record[k].variable !== "undefined") {
					var name = vars_to_record[k].variable;
					var eq = "getValueModel(\""+name+"\");";
					var interval = Number(vars_to_record[k].interval);
					var inter=setInterval(getData, interval,eq,name,Number(k));
					timers.push(inter);
					
					
				}
			}

		}
		else{
			for(var i in timers){
				clearInterval(timers[i]);
			}
			timers=[];
			if(!checkedValue){
				chart.draw(data, options);
			}
		}*/
		record = bool;
		if(!bool){
			for(var i in timers){
				clearInterval(timers[i]);
			}
			timers=[];
		}
	  }
  
  
  
  function recordvar(variable,inter){
	vars_to_record.push({"variable":variable,"interval":inter});
  }
  
  function selectEvent(number){
	  for(k in events){
		if(events[k].num == number){
			var cond = events[k].cond;
			var act = events[k].act;
			_model.addEvent(cond,act);
			return;
		}
	  }
	
  }
  
   function getValueModel(p1) {
  	  var obj = _model._userSerialize();
  	  for(var k in obj){
  		  if(k.localeCompare(p1)==0){
  			  return obj[k];
  		  }
  	  }
  	  return '';              
  }

  function setValueModel(p1,p2) {
	  aux = {};
	  aux[p1] = p2;
	  _model._userUnserialize(aux);   
  }
  
  String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};
  
  function addFixedRelation(number){
	  conditionFixed.push(true);
	  var text2 = fixedStatements[number];
	  var text = "if(conditionFixed["+(conditionFixed.length-1)+"])"+"{"+ text2+"}";
	  _model.addFixedRel(text);
  }
  
  function play(){
	  _model.play();
  }
  
    function pause(){
	  _model.pause();
	  for(var i in conditionFixed)
		  conditionFixed[i] = false;
  }
  
  function addEvent(number){
	  selectEvent(number);
  }
  
  function reset(){
	  _model.reset();
  }
  
  
    // Add an API function for the record() block.
 /* var wrapper = function(text,number) {
        text = text ? text.toString() : '';
        return interpreter.createPrimitive(recordvar(text,number));
      };
      interpreter.setProperty(scope, 'recordvar',
          interpreter.createNativeFunction(wrapper));*/
  
	// Add an API function for the rec() block.
    /*  var wrapper = function(bool) {
		if((bool.toString().localeCompare("true")==0))
        	bool=true;
        else if((bool.toString().localeCompare("false")==0))
        	bool=false;
        return interpreter.createPrimitive(rec(bool));
      };
      interpreter.setProperty(scope, 'rec',
          interpreter.createNativeFunction(wrapper));*/
		  
	 // Add an API function for the createChart() block.
     /* var wrapper = function(text) {
        text = text ? text.toString() : '';
        return interpreter.createPrimitive(buildDocument(text));
      };
      interpreter.setProperty(scope, 'createChart',
          interpreter.createNativeFunction(wrapper));*/
		  
      // Add an API function for the alert() block.
     /* var wrapper = function(text) {
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
          interpreter.createNativeFunction(wrapper));*/
      
   // Add an API function for the addEvent() block.
   /*   var wrapper = function(number) {
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
          interpreter.createNativeFunction(wrapper));*/
      
	  
      // Add an API function for the play block.
    /*  var wrapper = function() {
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
          interpreter.createNativeFunction(wrapper));*/
      
   // Add an API function for the reset block.
    /*  var wrapper = function() {
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
          interpreter.createNativeFunction(wrapper));*/
      
      // Add an API function for highlighting blocks.
   /*   var wrapper = function(id) {
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

    }*/
	
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  