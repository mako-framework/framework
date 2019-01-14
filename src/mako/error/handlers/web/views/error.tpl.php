<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="{{$__charset__ }}">
		<title>{{block:title}}Error{{endblock}}</title>
		<style type="text/css">
		body
		{
			background: #FEFEFE;
			color: #333333;
			padding: 0;
			margin: 0;
			height: 100%;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			font-size: 100%;
			line-height: 100%;
		}
		.container
		{
			height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
		}
	 	.container h1
		{
			padding: 0;
			margin: 0;
			width: auto;
			display: inline-block;
			font-size: 6em;
			line-height: 1em;
			border-bottom: 4px #2DB28A solid
		}
		.container .message
		{
			width: 700px;
			max-width: 80%;
			background: #EEEEEE;
			padding: 2em;
			border-radius: 2em;
		}
		.container .message p
		{
			font-size: 1.1em;
		}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="message">
				{{block:message}}
					<h1>Error</h1>
					<p>An error has occurred while processing your request.</p>
				{{endblock}}
			</div>
		</div>
	</body>
</html>
