var EJSS_HARDWARE = EJSS_HARDWARE || {};

EJSS_HARDWARE.orientationTools = function () {
	var self = {};
	var mIOSkind = (getMobileOperatingSystem()!=="Android");
	var mOrientation = 0;

	self.getOrientation = function() { return mOrientation; }
	
	function readOrientation() {
		var orientation;
		if (mIOSkind) { // Android does it otherwise
			if (window.orientation=="90") orientation = 90;
			else if (window.orientation=="0") orientation = 0;
			else if (window.orientation=="-90") orientation = -90;
			else orientation = 180;
		}
		else { // iOS behaviour
			if (window.orientation=="90") orientation = -90;
			else if (window.orientation=="0") orientation = 180;
			else if (window.orientation=="-90") orientation = 90;
			else orientation = 0;
		}
		return orientation;
	}
	
	function getMobileOperatingSystem() {
		var userAgent = navigator.userAgent || navigator.vendor || window.opera;
		if( userAgent.match( /iPad/i ) || userAgent.match( /iPhone/i ) || 
				userAgent.match( /iPod/i ) ) return 'iOS';
		if( userAgent.match( /Android/i ) ) return 'Android';
		return 'unknown';
	}

	window.addEventListener("orientationchange", function() {
		mOrientation  = readOrientation();
  	  }, false);

	mOrientation = readOrientation();

	//alert ("is IOSKind = "+mIOSkind+"\n orientation = "+mOrientation);
	
	return self;
};

EJSS_HARDWARE.accelerometer = function () {
	var self = {};
	var mListeners = [];
	var mOrientationTools = EJSS_HARDWARE.orientationTools();
	var mAverageTime = 0;
	var mIsRunning = false;

	var mData = { x : 0, y:0, z:0, interval:0 , alpha: 0, beta: 0, gamma: 0};
	var mHistory = []; // Stores data taken, most recent data goes to [0] 

	function copyData(acceleration,rotation,data) {
		data.x = acceleration.x;
		data.y = acceleration.y;
		data.z = acceleration.z;
	    data.alpha = rotation.alpha;
	    data.beta  = rotation.beta;
        data.gamma = rotation.gamma;
        data.millisAgo = 0;
        return data;
	}
	
	function deviceMotionHandler(eventData) {
		// Grab the acceleration from the results
		//var acceleration = eventData.acceleration;
		var acceleration = eventData.accelerationIncludingGravity;
	    var rotation = eventData.rotationRate;
		var interval = eventData.interval;
		if (mAverageTime>0) {
	    	for (var j=0, numData=mHistory.length; j<numData; j++) {
	    		var data = mHistory[j];
	    		data.millisAgo += interval;
	    		if (data.millisAgo>mAverageTime) {
	    			mHistory.length = j;
	    			break;
	    		}
	    	}
	    	mHistory.unshift(copyData(acceleration,rotation,{}));
		}

		copyData(acceleration,rotation,mData);

		for (var i=0, n=mListeners.length; i<n; i++) {
			mListeners[i](mData);
		}
	}

	self.isPresent = function() { return (typeof window.DeviceMotionEvent != "undefined"); };

	self.start = function() {
		if (self.isPresent()) {
			window.addEventListener('devicemotion', deviceMotionHandler, false);
			mIsRunning = true;
		}
	};

	self.stop = function() {
		if (self.isPresent()) window.removeEventListener('devicemotion', deviceMotionHandler);
		mHistory = [];
		mData = { x : 0, y:0, z:0, interval:0 , alpha: 0, beta: 0, gamma: 0};
		mIsRunning = false;
	};

	self.isRunning = function() { return mIsRunning; };

	self.setAverageInterval = function (seconds) {
		mAverageTime = seconds;
		if (mAverageTime<=0) mHistory = [];
	};
	
	function averageData() {
		var data = { x : 0, y:0, z:0, interval:0 , alpha: 0, beta: 0, gamma: 0};
	    data.alpha = mData.alpha;
	    data.beta  = mData.beta;
        data.gamma = mData.gamma;
        var n = mHistory.length;
    	for (var i=0; i<n; i++) {
    		var historicalData = mHistory[i];
    		data.x += historicalData.x;
    		data.y += historicalData.y;
    		data.z += historicalData.z;
    	}
    	data.x /= n;
    	data.y /= n;
    	data.z /= n;
    	return data;
	}
	
	self.getDeviceData = function() { return (mAverageTime>0) ? averageData() : mData; };
	
	self.getViewData = function () {
		var data = (mAverageTime>0) ? averageData() : mData;
		switch (mOrientationTools.getOrientation()) {
		  case   0 : return { x: data.x, y: data.y, z: data.z, alpha: data.alpha, beta: data.beta, gamma: data.gamma }; break;
		  case  90 : return { x:-data.y, y: data.x, z: data.z, alpha: data.alpha, beta: data.beta, gamma: data.gamma }; break;
		  case -90 : return { x: data.y, y:-data.x, z: data.z, alpha: data.alpha, beta: data.beta, gamma: data.gamma }; break;
		  default  : return { x:-data.x, y:-data.y, z: data.z, alpha: data.alpha, beta: data.beta, gamma: data.gamma }; break;
	    }
	};

	self.addListener = function(listener) { mListeners.push(listener); };

	self.removeListener = function(listener) {
		var index = mListeners.indexOf(listener);
		if (index>-1) mListeners = mListeners.splice(index,1); 
	};

	return self;

};

