<!DOCTYPE html>
<html lang="en">
	
<head>
<meta charset="<?php echo MAKO_CHARSET; ?>">

<title>Welcome!</title>

<style type="text/css" media="all">
body
{
	background: #EEEEEE;
	color: #333333;
	font-family: Arial, Helvetica, "Nimbus Sans", FreeSans, Malayalam, sans-serif;
	font-size: 100%;
	line-height: 100%;
	padding:0px;
	margin:0px;
	text-shadow: 0px 1px 0px #FFF;
}
h1
{
	font-size: 5em;
}
h2
{
	font-size: 1.25em;
	font-weight: normal;
}
a
{
	color: #0350a6;
	text-decoration: none;
}
a:hover
{
	color: #2677df;
}
a.active
{
	color: #444;
}
#welcome
{
	text-align: center;
	width: 900px;
	margin: 0px auto;
	margin-top: 15%;
	background: #DCDCDC;
	padding: 30px;
	border-radius: 4px;
	box-shadow: 0px 3px 3px 1px #AAA;
	-moz-transform:rotate(-3deg);
	-webkit-transform:rotate(-3deg);
	-o-transform:rotate(-3deg);
	-ms-transform:rotate(-3deg);
}
.tape
{
	position: absolute;
	height: 40px;
	width: 200px;
	background-color:#fff;
	opacity:0.6;
	border-left: 1px dashed rgba(0, 0, 0, 0.1);
	border-right: 1px dashed rgba(0, 0, 0, 0.1);
	box-shadow: 0px 0px 1px 0px #888;
	-moz-transform:rotate(30deg);
	-webkit-transform:rotate(30deg);
	-o-transform:rotate(30deg);
	-ms-transform:rotate(30deg);
}
.tape.top
{
	top: 10px;
	right: -60px;
}
.tape.bottom
{
	bottom: 0px;
	left: -70px;
}
#footer
{
	position: absolute;
	bottom: 0px;
	right: 0px;
	padding: 10px;
	text-align: right;
	font-size: 0.8em;
	color: #999;
}
.error
{
	color: #FFF;
	background: #B94A47;
	text-shadow: 0px 1px 0px #000;
	padding: 2px;
}

/**
* Awesome CSS animation by http://daneden.me/animate/
*/

.animated
{
	-webkit-animation-fill-mode: both;
	-moz-animation-fill-mode: both;
	-ms-animation-fill-mode: both;
	-o-animation-fill-mode: both;
	animation-fill-mode: both;
	-webkit-animation: 1s ease;
	-moz-animation: 1s ease;
	-ms-animation: 1s ease;
	-o-animation: 1s ease;
	animation: 1s ease;
}
.animated.hinge
{
	-webkit-animation: 2s ease;
	-moz-animation: 2s ease;
	-ms-animation: 2s ease;
	-o-animation: 2s ease;
	animation: 2s ease;
}
@-webkit-keyframes shake
{
	0%, 100% {-webkit-transform: translateX(0);}
	10%, 30%, 50%, 70%, 90% {-webkit-transform: translateX(-10px);}
	20%, 40%, 60%, 80% {-webkit-transform: translateX(10px);}
}
@-moz-keyframes shake
{
	0%, 100% {-moz-transform: translateX(0);}
	10%, 30%, 50%, 70%, 90% {-moz-transform: translateX(-10px);}
	20%, 40%, 60%, 80% {-moz-transform: translateX(10px);}
}
@-ms-keyframes shake
{
	0%, 100% {-ms-transform: translateX(0);}
	10%, 30%, 50%, 70%, 90% {-ms-transform: translateX(-10px);}
	20%, 40%, 60%, 80% {-ms-transform: translateX(10px);}
}
@-o-keyframes shake
{
	0%, 100% {-o-transform: translateX(0);}
	10%, 30%, 50%, 70%, 90% {-o-transform: translateX(-10px);}
	20%, 40%, 60%, 80% {-o-transform: translateX(10px);}
}
@keyframes shake
{
	0%, 100% {transform: translateX(0);}
	10%, 30%, 50%, 70%, 90% {transform: translateX(-10px);}
	20%, 40%, 60%, 80% {transform: translateX(10px);}
}
.shake
{
	-webkit-animation-name: shake;
	-moz-animation-name: shake;
	-ms-animation-name: shake;
	-o-animation-name: shake;
	animation-name: shake;
}
</style>

</head>

<body>

<div id="welcome">
<div class="tape top"></div>
<div class="tape bottom"></div>
<h1>Mako Framework</h1>

<?php foreach(array('cache', 'database', 'logs', 'sessions', 'templates') as $dir): ?>

<?php if(!is_writable(MAKO_APPLICATION_PATH . '/storage/' . $dir)): ?>
<p class="animated shake"><span class="error">Make sure that the <strong>application/storage/*</strong> directories are writable.</span></p>
<?php break; endif; ?>

<?php endforeach; ?>

<p>You have successfully installed the framework. Check out the <a href="http://makoframework.com/docs">documentation</a> and create something awesome!</p>
</div>

<div id="footer">
page rendered in <?php echo round(microtime(true) - MAKO_START, 4); ?> seconds | <?php echo MAKO_VERSION; ?>
</div>

</body>

</html>