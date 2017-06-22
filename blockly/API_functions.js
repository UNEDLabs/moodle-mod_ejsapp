 	  var checkedValue;
  
  var functions;
  var events =[{}];
  var fixedStatements = [];
  var num_events = 0;
  var arrayColumn = [1];

 
  var conditionFixed =[]
 
	
	function playStop(number){
		_model.addEvent(number+"-t","_model.pause()","");
		_model._play();
	}
	
	function evaluate(code){
		eval(code);
	}
	
function makeEvalContext (declarations)
	{
		eval(declarations);
		return function (str) { eval(str); }
	}
	
	
function initialize(){
	  _model.initialize();
  }
	
function reInitLab(){
	_model.removeEvents();
	for(var i in conditionFixed)
		  conditionFixed[i] = false;
}
 
  
  function selectEvent(number){
	  for(k in events){
		if(events[k].num == number){
			var cond = events[k].cond;
			var act = events[k].act;
			//_model.addEvent(cond,act);
			_model.getOdes()[0]._addEvent(cond,act,EJSS_ODE_SOLVERS.EVENT_TYPE.CROSSING_EVENT,EJSS_ODE_SOLVERS.EVENT_METHOD.BISECTION,100,1.0e-5,true);
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

  function get(pl){
	  return getValueModel(pl);
  }
  
  function set(p1,p2){
	  setValueModel(p1,p2);
  }
  
  function setValueModel(p1,p2) {
	  aux = {};
	  aux[p1] = p2;
	  _model._userUnserialize(aux);   
  }
  
  /*String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};*/
  
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
  
  function reset(){
	  _model.reset();
  }
  
  var flags;
  function setTimeStep(num){
	interval = true;
	flags=setInterval(changeInterval, num);
  }
  
  function replaceFunction(dropdown_original,text_params,text_newvars,value_name){
	  var statements_code= statem[0].toString();
	  statem.splice(0, 1);
	  var text_vars = "";
	  var array = text_newvars.split(',');
	  for(var i in array){
		  text_vars = text_vars+"var "+array[i]+"; ";
	  }
	  var fill = new Function(text_params, text_vars+statements_code+' return '+value_name+';');
	  setValueModel(dropdown_original,fill);
  }
  
  

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  