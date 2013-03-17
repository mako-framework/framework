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
		padding:40px;
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
		overflow: auto;
		word-wrap: normal;
		white-space: pre;
		-moz-tab-size: 4;
		-o-tab-size: 4;
		tab-size: 4;
		border-bottom-right-radius: 4px;
		border-bottom-left-radius: 4px;
		margin-top: 0px;
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
		border-top-left-radius: 8px;
		border-top-right-radius: 8px;
		text-shadow: 1px 2px 0px #333;
	}
	#error span.code
	{
		background: #F20117;
		padding: 5px;
		padding-left: 20px;
		padding-right: 20px;
		border-radius: 4px;
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
		border-radius: 4px;
		border-radius: 4px;
		border: 1px solid #ccc;
	}
	.where.code
	{
		border-radius: 0px;
		border-top-left-radius: 4px;
		border-top-right-radius: 4px;
		border-bottom: 0px;
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
	.prettyprint.linenums{box-shadow: inset 50px 0 0 #2D3033, inset 51px 0 0 transparent;}
	ol.linenums{margin: 0 0 0 0px;}
	ol.linenums li {padding-left: 12px;color: #bebec5;line-height: 18px;text-shadow: 0 1px 0 #000;}
	.pln{color:#c5c8c6}@media screen{.str{color:#b5bd68}
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
	.fun{color:#81a2be}}
	pre.prettyprint{background:#1d1f21;border:0 solid #000;padding:10px}
	ol.linenums{color:rgba(255,255,255,0.5);margin-top:0;margin-bottom:0}
</style>

</head>
<body>

<div id="error">
        {{$error['type']}} {% if(isset($error['code'])) %}<span class="code">{{$error['code']}}</span>{% endif %}
</div>

<div class="info">
	<p><strong>{{$error['message']}}</strong></p>

	{% if(!empty($error['source'])) %}
		<div class="where code">{{$error['file']}} on line {{$error['line']}}</div>
		<pre class="prettyprint linenums:{{$error['source']['start']}} linenums">{{$error['source']['code']}}</pre>
	{% else %}
		<div class="where">{{$error['file']}} on line {{$error['line']}}</div>
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
							<td width="5%">{{$k+1}}</td>
							<td width="95%">{{raw:$v}}</td>
						</tr>
						{% endforeach %}
					</table>
					<br>
				{% endif %}

				{% if(!empty($frame['location'])) %}
					<div class="where code">{{$frame['location']['file']}} on line {{$frame['location']['line']}}</div>
					<pre class="prettyprint linenums:{{$frame['location']['source']['start']}} linenums">{{trim($frame['location']['source']['code'])}}</pre>
				{% endif %}
			</div>
		{% endforeach %}
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
					<td width="5%">{{$k}}</td>
					<td width="95%">{{print_r($v, true)}}</td>
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
			<td width="5%">{{$k + 1}}</td>
			<td width="95%">{{$v}}</td>
		</tr>
		{% endforeach %}
	</table>
</div>

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/prettify/r224/prettify.js"></script>

<script>
	prettyPrint();

	$('.hidden').hide();

	$('.group').click(function(e)
	{
		var parent = this;
		var target = '.info' + '#' + $(this).data('target');

		$(target).slideToggle('fast', function()
		{
			if($(target).is(":visible"))
			{
				$(parent).children('span').html('-');
			}
			else
			{
				$(parent).children('span').html('+');
			}
		});
	});
</script>

</body>
</html>