EJSS_HARDWARE.SensorTag = {
		ACCELEROMETER : 0,
		GYROSCOPE : 1,
		AMBIENT_TEMPERATURE : 2,
		INFRARED_TEMPERATURE : 3,
		HUMIDITY : 4,
		BAROMETER : 5,
		MAGNETOMETER : 6
};


EJSS_HARDWARE.sensorTag = function () {
	var SensorTag = EJSS_HARDWARE.SensorTag;
	var self = {};

	self.isSupported = function() {
		return window.sensors && window.sensors.isSupportedSensorTag();
	};

	self.start = function(accelerometerPeriod, magnetometerPeriod) {
		if (typeof accelerometerPeriod === "undefined") accelerometerPeriod = 20;
		if (typeof magnetometerPeriod === "undefined") magnetometerPeriod = 20;
		window.sensors.runSensorTag(""+accelerometerPeriod,""+magnetometerPeriod);
	};

	self.stop = function() {
		window.sensors.stopSensorTag();
	};

	self.readData = function(sensor) {
		if (typeof sensor === "undefined") sensor = SensorTag.ACCELEROMETER;
		switch (sensor) {
		default :
		case SensorTag.ACCELEROMETER : return window.sensors.getSensorTagAccelerometer(); break;
		case SensorTag.GYROSCOPE : return window.sensors.getSensorTagGyroscope(); break;
		case SensorTag.AMBIENT_TEMPERATURE : return window.sensors.getSensorTagTempAmb(); break;
		case SensorTag.INFRARED_TEMPERATURE : return window.sensors.getSensorTagTempIR(); break;
		case SensorTag.HUMIDITY : return window.sensors.getSensorTagHumidity();break;
		case SensorTag.BAROMETER : return window.sensors.getSensorTagPressure();break;
		case SensorTag.MAGNETOMETER : return window.sensors.getSensorTagMagnetometer();break;
		}
	};

	return self;

};

EJSS_HARDWARE.linearAccelerometer = function () {
	var self = {};

	self.isPresent = function() {
		return window.sensors && window.sensors.isLinearAcceleration();
	};

	self.start = function() {
		if (self.isPresent()) window.sensors.runLinearAcceleration();
	};

	self.stop = function() {
		if (self.isPresent()) window.sensors.stopLinearAcceleration();
	};

	self.readData = function() {
		if (self.isPresent()) return eval(window.sensors.getLinearAcceleration());
		else return {x:0, y:0, z:0};
	};

	return self;

};

EJSS_HARDWARE.gyroscope = function () {
	var self = {};

	self.isPresent = function() {
		return window.sensors && window.sensors.isGyroscope();
	};

	self.start = function() {
		if (self.isPresent()) window.sensors.runGyroscope();
	};

	self.stop = function() {
		if (self.isPresent()) window.sensors.stopGyroscope();
	};

	self.readData = function() {
		if (self.isPresent()) return eval(window.sensors.getGyroscope());
		else return {x:0, y:0, z:0};
	};

	return self;

};