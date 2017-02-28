	  
	  
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

  function rec(bool){

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
  }
  
  function addEvent(number){
	  selectEvent(number);
  }
  
  function initialize(){
	  _model.initialize();
  }
  
  function reset(){
	  _model.reset();
	  for(var i in conditionFixed)
		  conditionFixed[i] = false;
	  if (typeof _model.removeEvents != 'undefined'){
		_model.removeEvents();
	  }
  }
 
  var flags;
  function setTimeStep(num){
	interval = true;
	flags=setInterval(changeInterval, num);
  }
  
  
  
  
  
  
  
  
  
  
  