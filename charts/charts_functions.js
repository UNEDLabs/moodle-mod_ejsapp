var tabs = $("#container-1").tabs();
var record = false;
var timers =new Array();
var chartArray = new Array(); 
var titlesArray = new Array();
var count_chart = 0;
var chartNumber = 0;
var chartInfo = new Array();
	
function addTab(textName){
	var ul = tabs.find( "ul" );
    var current_idx = ul.find("li").length + 1;
    //$("<li><a href='#fragment-" + current_idx + "'>"+textName+"</a><span id='close' onclick='removeTab("+(current_idx-1)+");'>x</span></li>" ).appendTo( ul );
	$("<li><a href='#fragment-" + current_idx + "'>"+textName+"</a></li>" ).appendTo( ul );
    tabs.append("<div id='fragment-" + current_idx + "'><div style='max-height: 600px; width: 600px;'><canvas id='myChart"+current_idx+"'></canvas></div></div>");
    tabs.tabs("refresh");
    tabs.tabs("select", 0);
	return "myChart"+current_idx;
}

/*function removeTab(index){
	alert(index);
	tabs.tabs('destroy').tabs();
	tabs.tabs('remove', index);
	chartInfo.splice(index, 1);
	timers.splice(index, 1);
	chartArray.splice(index, 1);
	titlesArray.splice(index, 1);
	count_chart--;
	chartNumber--;
}*/


function createChart(textName,time,chartNumber){
	var exists = -1;
	for(var n in titlesArray){
		if(textName===titlesArray[n]){
			exists = n;
			break;
		}
	}
	if(exists==-1){
		initChart(addTab(textName),textName,chartNumber,time);
	}
	else{
		toCSV(chartArray[0].data.datasets[0].data);
		addtoChart(chartNumber,exists,time);
	}
}

function addtoChart(chartNumber,exists,time){
	var lengthData = chartArray[exists].data.datasets.length;
	for(var i=1;i<chartInfo[chartNumber].length;i++){
		var ejey = chartInfo[chartNumber][i];
		var dataSet = {
			fill: false,
			borderColor: "rgba("+randomScalingFactor()+","+randomScalingFactor()+","+randomScalingFactor()+",1)",
			label:  ejey["name"],
			data: []
		};
		chartArray[exists].data.datasets.push(dataSet);
	}
	chartArray[exists].update();
	var inter=setInterval(getData, time,exists,chartNumber,lengthData);
	timers.push(inter);
}

function initChart(place,textName,chartNumber,time){
	titlesArray.push(textName);
	var ctx = document.getElementById(place).getContext('2d');
	var ejex = chartInfo[chartNumber][0];
	var config = {
		type: 'line',
		data: {},
		options: {
			responsive: true,
			animation: false,
			
			
			
			
			title:{
				display:true,
				text:textName
			},
			tooltips: {
				mode: 'index',
			},
			hover: {
				mode: 'index'
			},
			scales: {
				xAxes: [{
					type: 'linear',
					position: 'bottom',
					scaleLabel: {
						display: true,
						labelString: ejex["name"]
					}
				}],
				yAxes: [{
					display: true,
					scaleLabel: {
						display: true,
						labelString: 'Value'
					}
				}]
			}
		}
	};
	chart = new Chart(ctx, config);
	for(var i=1;i<chartInfo[chartNumber].length;i++){
		var ejey = chartInfo[chartNumber][i];
		var dataSet = {
			fill: false,
			borderColor: "rgba("+randomScalingFactor()+","+randomScalingFactor()+","+randomScalingFactor()+",1)",
			label:  ejey["name"],
			data: []
		};
		chart.data.datasets.push(dataSet);
		chart.update();
	}
	chartArray.push(chart);

	var inter=setInterval(getData, time,chartArray.length-1,chartNumber,0);
	timers.push(inter);
}

var getData = function (number,inforNumber,dataSetNumber){
	if(record){
		var x =  eval(chartInfo[inforNumber][0]["value"]);
		for(var i=1;i<chartInfo[inforNumber].length;i++){
			var val = chartInfo[inforNumber][i];
			var y = eval(val["value"]);
			chartArray[number].data.datasets[i-1+dataSetNumber].data[chartArray[number].data.datasets[i-1+dataSetNumber].data.length]={x,y};
			if((chartInfo[inforNumber][0]["checkBox"])){
				if(i==1)
					chartInfo[inforNumber][0]["number"] = chartInfo[inforNumber][0]["number"] -1;
				if(chartInfo[inforNumber][0]["number"]<0)
					chartArray[number].data.datasets[i-1+dataSetNumber].data.splice(0, 1); // remove first data point
			}
		}
		chartArray[number].update();
	}
}
	


var randomScalingFactor = function() {
        return Math.round(Math.random()*255) + 1
};

function rec(bool){
	record = bool;
	if(!bool){
		for(var i in timers){
			clearInterval(timers[i]);
		}
		timers=[];
	}
}

/**
 * Exports and triggers download of chart data as CSV file
 */
function toCSV(data) {
	var csvContent;
	csvContent = "data:text/csv;charset=utf-8\n";
	for(var i=0;i<data.length;i++)
	{
		var elemento = data[i];
		csvContent = csvContent+elemento.x+", "+elemento.y+"\n";
	}
	
	var encodedUri = encodeURI(csvContent);
	window.open(encodedUri);
}

function cleanCharts(){
	var ul = tabs.find( "ul" );
	for(var i = ul.find("li").length-1; i>=0;i--){
		tabs.tabs('destroy').tabs();
		tabs.tabs('remove', i);
	}
	
	chartInfo = new Array();
	tabs = $("#container-1").tabs();
    record = false;
	timers =new Array();
	chartArray = new Array(); 
	titlesArray = new Array();
	count_chart = 0;
	chartNumber = 0;
	
}

  
  
	
	