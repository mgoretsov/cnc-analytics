(function () {
	google.charts.load('current', {packages:['timeline', 'corechart', 'line']});
})();

$( document ).ready(function SlideShow() {
	
	document.getElementById('slideshow-link').classList.add('current');

	var mainNav = document.getElementById('main-nav');
	
	//list
	var list = document.createElement('ul');
	
	//previous button
	var listItemPrev = document.createElement('li');
	var prev = document.createElement('a');
	var prevHref = document.createAttribute('href');
	prevHref.value = 'javascript:void(0);';
	var prevElement = document.createElement('i');
	var prevClass = document.createAttribute('class');
	prevClass.value = 'fa fa-chevron-left';
	prevElement.setAttributeNode(prevClass);
	
	//start/stop button
	var listItemPlay = document.createElement('li');
	var play = document.createElement('a');
	var playHref = document.createAttribute('href');
	playHref.value = 'javascript:void(0);';
	var playElement = document.createElement('i');
	var playClass = document.createAttribute('class');
	playClass.value = 'fa fa-pause';
	playElement.setAttributeNode(playClass);

	//next button
	var listItemNext = document.createElement('li');
	var next = document.createElement('a');
	var nextHref = document.createAttribute('href');
	nextHref.value = 'javascript:void(0);';
	var nextElement = document.createElement('i');
	var nextClass = document.createAttribute('class');
	nextClass.value = 'fa fa-chevron-right';
	nextElement.setAttributeNode(nextClass);

	//prev.appendChild(document.createTextNode('НАЗАД'));
	//play.appendChild(document.createTextNode('ПАУЗА'));
	//next.appendChild(document.createTextNode('НАПРЕД'));

	mainNav.appendChild(list);

	list.appendChild(listItemPrev);
	list.appendChild(listItemPlay);
	list.appendChild(listItemNext);
	
	listItemPrev.appendChild(prev);
	listItemPlay.appendChild(play);
	listItemNext.appendChild(next);
	
	prev.appendChild(prevElement);
	play.appendChild(playElement);
	next.appendChild(nextElement);
	
	/*
	var SlideShowContent = document.createElement('div');
	SlideShowContent.setAttribute('id', 'slideshow-tab');
	var SlideShowContentClass = document.createAttribute('class');
	SlideShowContentClass.value = 'dashboard-content';
	SlideShowContent.setAttributeNode(SlideShowContentClass);
	document.body.appendChild(SlideShowContent);
	*/

	var slides = [0, 1, 2, 3, 4, 5, 6]; //slides
	var i = 0; //current slide
	
	$.ajax({
		url: 'embedded.php?id='+Math.random(),
		type: 'post',
		data: {"funcSelector": "2","workshop": (slides[i])},
		success: function(response) {
		document.getElementById('slideshow-tab').innerHTML = "";
		document.getElementById('slideshow-tab').innerHTML = response;
		}
	});
	
	var loopFunc = function () {
		if (!running) { //skip execution if it is currently paused
			return;
		}
		
		if ((i + 1) > (slides.length - 1)) {
			i = 0;
		} else {
			i++;
		}
		
	$.ajax({
		url: 'embedded.php?id='+Math.random(),
		type: 'post',
		data: {"funcSelector": "2","workshop": (slides[i])},
		success: function(response) {
		document.getElementById('slideshow-tab').innerHTML = "";
		document.getElementById('slideshow-tab').innerHTML = response;
		}
	});
		
	};

	var running = true;
	var loop = setInterval(loopFunc, 7000);

	var prevFunc = function () {
		clearInterval(loop);
		running = false; //pause
		playClass.value = 'fa fa-play';

		if ((i - 1) < 0) {
			i = slides.length - 1;
		} else {
			i--;
		}
		
	$.ajax({
		url: 'embedded.php?id='+Math.random(),
		type: 'post',
		data: {"funcSelector": "2","workshop": (slides[i])},
		success: function(response) {
		document.getElementById('slideshow-tab').innerHTML = "";
		document.getElementById('slideshow-tab').innerHTML = response;
		}
	});
	};
	
	var playFunc = function () {
		if (running) { //pause if it is currently running
			clearInterval(loop);
			running = false;
			playClass.value = 'fa fa-play';
		} else { //play if it is currently paused
			loop = setInterval(loopFunc, 7000);
			running = true;
			playClass.value = 'fa fa-pause';
		}
	};
	
	var nextFunc = function () {
		clearInterval(loop);
		running = false; //pause
		playClass.value = 'fa fa-play';

		if ((i + 1) > (slides.length - 1)) {
			i = 0;
		} else {
			i++;
		}
		
	$.ajax({
		url: 'embedded.php?id='+Math.random(),
		type: 'post',
		data: {"funcSelector": "2","workshop": (slides[i])},
		success: function(response) {
		document.getElementById('slideshow-tab').innerHTML = "";
		document.getElementById('slideshow-tab').innerHTML = response;
		}
	});
	};
	
	prev.onclick = prevFunc;
	play.onclick = playFunc;
	next.onclick = nextFunc;
});

function showSlideShow() {
	if (!!document.getElementById('analysis-tab')) {
	document.getElementById('analysis-tab').remove();
	document.getElementById('analysis-link').classList.remove('current');
	}
	if (!!document.getElementById('factorydata-tab')) {
	document.getElementById('factorydata-tab').remove();
	document.getElementById('factorydata-link').classList.remove('current');
	}
	document.getElementById('slideshow-link').classList.add('current');
};

