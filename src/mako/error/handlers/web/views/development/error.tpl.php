<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="{{$__charset__ }}">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>Error</title>
		<style type="text/css">
			body {
				background-color: #EEEEEE;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
				display: flex;
				justify-content: center;
				margin: 0;
				padding: 2rem;
			}
			.exception {
				background-color: #FFFFFF;
				width: 95%;
				overflow-wrap: break-word;
			}
			.exception > .header {
				background-color: #123;
				padding: 1.5rem;
				color: #2DB28A;
				font-size: 2.5rem;
				font-weight: bold;
				display: flex;
				align-items: center;
				justify-content: space-between;
			}
			.exception > .header > .environment {
				color: #999999;
				font-size: 1rem;
			}
			.exception > .body {
				padding: 2rem;
				border: 1px solid #CCCCCC;
				border-top: none;
			}
			.exception > .body h1 {
				margin-top: 0;
				padding: 0;
			}
			.exception > .tabs {
				background-color: #F6F6F6;
				padding: 2rem;
				border: 1px solid #CCCCCC;
				border-top: none;
			}
			.exception > .body.details > .frame {
				background-color: #EEEEEE;
				border: 1px solid #CCCCCC;
			}
			.exception > .body.details > .frame:not(:last-child) {
				border-bottom: none;
			}
			.exception > .body.details > .frame > .title {
				padding: 1rem;
				cursor: pointer;
			}
			.exception > .body.details > .frame > .title > .function {
				color: #2DB28A;
			}
			.exception > .body.details > .frame > .title > .number {
				display: inline-block;
				background-color: #CCCCCC;
				width: 3rem;
				padding: .25rem;
				border-radius: 4px;
				text-align: center;
			}
			.exception > .body.details > .frame > .title > .type {
				display: inline-block;
				background-color: #CCCCCC;
				width: 4.5rem;
				padding: .25rem;
				border-radius: 4px;
				text-align: center;
			}
			.exception > .body.details > .frame > .title > .type.app {
				background-color: #123;
				color: #2DB28A;
			}
			.exception > .body.details > .frame > .title > .toggle {
				background-color: #FFFFFF;
				color: #666666;
				padding: .25rem;
				border-radius: 4px;
				float: right;
			}
			.exception > .body.details > .frame > .details {
				background-color: #FFFFFF;
				border-top: 1px solid #CCCCCC;
				padding: 1rem;
			}
			.exception > .body.details > .frame > .details > .code {
				background-color: #383E49;
				color: #ABB2BF;
				padding: 1rem;
				margin-top: 1rem;
				border-radius: 8px;
			}
			.exception > .body.details > .frame > .details > .code > div {
				line-height: 125%;
			}
			.exception > .body.details > .frame > .details > .code > div.highlight {
				background-color: rgba(45, 178, 138, 0.5);
			}
			.exception > .body.details > .frame > .details > .code > div > span.line {
				display: inline-block;
				width: 4rem;
			}
			.exception > .body.details > .frame > .details > .code > div > pre {
				display: inline-block;
				margin: 0;
				padding: 0;
			}
			.exception > .body.details > .frame > .details > .code > div > pre > code {
				margin: 0;
				padding: 0;
			}
			.exception > .body.details > .frame > .details > ol > li:not(:last-child) {
				border-bottom: 1px solid #CCCCCC;
			}
		</style>
	</head>
	<body>
		<div class="exception">
			<div class="header">
				<div>Error</div>
				<div class="environment">Mako: {{\mako\Mako::VERSION}}, PHP: {{PHP_VERSION}}</div>
			</div>
			<div class="body">
				<h1>{{$type}} {% if(!empty($code)) %}({{$code}}){% endif %}</h1>

				<p>{{rtrim($message, '.')}}.</p>

				<p><b>Location:</b> {{$file}} on line {{$line}}.</p>
			</div>
			{#<div class="tabs">

			</div>#}
			<div class="body details">
				{% foreach($trace as $key => $frame) %}
					<div class="frame">
						<div class="title">
							<span class="toggle" aria-hidden="true">{{raw:$frame['open'] ? '&#x25BC;' : '&#x25B2;'}}</span>
							<span class="number">{{$key}}</span>
							<span class="type {{$frame['is_internal'] ? 'internal' : ($frame['is_app'] ? 'app' : 'vendor')}}">{{$frame['is_internal'] ? 'Internal' : ($frame['is_app'] ? 'App' : 'Vendor')}}</span>
							{{$frame['class'], default: ''}}{{$frame['type'], default: ''}}<span class="function">{{$frame['function']}}()</span>
						</div>
						<div class="details" data-open="{{$frame['open'] ? 'true' : 'false'}}">
							{% if($frame['is_internal']) %}
								<p>[internal]</p>
							{% else %}
								<p><b>Location:</b> {{$frame['file']}} on line {{$frame['line']}}.</p>
								{% if($frame['code'] !== null) %}
									<div class="code">
										{% foreach($frame['code'] as $line => $code) %}
											<div class="{{$line === $frame['line'] ? 'highlight' : 'normal'}}">
												<span class="line">{{$line}}</span> <pre><code>{{$code}}</code></pre>
											</div>
										{% endforeach %}
									</div>
								{% endif %}
								{% if(!empty($frame['args'])) %}
									<p><b>Arguments:</b></p>
									<ol>
										{% foreach($frame['args'] as $argument) %}
											<li>{{raw:$dump($argument)}}</li>
										{% endforeach %}
									</ol>
								{% endif %}
							{% endif %}
						</div>
					</div>
				{% endforeach %}
			</div>
		</div>
		<script type="text/javascript">
			document.addEventListener("DOMContentLoaded", function() {
				document.querySelectorAll('.frame > .details').forEach(function(element) {
					if (element.getAttribute('data-open') !== "true") {
						element.style.display = 'none';
					}
				});

				document.querySelectorAll('.frame > .title').forEach(function(element) {
					element.addEventListener('click', function() {
						element.nextElementSibling.style.display = element.nextElementSibling.style.display === 'none' ? 'block' : 'none';

						element.querySelector('.toggle').innerHTML = element.nextElementSibling.style.display === 'none' ? '&#x25B2;' : '&#x25BC;';
					});
				});

				document.querySelectorAll('.sf-dump-expanded').forEach(function(element) {
					element.classList.remove('sf-dump-expanded');

					element.classList.add('sf-dump-compact');
				});

				document.querySelectorAll('.sf-dump-toggle > span').forEach(function(element) {
					element.innerHTML = '&#9654;'
				});
			});
		</script>
	</body>
</html>
