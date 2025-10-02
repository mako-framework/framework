<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="{{$__charset__ }}">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>Error</title>
		<style type="text/css">
			body {
				background: repeating-linear-gradient(
					135deg,
					#EEE,
					#EEE 5px,
					#E7E7E7 5px,
					#E7E7E7 10px
				);
				color: #333;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
				font-size: 100%;
				display: flex;
				justify-content: center;
				margin: 0;
				padding: 2rem;
			}
			.center {
				text-align: center;
			}
			.exception {
				background-color: #FFF;
				width: 95%;
				overflow-wrap: break-word;
				border-radius: 8px;
			}
			.exception > .header {
				background-color: #123;
				padding: 1.5rem;
				color: #2DB28A;
				border-top-left-radius: 8px;
				border-top-right-radius: 8px;
				font-size: 2.5rem;
				font-weight: bold;
				display: flex;
				align-items: center;
				justify-content: space-between;
			}
			.exception > .header > div > .pill {
				padding: 4px;
				padding-left: 6px;
				padding-right: 6px;
				border-radius: 6px;
			}
			.exception > .header > div > .pill.mako {
				background-color: #2DB28A;
				color: #FFF;
			}
			.exception > .header > div > .pill.php {
				background-color: #4F5B93;
				color: #FFF;
			}
			.exception > .header > div > .exception_id {
				color: #999;
				font-size: 0.9rem;
				margin-bottom: 0;
			}
			.exception > .header > .environment {
				color: #999;
				font-size: 1rem;
			}
			.exception > .body {
				padding: 2rem;
				border: 1px solid #CCC;
				border-top: none;
				border-bottom-left-radius: 8px;
				border-bottom-right-radius: 8px;
			}
			.exception > .body h1 {
				margin-top: 0;
				padding: 0;
			}
			.exception > .tabs {
				background-color: #F6F6F6;
				border: 1px solid #CCC;
				border-top: none;
				display: flex;
			}
			.exception > .tabs > .tab {
				padding: 1.25rem;
				width: 100%;
				text-align: center;
				font-weight: bold;
				justify-content: space-evenly;
				border-bottom: 4px solid transparent;
				cursor: pointer;
			}
			.exception > .tabs > .tab:hover {
				background-color: #EEE;
			}
			.exception > .tabs > .tab.active {
				border-color: #CCC;
			}
			.exception > .body.details > table {
				width: 100%;
				margin-bottom: 2rem;
				border-spacing: 0;
				border-collapse: separate;
			}
			.exception > .body.details > table tr td {
				padding: 1rem;
			}
			.exception > .body.details > table tr td:first-child {
				width: 30%;
				border-right: 1px solid #CCC;
				text-align: right;
			}
			.exception > .body.details > table tr td:last-child {
				width: 70%;
				text-align: left;
			}
			.exception > .body.details > .frame {
				background-color: #EEE;
				border: 1px solid #CCC;
				border-radius: 8px;
				margin-bottom: .5rem;
			}
			.exception > .body.details > .frame.error {
				margin-bottom: 2rem;
			}
			/*.exception > .body.details > .frame:not(.error):not(:last-child) {
				border-bottom: none;
			}*/
			.exception > .body.details > .frame:not(.error):is(:last-child) {
				margin-bottom: 0;
			}
			.exception > .body.details > .frame > .title {
				padding: 1rem;
				cursor: pointer;
			}
			.exception > .body.details > .frame > .title > .title {
				display: inline-block;
				padding: .25rem;
			}
			.exception > .body.details > .frame > .title > .title > .function {
				color: #2DB28A;
			}
			.exception > .body.details > .frame > .title > .number {
				display: inline-block;
				background-color: #CCC;
				width: 3rem;
				padding: .25rem;
				border-radius: 4px;
				text-align: center;
			}
			.exception > .body.details > .frame > .title > .type {
				display: inline-block;
				background-color: #CCC;
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
				background-color: #FFF;
				color: #666;
				width: 1.3rem;
				padding: .25rem;
				border-radius: 4px;
				text-align: center;
				float: right;
			}
			.exception > .body.details > .frame > .details {
				background-color: #FCFCFC;
				border-top: 1px solid #CCC;
				padding: 1rem;
				border-bottom-left-radius: 8px;
				border-bottom-right-radius: 8px;
			}
			.exception > .body.details > .frame > .details > .location {
				background-color: #525863;
				color: #ABB2BF;
				padding: 1rem;
				border-top-left-radius: 8px;
				border-top-right-radius: 8px;
			}
			.exception > .body.details > .frame > .details > .code {
				background-color: #383E49;
				color: #ABB2BF;
				padding: 1rem;
				border-style: dashed;
				border-color: #525863;
				border-top: none;
				border-bottom-left-radius: 8px;
				border-bottom-right-radius: 8px;
				tab-size: 4;
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
				padding-left: .5rem;
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
			.exception > .body.details > .frame > .details > ol > li:not(:last-child), .exception > .body.details > .frame > .details > ul > li:not(:last-child) {
				border-bottom: 1px solid #CCC;
			}
			@media (prefers-color-scheme: dark) {
				body {
					background: repeating-linear-gradient(
					135deg,
					#222,
					#222 5px,
					#1F1F1F 5px,
					#1F1F1F 10px
				);
					color: #EDEDED;
				}
				.exception {
					background-color: #333;
				}
				.exception > .header {
					border: 1px solid #555;
				}
				.exception > .body {
					border-color: #555;
				}
				.exception > .body.details > table tr td:first-child {
					border-color: #555;
				}
				.exception > .tabs {
					background-color: #3f3f3f;
					border-color: #555;
				}
				.exception > .tabs > .tab:hover {
					background-color: #222;
				}
				.exception > .tabs > .tab.active {
					border-color: #555;
				}
				.exception > .body.details > .frame {
					background-color: #222;
					border: 1px solid #555;
				}
				.exception > .body.details > .frame > .title > .number, .exception > .body.details > .frame > .title > .type {
					background-color: #111;
				}
				.exception > .body.details > .frame > .title > .toggle {
					background-color: #111;
				}
				.exception > .body.details > .frame > .details {
					background-color: #303030;
					border-color: #555;
					padding: 1rem;
				}
				.exception > .body.details > .frame > .details > ol > li:not(:last-child), .exception > .body.details > .frame > .details > ul > li:not(:last-child) {
					border-color: #555;
				}
			}
		</style>
	</head>
	<body>
		<div class="exception">
			<div class="header">
				<div>
					Error
					<p class="exception_id">Exception id: {{$exception_id}}</p>
				</div>
				<div class="environment"><span class="pill mako">Mako: {{\mako\Mako::VERSION}}</span> <span class="pill php">PHP: {{PHP_VERSION}}</span></div>
			</div>
			<div class="body">
				<h1>{{$type}} {% if(!empty($code)) %}({{$code}}){% endif %}</h1>
				<p>{{rtrim($message, '.')}}.</p>
				{% if(!empty($previous)) %}
					<h2>Previous Exceptions</h2>
					<ol>
						{% foreach($previous as $exception) %}
							<li>
								<em>{{$exception['type']}}{% if(!empty($exception['code'])) %} ({{$exception['code']}}){% endif %}</em> in {{$exception['file']}} on line {{$exception['line']}}.
								{% if(!empty($exception['message'])) %}<small><p>{{rtrim($exception['message'], '.')}}.</p></small>{% endif %}
							</li>
						{% endforeach %}
					</ol>
				{% endif %}
			</div>
			<div class="tabs">
				<div class="tab active" data-target="stack-trace">Stack Trace</div>
				<div class="tab" data-target="environment">Environment</div>
				{% if($queries !== null) %}<div class="tab" data-target="database">Database</div>{% endif %}
			</div>
			<div id="stack-trace" class="body details" data-open="true">
				{% foreach($trace as $key => $frame) %}
					<div class="frame{% if($frame['is_error']) %} error{% endif %}">
						<div class="title">
							<span class="toggle" aria-hidden="true">{{raw:$frame['open'] ? '&#x25BC;' : '&#x25B2;'}}</span>
							<span class="number">{{$key}}</span>
							<span class="type {{$frame['is_error'] ? 'error' : ($frame['is_internal'] ? 'internal' : ($frame['is_app'] ? 'app' : 'vendor'))}}">{{$frame['is_error'] ? 'Error' : ($frame['is_internal'] ? 'Internal' : ($frame['is_app'] ? 'App' : 'Vendor'))}}</span>
							<span class="title">{{$frame['class'], default: ''}}{{$frame['type'], default: ''}}{% if(isset($frame['function'])) %}<span class="function">{{$frame['function']}}()</span>{% endif %}</span>
						</div>
						<div class="details" data-open="{{$frame['open'] ? 'true' : 'false'}}">
							{% if($frame['is_internal']) %}
								<p>[internal]</p>
							{% else %}
								{% if($frame['code'] === null) %}
									<p><b>Location:</b> {{$frame['file']}} on line {{$frame['line']}}.</p>
								{% else %}
									<div class="location"><b>Location:</b> {{$frame['file']}} on line {{$frame['line']}}.</div>
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
			<div id="environment" class="body details" data-open="false">
				<table>
					<tr>
						<td>OS</td>
						<td>{{php_uname()}}</td>
					</tr>
					<tr>
						<td>Server time</td>
						<td>{{(new \DateTime)->format('Y-m-d H:i:s T')}}</td>
					</tr>
					<tr>
						<td>Mako environment</td>
						<td>{{\mako\env('MAKO_ENV', 'default')}}</td>
					</tr>
				</table>
				{% foreach($superglobals as $name => $values) %}
					{% if(!empty($values)) %}
						<div class="frame">
								<div class="title">
									<span class="toggle" aria-hidden="true">&#x25B2;</span>
									<span class="title">${{$name}}</span>
								</div>
								<div class="details" data-open="false">
									<ul>
										{% foreach($values as $name => $value) %}
											<li><p>{{$name}} {{raw:$dump($value)}}</p></li>
										{% endforeach %}
									</ul>
								</div>
						</div>
					{% endif %}
				{% endforeach %}
			</div>
			{% if($queries !== null) %}
				<div id="database" class="body details" data-open="false">
					{% if(empty($queries)) %}
						<div class="center">No database connections have been established.</div>
					{% else %}
						{% foreach($queries as $name => $connectionQueries) %}
							<div class="frame">
								<div class="title">
									<span class="toggle" aria-hidden="true">&#x25B2;</span>
									<span class="title">{{$name}} connection ({{count($connectionQueries)}})</span>
								</div>
								<div class="details" data-open="false">
									{% if(empty($connectionQueries)) %}
										<div class="center">No database queries have been logged. <em>Have you enabled the query log?</em></div>
									{% else %}
										<ol>
											{% foreach($connectionQueries as ['query' => $query]) %}
												<li>{{raw:$query}}</li>
											{% endforeach %}
										</ol>
									{% endif %}
								</div>
							</div>
						{% endforeach %}
					{% endif %}
				</div>
			{% endif %}
		</div>
		<script type="text/javascript">
			document.addEventListener("DOMContentLoaded", function() {
				document.querySelectorAll('.details').forEach(function(element) {
					if (element.getAttribute('data-open') !== "true") {
						element.style.display = 'none';
					}
				});

				document.querySelectorAll('.tab').forEach(function(element) {
					element.addEventListener('click', function(event) {
						const targetId = event.target.getAttribute('data-target');

						const targetElement = document.getElementById(targetId);

						document.querySelectorAll('.body.details').forEach(function(element) {
							if(element.id !== targetId) {
								element.style.display = 'none';
							}
						});

						if(targetElement.style.display === 'none') {
							targetElement.style.display = 'block';
						}

						document.querySelectorAll('.tab').forEach(function(tab) {
							tab.classList.remove('active');
						});

						element.classList.add('active');
					});
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
