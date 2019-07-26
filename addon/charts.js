/*				CHARTS				*/
var tabs = document.getElementById('slideshow-wrapper');
var actual_chart = 0;
var chartArray = [];
var chartInfo = [];
var intervals = [];

function getChartByID(id){
	var uno = null;
	var dos = null;
	for(var i = 0;i<chartInfo.length;i++){
		if(chartInfo[i][0].idNumber===id){
				uno = chartInfo[i][0];
		}
	}
	for(var i = 0;i<chartArray.length;i++){
		if(chartArray[i].idNumber===id){
				dos = chartArray[i];
		}
	}
	return [uno,dos];
}

function nextChartByID(actual){
	var i = actual;
	for(var i = 0;i<chartArray.length;i++){
		if(chartArray[i].idNumber===actual){
			if(i===(chartArray.length-1))
				return chartArray[0].idNumber
			return chartArray[i+1].idNumber
		}
	}
	return -1;
			
}

function prevChartByID(actual){
	var i = actual;
	for(var i = 0;i<chartArray.length;i++){
		if(chartArray[i].idNumber===actual){
			if(i===0)
				return chartArray[chartArray.length-1].idNumber
			return chartArray[i-1].idNumber
		}
	}
	return -1;
}

function nextChart() {
	hideAllChartsAndFragments();
	actual_chart = nextChartByID(actual_chart);
	var nextChart = getChartByID(actual_chart)[1];
	if(nextChart!==null)
		showChart(document.getElementById(nextChart.fragment));
}

function prevChart() {
	hideAllChartsAndFragments();
	actual_chart= prevChartByID(actual_chart);
	var prevChart = getChartByID(actual_chart)[1];
	if(prevChart!==null)
		showChart(document.getElementById(prevChart.fragment));
}

function paintChart() {
	hideAllChartsAndFragments();
	showChart(document.getElementById("fragment-" + actual_chart));
}

function addTab(textName,id) {
	var iDiv = document.createElement('div');
	iDiv.id = "fragment-" + id;
	var iCanvas = document.createElement('canvas');
	iCanvas.id = 'myChart' + id;
	iDiv.appendChild(iCanvas);
	tabs.appendChild(iDiv);
	return iCanvas.id;
}

function createChart(number) {
	setTimeStep(10);
	var exists = -1;
	var textName = chartInfo[number][0].title;
	var renew = chartInfo[number][0].renew;
	var time = chartInfo[number][0].time;
	var idNumber = chartInfo[number][0].idNumber;
	for (var n = 0; n < chartArray.length; n++) {
		if (textName === chartArray[n].name) {
			exists = chartArray[n].idNumber;
			break;
		}
	}
    if (exists === -1) {
		initChart(number, addTab(textName,idNumber), textName, time,idNumber);
	}
	else if(renew === true){
		clearChartByNumber(exists);
		initChart(number, addTab(textName,idNumber), textName, time,idNumber);
	} 
	else {
		addtoChart(number, exists, time);
	}
	
}

function addtoChart(number, exists, time) {
	var charts = getChartByID(exists);
	var lengthData = charts[1].chart.data.datasets.length;
	for (var i = 1; i < chartInfo[number].length; i++) {
		var ejey = chartInfo[number][i];
		var dataSet = {
			fill: false,
			borderColor: "rgba(" + randomScalingFactor() + "," + randomScalingFactor() + "," + randomScalingFactor() + ",1)",
			label: ejey.name,
			data: []
		};
		charts[1].chart.data.datasets.push(dataSet);
	}
	charts[1].chart.update();
	var c = window.setInterval(getData, time, charts[1], chartInfo[number], lengthData);
	intervals.push(c);
}

