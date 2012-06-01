<script>
var Mako =
{
	toggleDisplay : function(id)
	{
		var element = document.getElementById(id);

		var elements = document.getElementsByClassName('mako-toggle');

		for(var i = 0; i < elements.length; i++)
		{
		    if(element !== elements[i])
		    {
		    	elements[i].style.display = 'none';
		    }
		}

		if(window.getComputedStyle(element).getPropertyValue('display') == 'none')
		{
			element.style.display = 'block';
		}
		else
		{
			element.style.display = 'none';	
		}

		return false;
	}
};
</script>

<style>
#mako-debug
{
	width: 100%;
	position: fixed;
	bottom: 0;
	right:0;
	z-index: 9999;
	color: #fff;
	font-family:"Helvetica Neue",Helvetica,Arial,sans-serif !important;
	font-size: 16px !important;
}
#mako-debug p
{
	margin-top: 1em;
	margin-bottom: 1em;
}
#mako-debug .mako-strong
{
	font-weight: bold;
}
#mako-debug .mako-time
{
	float: right;
	color: #999;
}
#mako-debug .mako-log
{
	color: #fff;
	text-shadow: 0px 1px 0px #000;
}
#mako-debug .mako-notice
{
	background: #999999;
}
#mako-debug .mako-critical
{
    background: #B94A48;
}
#mako-debug .mako-alert
{
    background: #F89406;
}
#mako-debug .mako-emergency
{
    background: #B94A48;
}
#mako-debug .mako-error
{
    background: #B94A48;
}
#mako-debug .mako-warning
{
    background: #F89406;
}
#mako-debug .mako-info
{
    background: #3A87AD;
}
#mako-debug .mako-debug
{
    background: #468847;
}
#mako-debug .mako-title
{
	color: #aaa;
	font-size: 2.0em;
	text-align: center;
	text-shadow: 0px 2px 0px #fff;
}
#mako-debug .mako-empty
{
	margin: 150px auto;
}
#mako-debug .mako-table
{
	width: 100%;
	border: 1px solid #ccc;
	background: #fff;
}
#mako-debug table td
{
	padding: 4px;
	border-bottom: 1px solid #ccc;
}
#mako-debug table td:first-child
{
	width: 20%;
	vertical-align: top;
}
#mako-debug table td:last-child
{
	width: 80%;
	white-space: pre-wrap;
	word-wrap: break-word;
	word-break: break-all;
}
#mako-debug table tr:last-child td
{
	border: 0px;
}
#mako-debug table tr:nth-child(odd)
{
	background: #efefef;
}
#mako-debug table tr th
{
	text-align: left;
	padding: 4px;
}
#mako-debug .mako-toolbar
{
	padding: 12px;
	background: #111;
	background: -webkit-linear-gradient(bottom, #222, #444);
	background: -moz-linear-gradient(bottom, #222, #444);
	background: -ms-linear-gradient(bottom, #222, #444);
	background: -o-linear-gradient(bottom, #222, #444);
	border-top: 1px solid #000;
	font-size: 0.8em;
	text-shadow: 0px 1px 0px #000;
}
#mako-debug .mako-content
{
	display: none;
	height: 400px;
	padding: 12px;
	overflow: auto;
	background: #eee;
	background: rgba(250, 250, 250, 0.95);
	border-top: 2px solid #555;
	color: #222;
	font-size: 0.9em;
	text-shadow: 0px 1px 0px #fff;
}
#mako-debug a.mako-button
{
	padding: 5px;
	background: -webkit-linear-gradient(top, #fff, #ccc);
	background: -moz-linear-gradient(top, #fff, #ccc);
	background: -ms-linear-gradient(top, #fff, #ccc);
	background: -o-linear-gradient(top, #fff, #ccc);
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
	border: 1px solid #000;
	color: #222;
	text-decoration: none;
	text-shadow: 0px 1px 0px #fff;
}
#mako-debug a.mako-button:hover
{
	background: -webkit-linear-gradient(bottom, #fff, #ccc);
	background: -moz-linear-gradient(bottom, #fff, #ccc);
	background: -ms-linear-gradient(bottom, #fff, #ccc);
	background: -o-linear-gradient(bottom, #fff, #ccc);
}
</style>

<div id="mako-debug">

<div class="mako-content mako-toggle" id="mako-queries">
<?php if(empty($queries)): ?>
<div class="mako-empty mako-title">No database queries...</div>
<?php else: ?>
<p><span class="mako-title">DATABASE QUERIES</span></p>
<table class="mako-table">
<tr>
<th>Time</th>
<th>Query</th>
</tr>
<?php foreach($queries as $query): ?>
<tr>
<td><?php echo round($query['time'], 5); ?> seconds</td>
<td><?php echo htmlspecialchars(print_r($query['query'], true), ENT_QUOTES, MAKO_CHARSET); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<div class="mako-content mako-toggle" id="mako-log">
<?php if(empty($logs)): ?>
<div class="mako-empty mako-title">No log entries...</div>
<?php else: ?>
<p><span class="mako-title">LOG ENTRIES</span></p>
<table class="mako-table">
<tr>
<th>Type</th>
<th>Message</th>
</tr>
<?php foreach($logs as $log): ?>
<tr>
<td class="mako-log mako-<?php echo $log['type']; ?>"><?php echo $log['type']; ?></td>
<td><?php echo htmlspecialchars(print_r($log['message'], true), ENT_QUOTES, MAKO_CHARSET); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<div class="mako-content mako-toggle" id="mako-variables">

<?php if(!empty($_COOKIE)): ?>
<p><span class="mako-title">COOKIE</span></p>
<table class="mako-table">
<tr>
<th>Key</th>
<th>Value</th>
</tr>
<?php foreach($_COOKIE as $key => $value): ?>
<tr>
<td><?php echo htmlspecialchars($key, ENT_QUOTES, MAKO_CHARSET); ?></td>
<td><?php echo htmlspecialchars(print_r($value, true), ENT_QUOTES, MAKO_CHARSET); ?></td>
</tr>
<?php endforeach; ?>
</table>
<br>
<?php endif; ?>

<?php if(!empty($_ENV)): ?>
<p><span class="mako-title">ENV</span></p>
<table class="mako-table">
<tr>
<th>Key</th>
<th>Value</th>
</tr>
<?php foreach($_ENV as $key => $value): ?>
<tr>
<td><?php echo htmlspecialchars($key, ENT_QUOTES, MAKO_CHARSET); ?></td>
<td><?php echo htmlspecialchars(print_r($value, true), ENT_QUOTES, MAKO_CHARSET); ?></td>
</tr>
<?php endforeach; ?>
</table>
<br>
<?php endif; ?>

<?php if(!empty($_FILES)): ?>
<p><span class="mako-title">FILES</span></p>
<table class="mako-table">
<tr>
<th>Key</th>
<th>Value</th>
</tr>
<?php foreach($_FILES as $key => $value): ?>
<tr>
<td><?php echo htmlspecialchars($key, ENT_QUOTES, MAKO_CHARSET); ?></td>
<td><?php echo htmlspecialchars(print_r($value, true), ENT_QUOTES, MAKO_CHARSET); ?></td>
</tr>
<?php endforeach; ?>
</table>
<br>
<?php endif; ?>

<?php if(!empty($_GET)): ?>
<p><span class="mako-title">GET</span></p>
<table class="mako-table">
<tr>
<th>Key</th>
<th>Value</th>
</tr>
<?php foreach($_GET as $key => $value): ?>
<tr>
<td><?php echo htmlspecialchars($key, ENT_QUOTES, MAKO_CHARSET); ?></td>
<td><?php echo htmlspecialchars(print_r($value, true), ENT_QUOTES, MAKO_CHARSET); ?></td>
</tr>
<?php endforeach; ?>
</table>
<br>
<?php endif; ?>



<?php if(!empty($_POST)): ?>
<p><span class="mako-title">POST</span></p>
<table class="mako-table">
<tr>
<th>Key</th>
<th>Value</th>
</tr>
<?php foreach($_POST as $key => $value): ?>
<tr>
<td><?php echo htmlspecialchars($key, ENT_QUOTES, MAKO_CHARSET); ?></td>
<td><?php echo htmlspecialchars(print_r($value, true), ENT_QUOTES, MAKO_CHARSET); ?></td>
</tr>
<?php endforeach; ?>
</table>
<br>
<?php endif; ?>

<p><span class="mako-title">SERVER</span></p>
<table class="mako-table">
<tr>
<th>Key</th>
<th>Value</th>
</tr>
<?php foreach($_SERVER as $key => $value): ?>
<tr>
<td><?php echo htmlspecialchars($key, ENT_QUOTES, MAKO_CHARSET); ?></td>
<td><?php echo htmlspecialchars(print_r($value, true), ENT_QUOTES, MAKO_CHARSET); ?></td>
</tr>
<?php endforeach; ?>
</table>

<br>

<?php if(!empty($_SESSION)): ?>
<p><span class="mako-title">SESSION</span></p>
<table class="mako-table">
<tr>
<th>Key</th>
<th>Value</th>
</tr>
<?php foreach($_SESSION as $key => $value): ?>
<tr>
<td><?php echo htmlspecialchars($key, ENT_QUOTES, MAKO_CHARSET); ?></td>
<td><?php echo htmlspecialchars(print_r($value, true), ENT_QUOTES, MAKO_CHARSET); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

</div>

<div class="mako-content mako-toggle" id="mako-files">
<p><span class="mako-title">INCLUDED FILES</span></p>
<table class="mako-table">
<tr>
<th>#</th>
<th>Name</th>
</tr>
<?php foreach(get_included_files() as $key => $value): ?>
<tr>
<td><?php echo $key + 1; ?></td>
<td><?php echo htmlspecialchars($value, ENT_QUOTES, MAKO_CHARSET); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<div class="mako-toolbar">
<div class="mako-time">rendered in <?php echo $time; ?> seconds</div>
<a class="mako-button" href="#" onclick="Mako.toggleDisplay('mako-queries')"><span class="mako-strong"><?php echo count($queries); ?></span> database queries</a>
<a class="mako-button" href="#" onclick="Mako.toggleDisplay('mako-log')"><span class="mako-strong"><?php echo count($logs); ?></span> log entries</a>
<a class="mako-button" href="#" onclick="Mako.toggleDisplay('mako-variables')">superglobals</a>
<a class="mako-button" href="#" onclick="Mako.toggleDisplay('mako-files')">included files</a>
</div>

</div>