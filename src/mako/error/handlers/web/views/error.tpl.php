<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="{{$__charset__ }}">
		<title>{{block:title}}Error{{endblock}}</title>
		<style type="text/css">
		body
		{
			background:#eee;
			padding:0px;
			margin:0px;
			height: 100%;
			font-size: 100%;
			color:#333;
			font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
			line-height: 100%;
		}
		h1
		{
			font-size: 4em;
		}
		small
		{
			font-size: 0.7em;
			color: #999;
			font-weight: normal;
		}
		hr
		{
			border:0px;
			border-bottom:1px #ddd solid;
		}
		.container {
			height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.message
		{
			width: 700px;
		}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="message">
				{{block:message}}
					<h1>Error</h1>
					<hr>
					<p>Aw, snap! An error has occurred while processing your request.</p>
				{{endblock}}
			</div>
		</div>
	</body>
</html>