function showChart(machine, type) {
	
	if (!!document.getElementById('factorydata-tab')) {
		document.getElementById('factorydata-tab').remove();
		document.getElementById('factorydata-link').classList.remove('current');
	}
	
	if (!!document.getElementById('analysis-tab')) {
		return;
	}
	
	//document.getElementById("main-nav").style.display = "none";
	
	document.getElementById('slideshow-link').classList.remove('current');
	document.getElementById('analysis-link').classList.add('current');
	
	var AnalysisContent = document.createElement('div');
	AnalysisContent.setAttribute('id', 'analysis-tab');
	var AnalysisContentClass = document.createAttribute('class');
	AnalysisContentClass.value = 'analysis-content';
	AnalysisContent.setAttributeNode(AnalysisContentClass);
	
	var closeButton = document.createElement('a');
	
	var closeIcon = document.createElement('i');
	var closeIconClass = document.createAttribute('class');
	closeIconClass.value = 'fa fa-times';
	closeIcon.setAttributeNode(closeIconClass);
	
	closeButton.setAttribute('id', 'analysis-closeButton');
	closeButton.appendChild(closeIcon);
	
	var iframe = document.createElement('iframe');
	iframe.setAttribute('src', '/chart.php?machine=' + encodeURIComponent(machine) + '&type=' + encodeURIComponent(type));
	iframe.setAttribute('width', '100%');
	iframe.setAttribute('height', '100%');
	iframe.setAttribute('padding', '0');
	iframe.setAttribute('margin', '0');
	iframe.setAttribute('id', 'analysis-frame');
	
	AnalysisContent.appendChild(iframe);
	
	AnalysisContent.appendChild(closeButton);
	
	document.body.appendChild(AnalysisContent);
	
	closeButton.onclick = function () {
		document.body.removeChild(AnalysisContent);
		document.getElementById('analysis-link').classList.remove('current');
		document.getElementById('slideshow-link').classList.add('current');
	};
};

function showLiveData(which) {
	
	if (!!document.getElementById('ld' +which)) {
	return;
	}

	var closeButton = document.createElement('a');
	
	var closeIcon = document.createElement('i');
	var closeIconClass = document.createAttribute('class');
	closeIconClass.value = 'fa fa-times';
	closeIcon.setAttributeNode(closeIconClass);
	
	closeButton.setAttribute('id', 'livedata-closeButton');
	closeButton.appendChild(closeIcon);

	var LiveDataContainer = document.createElement('div');
	var LiveDataContent = document.createElement('div');
	
	LiveDataContainer.setAttribute('style', 'display:block;position:fixed;top:3%;left:1.5%;width:290px;margin:0;background:#fff;padding:0;border-radius:10px;box-shadow: 0px 12px 27px 3px rgb(0 0 0 / 15%), 0px 6px 4px 0px rgb(0 0 0 / 5%);z-index:11;');
	LiveDataContainer.setAttribute('id', 'ld' +which);
	
	LiveDataContent.setAttribute('style', 'display:block;width:100%;height:100%;');
	LiveDataContent.setAttribute('id', 'LDContent');
	
	LiveDataContainer.appendChild(LiveDataContent);
	LiveDataContainer.appendChild(closeButton);
	
	document.body.appendChild(LiveDataContainer);
	
	$(LiveDataContent).html("<i style=\'display:block;width:100%;color:#4350d6;margin:30px 0;text-align:center;\' class=\'fa fa-refresh fa-spin fa-2x fa-fw\'></i>");
	
	var MCLiveDataPar = which;
	
	function FetchLiveData(MCLiveDataPar) {
	$.ajax({
		url: 'embedded.php?id='+Math.random(),
		type: 'post',
		data: {"funcSelector": "1","mcname": which},
		success: function(response) {
			$(LiveDataContent).html(response);
		},
		complete:function(response){
		setTimeout(FetchLiveData(which),5000);
		}
	});
	};
	
	closeButton.onclick = function () {
		document.body.removeChild(LiveDataContainer);
	};
	
	setTimeout(FetchLiveData(MCLiveDataPar),5000);
};
function showFactoryData() {
	
	if (!!document.getElementById('factorydata-tab')) {
		return;
	}
	
	if (!!document.getElementById('analysis-tab')) {
		document.getElementById('analysis-tab').remove();
	}
	
	document.getElementById('slideshow-link').classList.remove('current');
	document.getElementById('analysis-link').classList.remove('current');
	document.getElementById('factorydata-link').classList.add('current');
	
	var FactoryDataContent = document.createElement('div');
	FactoryDataContent.setAttribute('id', 'factorydata-tab');
	var FactoryDataContentClass = document.createAttribute('class');
	FactoryDataContentClass.value = 'factorydata-content';
	FactoryDataContent.setAttributeNode(FactoryDataContentClass);

	var closeButton = document.createElement('a');
	
	closeButton.setAttribute('id', 'factorydata-closeButton');
	closeButton.appendChild(document.createTextNode('x'));
	
	document.body.appendChild(FactoryDataContent);
	FactoryDataContent.innerHTML = '<i class="fa fa-code" style="display:block;text-align:center;font-size:62px;" aria-hidden="true"></i><h2>В процес на разработка.</h2>';
	FactoryDataContent.appendChild(closeButton);
	
	closeButton.onclick = function () {
		
	if (!!document.getElementById('analysis-tab')) {
		document.body.removeChild(FactoryDataContent);
		document.getElementById('factorydata-link').classList.remove('current');
		document.getElementById('analysis-link').classList.add('current');
		return;
	}
	
	document.body.removeChild(FactoryDataContent);
	document.getElementById('factorydata-link').classList.remove('current');
	document.getElementById('slideshow-link').classList.add('current');
	};
	
};