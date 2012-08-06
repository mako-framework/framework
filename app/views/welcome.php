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
#container
{
	width: 980px;
	margin: 0px auto;
	margin-top: 25%;
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

<div id="container">
<h1>Mako Framework</h1>

<?php if(!is_writable(MAKO_APPLICATION . '/storage/logs') || !is_writable(MAKO_APPLICATION . '/storage/logs')): ?>
<p class="animated shake"><span class="error">Make sure that the <strong>application/storage/cache</strong> and <strong>application/storage/cache</strong> directories are writable.</span></p>
<?php endif; ?>

<p>You have successfully installed the framework. Check out the <a href="http://makoframework.com/docs">documentation</a> and create something awesome!</p>
</div>

</body>

</html>