function initChart(number, place, textName, time,idNumber) {
	var ctx = document.getElementById(place).getContext('2d');
	var ejex = chartInfo[number][0];
	var config = {
		type: 'line',
		data: {},
		options: {
			responsive: true,
			animation: false,
			title: {
				display: true,
				text: textName
			},
			tooltips: {
				mode: 'index'
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
							labelString: ejex.name
						}
					}
				],
				yAxes: [{
						display: true,
						scaleLabel: {
							display: true
						}
					}
				]
			}
		}
	};
	var chart = new Chart(ctx, config);
	for (var i = 1; i < chartInfo[number].length; i++) {
		var ejey = chartInfo[number][i];
		var dataSet = {
			fill: false,
			borderColor: "rgba(" + randomScalingFactor() + "," + randomScalingFactor() + "," + randomScalingFactor() + ",1)",
			label: ejey.name,
			data: []
		};
		chart.data.datasets.push(dataSet);
		chart.update();
	}
	chartArray.push({"name": textName, "idNumber":idNumber, "timer": null, "chart": chart, "fragment": ("fragment-" + idNumber)});
	actual_chart = idNumber;
	paintChart(); 
	var c = window.setInterval(getData, time, chartArray[chartArray.length - 1], chartInfo[number], 0);
	intervals.push(c);
}

var getData = function(chart, info, dataSetNumber) {
		if (record) {
			var x = calculaExpresion(info[0].value);//Number(eval(info[0].value));
			for (var i = 1; i < info.length; i++) {
				var val = info[i];
				var y = calculaExpresion(val.value);//Number(eval(val.value));
				chart.chart.data.datasets[i - 1 + dataSetNumber].data[chart.chart.data.datasets[i - 1 + dataSetNumber].data.length] = {
					x: x,
					y: y
				};
				if ((info[0].checkBox)) {
					if (i === 1) {
						info[0].number = info[0].number - 1;
					}
					if (info[0].number < 0) {
						chart.chart.data.datasets[i - 1 + dataSetNumber].data.splice(0, 1);
					} // Remove first data point
				}
			}
			chart.chart.update();
		}
};

var randomScalingFactor = function() {
	return Math.round(Math.random() * 255) + 1;
};

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

/**
 * Exports and triggers download of chart data as CSV file
 */
/* function toCSV(data) {
	var csvContent;
	csvContent = "data:text/csv;charset=utf-8\n";
	for (var i = 0; i < data.length; i++) {
		var elemento = data[i];
		csvContent = csvContent + elemento.x + ", " + elemento.y + "\n";
	}

	var encodedUri = encodeURI(csvContent);
	window.open(encodedUri);
}*/

function hideAllCharts() {
	document.getElementById("ChartBox").style.display = "";
	document.getElementById("clean_chart").style.display = "";
    document.getElementById("prev_chart").style.display = "";
	document.getElementById("next_chart").style.display = "";
	
	if (chartArray.length === 1) {
        document.getElementById("prev_chart").style.display = "none";
        document.getElementById("next_chart").style.display = "none";
	}
    if (chartArray.length === 0) {
        document.getElementById("ChartBox").style.display = "none";
		document.getElementById("clean_chart").style.display = "none";
        document.getElementById("prev_chart").style.display = "none";
        document.getElementById("next_chart").style.display = "none";
		actual_chart = chartId;
    }
}


function hideAllChartsAndFragments() {
	if(chartArray.length===0) return;
	for (var k = 0; k < chartArray.length; k++) {
		document.getElementById(chartArray[k].fragment).style.display = "none";
	}
	hideAllCharts();
}

function removeFragment(number){
	var elem = document.getElementById("fragment-"+number);
    elem.parentNode.removeChild(elem);
}

function showChart(elem) {
	elem.style.display = "";
}

function removeChart(number){
	for (var i = 0; i < chartArray.length; i++) 
		if(chartArray[i].idNumber === number) chartArray.splice(i,1);
}

function clearChartByNumber(number) {
	if(chartArray.length > 1) {
		actual_chart= prevChartByID(actual_chart);
		var prevChart = getChartByID(actual_chart)[1];
		hideAllChartsAndFragments();
		removeChart(number);
		if(prevChart!==null)
			showChart(document.getElementById(prevChart.fragment));
		hideAllCharts();
		removeFragment(number);
	} 
	else {
		hideAllChartsAndFragments();
		removeChart(number);
		hideAllCharts();
		removeFragment(number);
	}

}

function cleanCharts() {
    clearChartByNumber(actual_chart);
	//removeFragment(actual_chart);
}