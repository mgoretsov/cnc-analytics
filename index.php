<!doctype html>
<html lang="bg">
<head>
<meta charset="utf-8" />
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Slideshow | Machine Monitoring</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="theme/styles/style.css" />
<script type="text/javascript" src="theme/scripts/loader.js"></script>
<script src="jquery-3.5.1.min.js"></script>
<script src="core.js"></script>
<link href="fontawesome/css/all.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap" rel="stylesheet">
</head>
<body>

<div class="header">
<div class="app_logo left">
<ul class="left">
  <li><a href="javascript:void(0);" id="slideshow-link" onclick="showSlideShow()"><i class="fa-solid fa-eye"></i></a></li>
  <li><a href="javascript:void(0);" id="analysis-link" onclick="showChart('1101-N01-STUDER-S31',1)"><i class="fa-solid fa-chart-pie"></i></a></li>
</ul>
</div>
<div class="main_navigation right">
<ul class="right">
  <li><a href="javascript:void(0);"><i class="fa-solid fa-clock"></i></a></li>
  <li><a href="javascript:void(0);" id="factorydata-link" onclick="showFactoryData()"><i class="fa-solid fa-bars"></i></a></li>
</ul>
</div>
</div>

<div class="content">

<div class="app_form_title">
	<h2>Слайдшоу</h2>
	<div id="main-nav"></div>
</div>

<div class="app_form_scrollable" id="slideshow-tab">

</div>

</div>

</body>
</html>