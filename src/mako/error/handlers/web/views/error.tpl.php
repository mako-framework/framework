<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="{{$__charset__ }}">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>{{block:'title'}}Error{{endblock}}</title>
		<style type="text/css">
		body {
			background: #EFEFEF;
			color: #333;
			padding: 0;
			margin: 0;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			font-size: 100%;
			line-height: 100%;
		}
		.container {
			position: absolute;
			height: 100%;
			width: 100%;
			display: flex;
			align-items: center;
			justify-content: center;
		}
	 	.container h1 {
			padding: 0;
			margin: 0;
			width: auto;
			display: inline-block;
			font-size: 6em;
			line-height: 1em;
			border-bottom: 4px #2DB28A solid
		}
		.container .message {
			padding: 2em;
		}
		.container .message p {
			font-size: 1.1em;
		}
		.exception_id {
			color: #BBB;
			font-size: .9em;
			padding: 1em;
			position: absolute;
			bottom: 0;
			right: 0;
		}
		@media (prefers-color-scheme: dark) {
			body {
				background: #222;
				color: #EDEDED;
			}
			.exception_id {
				color: #444;
			}
		}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="message">
				{{block:'message'}}
					<h1>Error</h1>
					<p>An error has occurred while processing your request.</p>
				{{endblock}}
			</div>
		</div>
		<div class="exception_id">{{$exception_id}}</div>
	</body>
</html>
