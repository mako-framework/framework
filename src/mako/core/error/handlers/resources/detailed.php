<!DOCTYPE html>
<html lang="en">
<head>
<title>Error</title>

<style type="text/css">

body
{
	height:100%;
	background:#eee;
	padding:0px;
	margin:0px;
	height: 100%;
	font-size: 100%;
	color:#333;
	font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
	line-height: 100%;
}
a
{
	cursor: pointer;
	color: #841320;
}
a:hover, a:active
{
	color: #CD3F3F;
}
.pull-right
{
	float: right;
}
.transparent
{
	opacity: 0.5
}
.header
{
	background: #cd3f3f;
	color: #fff;
	padding: 20px;
	text-shadow: 0 1px 0px rgba(0, 0, 0, .9);
	border-bottom: 4px solid #841420;
}
.header .error
{
	font-size: 3em; /*  */
	font-weight: bold;
	margin-bottom: 10px;
}
.header hr
{
	background: transparent;
	border: 0;
	border-top: 1px solid #841420;
	border-bottom: 1px solid #E6323C;
	margin-top: 30px;
	margin-bottom: 10px;
}
.header .details
{
	font-size: 1.2em;
}
table
{
	width: 100%;
	color: #333;
	padding: 0;
	margin: 0;
	margin-bottom: 1em;
}
table td:first-child
{
	width: 10%;
	background: #eee;
	text-align: center;
	font-weight: bold;
}
table td:last-child
{
	width: 90%;
}
table td
{
	padding: 4px;
	background: #fff;
	border: 1px solid #999;
	overflow: auto;
}
table td pre
{
	margin: 0;
	padding :0;
}
.frame
{
	padding: 20px;
	background: #fff;
	border-bottom: 1px solid #ccc;
}
.frame:not(.frame-active):hover
{
	background: #F8E6E8;
}
.frame .class, .frame .line
{
	color: #841320;
	font-weight: bold;
}
.frame .type
{
	font-weight: bold;
}
.frame .function
{
	font-weight: bold;
}
.frame .source
{
	padding-left: 10px;
	padding-right: 10px;
	background: #333;
	border: 1px solid #000000;
}
.frame .inspect
{
	float: right;
	background: #eee;
	border: 1px solid #ccc;
	padding: 5px;
	font-weight: bold;
	display: none;
	cursor: pointer;
}
.frame-active
{
	background: #EAC7C9;
}

/**
 * Prettyprint styles
 */

pre { overflow: auto; word-wrap: normal; white-space: pre; -moz-tab-size: 4; -o-tab-size: 4; tab-size: 4; }
pre .str, code .str { color: #BCD42A; }  /* string  */
pre .kwd, code .kwd { color: #4bb1b1;  font-weight: bold; }  /* keyword*/
pre .com, code .com { color: #888; font-weight: bold; } /* comment */
pre .typ, code .typ { color: #ef7c61; }  /* type  */
pre .lit, code .lit { color: #BCD42A; }  /* literal */
pre .pun, code .pun { color: #fff; font-weight: bold;  } /* punctuation  */
pre .pln, code .pln { color: #e9e4e5; }  /* plaintext  */
pre .tag, code .tag { color: #4bb1b1; }  /* html/xml tag  */
pre .htm, code .htm { color: #dda0dd; }  /* html tag */
pre .xsl, code .xsl { color: #d0a0d0; }  /* xslt tag */
pre .atn, code .atn { color: #ef7c61; font-weight: normal;} /* html/xml attribute name */
pre .atv, code .atv { color: #bcd42a; }  /* html/xml attribute value  */
pre .dec, code .dec { color: #606; }  /* decimal  */
pre.prettyprint, code.prettyprint { font-family: 'Source Code Pro', Monaco, Consolas, "Lucida Console", monospace;; background: #333; color: #e9e4e5; font-size: 12px; }
pre.prettyprint { white-space: pre-wrap; }
pre.prettyprint a, code.prettyprint a { text-decoration:none; }
.linenums li { color: #A5A5A5; }
.linenums li.current{ background: rgba(255, 100, 100, .07); padding-top: 4px; padding-left: 1px; }
.linenums li.current.active { background: rgba(255, 100, 100, .17); }

</style>

</head>
<body>

<div class="header">

	<div class="error">
		<?= $type ?> <span class="pull-right transparent"><?= $code ?></span>
	</div>

	<?php if(!empty($message)): ?>

	<hr>

	<div class="details">
		<?= $message ?>
	</div>

	<?php endif; ?>

</div>

<?php foreach($trace as $key => $frame): ?>

<div class="frame <?php if($key === 0): ?>frame-active<?php endif; ?>">

	<div class="inspect">INSPECT</div>

	<?= (count($trace) - $key) ?>.

	<?php if(!empty($frame['class'])): ?>

		<span class="class"><?= $frame['class'] ?></span>

	<?php endif; ?>

	<?php if(!empty($frame['type'])): ?>

		<span class="type"><?= $frame['type'] ?></span>

	<?php endif; ?>

	<?php if(!empty($frame['function'])): ?>

		<span class="function"><?= $frame['function'] ?>()</span>

	<?php endif; ?>

	<?php if(!empty($frame['args'])): ?>

		( <a class="args">arguments</a> )

		<br><br>

		<table>

		<?php foreach($frame['args'] as $key => $argument): ?>

			<tr>
				<td><?= $key + 1 ?></td>
				<td><pre><?= $argument ?></pre></td>
			</tr>

		<?php endforeach; ?>

		</table>

	<?php else: ?>

		<br><br>

	<?php endif; ?>

	<?php if(!empty($frame['file'])): ?>

		<span class="file"><?= $frame['file'] ?></span>

	<?php else: ?>

		<span class="file">&lt;#UNKNOWN&gt;</span>

	<?php endif; ?>

	<?php if(!empty($frame['line'])): ?>

		<span class="line">: <?= $frame['line'] ?></span>

	<?php else: ?>

		<span class="line">: 0</span>

	<?php endif; ?>

	<?php if(!empty($frame['source'])): ?>

		<br><br>

		<div class="source">
			<pre class="code-block prettyprint linenums:<?= ($frame['line'] - $frame['source_padding']) ?>"><?= $frame['source'] ?></pre>
		</div>

	<?php endif; ?>

</div>

<?php endforeach; ?>

 <script src="//cdnjs.cloudflare.com/ajax/libs/prettify/r224/prettify.js"></script>
 <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
 <script>

 	 $(function()
 	 {
 	 	prettyPrint();

 	 	$('table').hide();
 	 	$("div.frame:not(:first) div.source").hide();
 	 	$("div.frame:not(:first) div.inspect").show();

 	 	$("div.frame div.inspect").click(function()
 	 	{
 	 		if(!$(this).parent().hasClass('frame-active'))
 	 		{
 	 			$('div.frame-active div.source').hide();
 	 			$('div.frame-active div.inspect').show().parent().removeClass('frame-active');

 	 			$(this).hide();
 	 			$(this).parent().addClass('frame-active');
 	 			$(this).parent().find('div.source').show();
 	 		}
 	 	});

 	 	$("div.frame a.args").click(function()
 	 	{
 	 		$(this).parent().find('table').toggle();
 	 	});
 	 });
 	
 </script>

</body>
</html>