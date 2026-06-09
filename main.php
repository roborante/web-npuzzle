<?php 
	require 'php/config.php';
	
	session_start( );
	header("Cache-Control: private");
    header("Pragma: cache");
	updateLoginData( );
	echo 
<<<'HTML'
	<!DOCTYPE html>
	<html>

	<head>
		<title>nPuzzle</title>
		<meta charset="utf-8">
		<meta http-equiv="Cache-control" content="private">
		<link rel="stylesheet" type="text/css" href="layout/style-row.css">
	</head>

	<body id="body" onload="firstCheck( ); newBoard( )" onresize="resize( )">
		<div class="posB" id="topleft">
			<button class="buttons" id="solve" title="automatic solution" onclick="findSolution( )" onfocus="focusOff(this)">S</button>
		</div>
		<div class="settings" id="boardSettings">
			<form>
				<input id="nums" name="8/15" type="checkbox" title="show / hide numbers on tiles" onclick="tileLabs(-1)" onfocus="focusOff(this)"><label class="rightLabel" for="nums">n#</label><br>
				<input id="to15" name="8/15" type="radio"    title="switch to 15 tiles"           onclick="switchTo(16)" onfocus="focusOff(this)"><label class="rightLabel" for="to15">15</label><br>
				<input id="to8"  name="8/15" type="radio"    title="switch to 8 tiles"            onclick="switchTo( 9)" onfocus="focusOff(this)"><label class="rightLabel" for="to8" >&nbsp;8</label>
			</form>
		</div>
		<div id="board">
			<div class="pos" id="pos00"></div>
			<div class="pos" id="pos01"></div>
			<div class="pos" id="pos02"></div>
			<div class="pos" id="pos10"></div>
			<div class="pos" id="pos11"></div>
			<div class="pos" id="pos12"></div>
			<div class="pos" id="pos20"></div>
			<div class="pos" id="pos21"></div>
			<div class="pos" id="pos22"></div>
		</div>
		<div class="settings" id="imageSettings">
			<form>
				<input id="custom" name="img" type="radio" title="play with your custom picture" onclick="upload (                     )" onfocus="focusOff(this)"><label class="leftLabel" for="custom">C</label><br>
				<input id="noImg"  name="img" type="radio" title="play with numbers"             onclick="reStick('layout/tiles/',false)" onfocus="focusOff(this)"><label class="leftLabel" for="noImg" >N</label>
			</form>
			<form class="upload">
				<input id="uploadImg" name="imgFile" type="file" accept="image/jpeg, image/png" value="" onchange="loadImg(this)">
			</form>
		</div>
		<div class="stats" id="bottom">
			<table id="count">
				<tbody>
					<tr><th id="counter"></th></tr>
				</tbody>
			</table>
		</div>
		<div class="posB" id="bottomright">
			<button class="buttons" id="newGame" title="new game" name="layout/tiles/" onclick="shuffle( )" onfocus="focusOff(this)">N</button>
		</div>
	</body>
	
	<script src="../web-nPuzzle/baselang/jquery-3.6.0.min.js"></script>
	<script src="../web-nPuzzle/js_query/animations.jq.js"></script>
	<script src="../web-nPuzzle/js_query/pictures.jq.js"></script>
	<script src="../web-nPuzzle/js_query/layout.jq.js"></script>
	<script src="../web-nPuzzle/js_query/game.jq.js"></script>

	</html>
HTML;
?>
