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
					#EAEAEA 5px,
					#EAEAEA 10px
				);
				color: #333;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
				font-size: 100%;
				display: flex;
				justify-content: center;
				margin: 0;
				padding: 2rem;
			}
			svg.icon {
				vertical-align: -0.125em;
				width: .9em;
				height: .9em;
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
			}
			.exception > .body.details {
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
				border: 1px solid #525863;
				border-top: none;
				border-bottom-left-radius: 8px;
				border-bottom-right-radius: 8px;
				tab-size: 4;
			}
			.exception > .body.details > .frame > .details > .code > div {
				line-height: 125%;
			}
			.exception > .body.details > .frame > .details > .code > div.highlight {
				margin-right: -1rem;
				margin-left: -1rem;
				padding-left: 1rem;
				padding-right: 1rem;
				padding-top: 2px;
				padding-bottom: 2px;
				background-color: rgba(178, 45, 45, 0.5);
				border: 1px solid rgba(178, 84, 84, 0.5);
				border-left: none;
				border-right: none;
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
						#202020 5px,
						#202020 10px
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
			.hl-keyword {
    			color: #ca9ee6;
			}
			.hl-property {
				color: #8caaee;
			}
			.hl-attribute {
				font-style: italic;
			}
			.hl-type {
				color: #e5c890;
			}
			.hl-generic {
				color: #e78284;
			}
			.hl-value {
				color: #a6d189;
			}
			.hl-literal {
				color: #a6d189;
			}
			.hl-number {
				color: #ef9f76;
			}
			.hl-variable {
				color: #FFF;
			}
			.hl-comment {
				color: #737994;
			}
			.hl-blur {
				filter: blur(2px);
			}
			.hl-strong {
				font-weight: bold;
			}

			.hl-em {
				font-style: italic;
			}
			.hl-addition {
				display: inline-block;
				min-width: 100%;
				background-color: #00FF0022;
			}
			.hl-deletion {
				display: inline-block;
				min-width: 100%;
				background-color: #FF000011;
			}
			.hl-gutter {
				display: inline-block;
				font-size: 0.9em;
				color: #555;
				padding: 0 1ch;
				margin-right: 1ch;
				user-select: none;
			}
			.hl-gutter-addition {
				background-color: #34A853;
				color: #fff;
			}
			.hl-gutter-deletion {
				background-color: #EA4334;
				color: #fff;
			}
		</style>
	</head>
	<body>
		<!-- start icons (see: https://github.com/tabler/tabler-icons) -->
		<svg xmlns="http://www.w3.org/2000/svg" style="display:none">
			<symbol id="icon-warning" viewBox="0 0 24 24" fill="currentColor">
				<path stroke="none" d="M0 0h24v24H0z" fill="none"/>
				<path d="M12 1.67c.955 0 1.845 .467 2.39 1.247l.105 .16l8.114 13.548a2.914 2.914 0 0 1 -2.307 4.363l-.195 .008h-16.225a2.914 2.914 0 0 1 -2.582 -4.2l.099 -.185l8.11 -13.538a2.914 2.914 0 0 1 2.491 -1.403zm.01 13.33l-.127 .007a1 1 0 0 0 0 1.986l.117 .007l.127 -.007a1 1 0 0 0 0 -1.986l-.117 -.007zm-.01 -7a1 1 0 0 0 -.993 .883l-.007 .117v4l.007 .117a1 1 0 0 0 1.986 0l.007 -.117v-4l-.007 -.117a1 1 0 0 0 -.993 -.883z" />
			</symbol>
		</svg>
		<svg xmlns="http://www.w3.org/2000/svg" style="display:none">
			<symbol id="icon-stack" viewBox="0 0 24 24" fill="currentColor">
				<path stroke="none" d="M0 0h24v24H0z" fill="none"/>
				<path d="M20.894 17.553a1 1 0 0 1 -.447 1.341l-8 4a1 1 0 0 1 -.894 0l-8 -4a1 1 0 0 1 .894 -1.788l7.553 3.774l7.554 -3.775a1 1 0 0 1 1.341 .447m0 -4a1 1 0 0 1 -.447 1.341l-8 4a1 1 0 0 1 -.894 0l-8 -4a1 1 0 0 1 .894 -1.788l7.552 3.775l7.554 -3.775a1 1 0 0 1 1.341 .447m0 -4a1 1 0 0 1 -.447 1.341l-8 4a1 1 0 0 1 -.894 0l-8 -4a1 1 0 0 1 .894 -1.788l7.552 3.775l7.554 -3.775a1 1 0 0 1 1.341 .447m-8.887 -8.552q .056 0 .111 .007l.111 .02l.086 .024l.012 .006l.012 .002l.029 .014l.05 .019l.016 .009l.012 .005l8 4a1 1 0 0 1 0 1.788l-8 4a1 1 0 0 1 -.894 0l-8 -4a1 1 0 0 1 0 -1.788l8 -4l.011 -.005l.018 -.01l.078 -.032l.011 -.002l.013 -.006l.086 -.024l.11 -.02l.056 -.005z" />
			</symbol>
		</svg>
		<svg xmlns="http://www.w3.org/2000/svg" style="display:none">
			<symbol id="icon-environment" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path stroke="none" d="M0 0h24v24H0z" fill="none"/>
				<path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" />
				<path d="M3 12m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" />
				<path d="M7 8l0 .01" />
				<path d="M7 16l0 .01" />
				<path d="M11 8h6" />
				<path d="M11 16h6" />
			</symbol>
		</svg>
		<svg xmlns="http://www.w3.org/2000/svg" style="display:none">
			<symbol id="icon-database" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path stroke="none" d="M0 0h24v24H0z" fill="none"/>
				<path d="M12 6m-8 0a8 3 0 1 0 16 0a8 3 0 1 0 -16 0" />
				<path d="M4 6v6a8 3 0 0 0 16 0v-6" />
				<path d="M4 12v6a8 3 0 0 0 16 0v-6" />
			</symbol>
		</svg>
		<svg xmlns="http://www.w3.org/2000/svg" style="display:none">
			<symbol id="icon-file" viewBox="0 0 24 24" fill="currentColor">
				<path stroke="none" d="M0 0h24v24H0z" fill="none"/>
				<path d="M12 2l.117 .007a1 1 0 0 1 .876 .876l.007 .117v4l.005 .15a2 2 0 0 0 1.838 1.844l.157 .006h4l.117 .007a1 1 0 0 1 .876 .876l.007 .117v9a3 3 0 0 1 -2.824 2.995l-.176 .005h-10a3 3 0 0 1 -2.995 -2.824l-.005 -.176v-14a3 3 0 0 1 2.824 -2.995l.176 -.005h5z" /><path d="M19 7h-4l-.001 -4.001z" />
			</symbol>
		</svg>
		<svg xmlns="http://www.w3.org/2000/svg" style="display:none">
			<symbol id="icon-down" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
				<path stroke="none" d="M0 0h24v24H0z" fill="none"/>
				<path d="M4 11l8 3l8 -3" />
			</symbol>
		</svg>
		<svg xmlns="http://www.w3.org/2000/svg" style="display:none">
			<symbol id="icon-up" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
				<path stroke="none" d="M0 0h24v24H0z" fill="none"/>
				<path d="M4 13l8 -3l8 3" />
			</symbol>
		</svg>
		<!-- end icons -->
		<div class="exception">
			<div class="header">
				<div>
					<svg class="icon"><use href="#icon-warning"></use></svg>
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
				<div class="tab active" data-target="stack-trace">
					<svg class="icon"><use href="#icon-stack"></use></svg>
					Stack Trace
				</div>
				<div class="tab" data-target="environment">
					<svg class="icon"><use href="#icon-environment"></use></svg>
					Environment
				</div>
				{% if($queries !== null) %}
				<div class="tab" data-target="database">
					<svg class="icon"><use href="#icon-database"></use></svg>
					Database
				</div>
				{% endif %}
			</div>
			<div id="stack-trace" class="body details" data-open="true">
				{% foreach($trace as $key => $frame) %}
					<div class="frame{% if($frame['is_error']) %} error{% endif %}">
						<div class="title">
							<span class="toggle" aria-hidden="true">
								<svg class="icon"><use href="#icon-{{$frame['open'] ? 'down' : 'up'}}"></use></svg>
							</span>
							<span class="number">{{$key}}</span>
							<span class="type {{$frame['is_error'] ? 'error' : ($frame['is_internal'] ? 'internal' : ($frame['is_app'] ? 'app' : 'vendor'))}}">{{$frame['is_error'] ? 'Error' : ($frame['is_internal'] ? 'Internal' : ($frame['is_app'] ? 'App' : 'Vendor'))}}</span>
							<span class="title">{{$frame['class'], default: ''}}{{$frame['type'], default: ''}}{% if(isset($frame['function'])) %}<span class="function">{{$frame['function']}}()</span>{% endif %}</span>
						</div>
						<div class="details" data-open="{{$frame['open'] ? 'true' : 'false'}}">
							{% if($frame['is_internal']) %}
								<p>[internal]</p>
							{% else %}
								{% if($frame['code'] === null) %}
									<p>
										<svg class="icon"><use href="#icon-file"></use></svg>
										<b>Location:</b> {{$frame['file']}} on line {{$frame['line']}}.
									</p>
								{% else %}
									<div class="location">
										<svg class="icon"><use href="#icon-file"></use></svg>
										<b>Location:</b> {{$frame['file']}} on line {{$frame['line']}}.
									</div>
									<div class="code">
										{% foreach($frame['code'] as $line => $code) %}
											<div class="{{$line === $frame['line'] ? 'highlight' : 'normal'}}">
												<span class="line">{{$line}}</span> <pre><code>{{raw:$code}}</code></pre>
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

						element.querySelector('.toggle > svg > use').setAttribute('href', element.nextElementSibling.style.display === 'none' ? '#icon-up' : '#icon-down');
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
