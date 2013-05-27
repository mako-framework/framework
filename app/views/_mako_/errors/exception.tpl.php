<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="{{MAKO_CHARSET}}">
<title>{{$error['type']}}</title>

<style type="text/css">
	body
	{
		height:100%;
		background:#eee;
		margin:0px;
		height: 100%;
		font-size: 100%;
		color:#333;
		font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
		line-height: 100%;
	}
	table
	{
		border-spacing:0;
		border-collapse: collapse;
		border-color: #ddd;
		border-width: 0 0 1px 1px;
		border-style: solid;
	}
	td
	{
		border-color: #ddd;border-width: 1px 1px 0 0;
		border-style: solid;
		padding: 8px;
	}
	tr:nth-child(even)
	{
		background: #eee;
	}
	pre
	{
		background: #1D1F21;
		color: #ccc;
		padding: 20px;
		overflow: auto;
		word-wrap: normal;
		white-space: pre;
		-moz-tab-size: 4;
		-o-tab-size: 4;
		tab-size: 4;
		border-radius: 5px;
	}
	pre::-webkit-scrollbar
	{
		-webkit-appearance:none !important;
		width:11px !important
	}
	pre::-webkit-scrollbar
	{
		border-radius:8px !important;
		border:2px solid white !important;
		background-color:#ccc !important
	}
	pre::-webkit-scrollbar-thumb
	{
		border-radius:8px !important;
		border:2px solid white !important;
		background-color:rgba(0,0,0,.5) !important
	}
	a
	{
		color:#0088cc;
		text-decoration:none;
	}
	a:hover
	{
		color:#005580;
		text-decoration:underline;
	}
	#error
	{
		background:#cc0a0a;
		padding:40px;
		color:#fff;
		font-size:2.5em;
		font-weight:bold;
		text-shadow: 1px 2px 0px #333;
	}
	#error span.code
	{
		background: #F20117;
		padding: 5px;
		padding-left: 20px;
		padding-right: 20px;
		box-shadow: 1px 2px 0px 0px #75000B;
	}
	.info
	{
		background:#fff;
		padding: 20px;
		padding-left: 40px;
		padding-right: 40px;
	}
	.where
	{
		background:#eee;
		padding:10px;
		border: 1px solid #ccc;
		border-radius: 5px;
	}
	.where a
	{
		color: #555;
	}
	.group
	{
		color: #333;
		background:#ccc;
		padding:20px;
		font-size:1.5em;
	}
	.group:hover
	{
		cursor: pointer;
		background: #bbb;
	}
	.group span.toggle
	{
		float: right;
	}
	.frame
	{
		padding-top: 10px;
		padding-bottom: 10px;
		border-bottom: 1px solid #ccc;
	}
	.frame:first-child
	{
		padding-top: 0px;
	}
	.frame:last-child
	{
		border: none;
		padding-bottom: 0px;
	}
	{% if(mako\Config::get('application.error_handler.syntax_highlighting')) %}
	.prettyprint.linenums{box-shadow: inset 60px 0 0 #2D3033, inset 50px 0 0 transparent;}
	ol.linenums li{padding-left: 10px; color: #bebec5;line-height: 18px;text-shadow: 0 1px 0 #000;}
	.pln{color:#c5c8c6}
	.str{color:#b5bd68}
	.kwd{color:#b294bb}
	.com{color:#969896}
	.typ{color:#81a2be}
	.lit{color:#de935f}
	.pun{color:#c5c8c6}
	.opn{color:#c5c8c6}
	.clo{color:#c5c8c6}
	.tag{color:#c66}
	.atn{color:#de935f}
	.atv{color:#8abeb7}
	.dec{color:#de935f}
	.var{color:#c66}
	.fun{color:#81a2be}
	pre.prettyprint{background:#1d1f21;border:0 solid #000;padding:20px}
	ol.linenums{color:rgba(255,255,255,0.5);margin-top:0;margin-bottom:0}
	{% endif %}
</style>

</head>
<body>

<div id="error">
	{{$error['type']}} {% if(isset($error['code'])) %}<span class="code">{{$error['code']}}</span>{% endif %}
</div>

<div class="info">
	<p><strong>{{$error['message']}}</strong></p>

	<div class="where">
		<a href="{{$open_with($error['file'], $error['line'])}}">{{$error['file']}} on line {{$error['line']}}</a>
	</div>

	{% if(!empty($error['source'])) %}
		<pre class="prettyprint linenums:{{$error['source']['start']}} linenums">{{$error['source']['code']}}</pre>
	{% endif %}
</div>

{% if(!empty($error['backtrace'])) %}
	<div class="group" data-target="backtrace">Backtrace <span class="toggle">+</span></div>

	<div class="info hidden" id="backtrace">
		{% foreach($error['backtrace'] as $key => $frame) %}
			<div class="frame">
				<p><strong>{{$frame['function']}}</strong></p>

				{% if(!empty($frame['arguments'])) %}
					<table width="100%">
						{% foreach($frame['arguments'] as $k => $v) %}
						<tr>
							<td width="20%">{{$k+1}}</td>
							<td width="80%">{{preserve:$v}}</td>
						</tr>
						{% endforeach %}
					</table>
					<br>
				{% endif %}

				{% if(!empty($frame['location'])) %}
					<div class="where">
						<a href="{{$open_with($frame['location']['file'], $frame['location']['line'])}}">{{$frame['location']['file']}} on line {{$frame['location']['line']}}</a>
					</div>
					<pre class="prettyprint linenums:{{$frame['location']['source']['start']}} linenums">{{$frame['location']['source']['code']}}</pre>
				{% endif %}
			</div>
		{% endforeach %}
	</div>
{% endif %}

{% if(!empty($error['queries'])) %}
	<div class="group" data-target="queries">Database queries <span class="toggle">+</span></div>

	<div class="info hidden" id="queries">

		<table width="100%">
			{% foreach($error['queries'] as $query) %}
				<tr>
					<td width="20%">{{round($query['time'], 5)}} seconds</td>
					<td width="80%">{{$query['query']}}</td>
				</tr>
			{% endforeach %}
		</table>

	</div>
{% endif %}

<div class="group" data-target="globals">Superglobals <span class="toggle">+</span></div>

<div class="info hidden" id="globals">
	
	<?php $superglobals = array
	(
		'COOKIE'  => &$_COOKIE,
		'ENV'     => &$_ENV,
		'FILES'   => &$_FILES,
		'GET'     => &$_GET,
		'POST'    => &$_POST,
		'SERVER'  => &$_SERVER,
		'SESSION' => &$_SESSION,
	); ?>

	{% foreach($superglobals as $name => $global) %}

		{% if(!empty($global)) %}

			<h2>{{$name}}</h2>

			<table width="100%">
				{% foreach($global as $k => $v) %}
				<tr>
					<td width="20%">{{$k}}</td>
					<td width="80%">{{print_r($v, true)}}</td>
				</tr>
				{% endforeach %}
			</table>

		{% endif %}

	{% endforeach %}

</div>

<div class="group" data-target="files">Included files <span class="toggle">+</span></div>

<div class="info hidden" id="files">
	<table width="100%">
		{% foreach(get_included_files() as $k => $v) %}
		<tr>
			<td width="20%">{{$k + 1}}</td>
			<td width="80%">{{$v}}</td>
		</tr>
		{% endforeach %}
	</table>
</div>

<script>

	// Register onclick events

	var groups = document.getElementsByClassName('group');

	for(var i = 0; i < groups.length; i++)
	{
		groups[i].onclick = function(e)
		{
			var group = document.getElementById(e.target.getAttribute('data-target'));

			e.target.getElementsByTagName('span')[0].innerHTML = (group.style.display === 'none' ? '-' : '+');

			group.style.display = (group.style.display === 'none' ? 'block' : 'none');
		};
	}

	// Hide groups

	var hidden = document.getElementsByClassName('hidden');

	for(var i = 0; i < hidden.length; i++)
	{
		hidden[i].style.display = 'none';
	}

</script>

{% if(mako\Config::get('application.error_handler.syntax_highlighting')) %}
<script src="//cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.js"></script>
<script>prettyPrint();</script>
{% endif %}

</body>
</html>