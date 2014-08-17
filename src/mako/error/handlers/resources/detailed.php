<!DOCTYPE html>
<html lang="en">
	
	<head>
		<title>Error</title>

		<style>
			body, html
			{
				padding: 0;
				margin: 0;
			}
			body
			{
				font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
			}
			a
			{
				color: #BC1025;
				cursor: pointer;
			}
			a:hover
			{
				text-decoration: underline;
			}
			table
			{
				width: 100%;
				border: 1px solid #ccc;
				background: #fff;
				border-collapse: collapse;
				margin-top: 10px;
			}
			table th
			{
				padding: 4px;
				background: #ddd;
				border-bottom: 1px solid #ccc;
				text-align: left;
			}
			table td
			{
				padding: 4px;
				border-bottom: 1px solid #ccc;
				border-right: 1px solid #ccc;
				vertical-align: middle;
			}
			table td
			{
				white-space: pre-wrap;
				word-wrap: break-word;
				word-break: break-all;
			}
			.header
			{
				background: #BC1025;
				padding: 10px;
				color: #fff;
				text-shadow: 1px 1px 0 #5E0806;
			}
			.header h2
			{
				color: #ddd;
			}
			.details
			{
				padding: 10px;
			}
			.panel
			{
				background: #eee;
				padding: 10px;
			}
			.panel .frame
			{
				padding-bottom: 10px;
				margin-bottom: 10px;
				border-bottom: 1px solid #ccc;
			}
			.panel .frame .file
			{
				color: #888;
			}
			.panel .frame .line
			{
				
			}
			.panel .frame .class
			{
				font-style: italic;
			}
			.panel .frame .function
			{
				font-style: oblique;
			}
			.panel .frame:last-child
			{
				padding-bottom: 0;
				margin-bottom: 0;
				border-bottom: 0;
			}
			.panel .source pre
			{
				color: #fff;
				background: #333;
				overflow: auto;
				word-wrap: normal;
				padding: 10px;
				border: 1px solid #ccc;
			}
			.panel .source pre span.code-line
			{
				width: 60px;
				color: #888;
				display: inline-block;
			}
			.panel .source pre div
			{
				margin: 0;
				padding: 4px;
			}
			.panel .source pre div.highlight
			{
				color: #fff;
				background: #BC1025;
			}
		</style>
	</head>

	<body>

		<div class="header">
			<h1><?= $type ?> ( <?= $code ?> )</h1>

			<?php if(!empty($message)): ?>

				<h2><?= htmlspecialchars($message, ENT_QUOTES, $charset) ?></h2>

			<?php endif; ?>
		</div>

		<div class="details">

			<h2>Stack trace</h2>

			<div class="panel">

				<?php foreach($trace as $key => $frame): ?>

					<div class="frame">

						<?= (count($trace) - $key) ?>.

						<?php if(!empty($frame['file'])): ?>

							<span class="file"><?= $frame['file'] ?></span>

						<?php else: ?>

							<span class="file">&lt;#UNKNOWN&gt;</span>

						<?php endif; ?>

						<?php if(!empty($frame['line'])): ?>

							: <span class="line"><a class="toggle-code" title="Toggle source"><?= $frame['line'] ?></a></span>

						<?php else: ?>

							<span class="line">: 0</span>

						<?php endif; ?>

						<?php if(!empty($frame['class'])): ?>

							<span class="class"><?= $frame['class'] ?></span>

						<?php endif; ?>

						<?php if(!empty($frame['type'])): ?>

							<span class="type"><?= $frame['type'] ?></span>

						<?php endif; ?>

						<?php if(!empty($frame['function'])): ?>

							<span class="function"><?= $frame['function'] ?>(<?php if(!empty($frame['args'])): ?><a class="toggle-table" title="Toggle parameters">...</a><?php endif; ?>)</span>

							<?php if(!empty($frame['args'])): ?>

								<table>

									<tr>
										<th>#</th>
										<th>Parameter</th>
									</tr>

									<?php foreach($frame['args'] as $key => $argument): ?>

										<tr>
											<td><?= $key + 1 ?></td>
											<td><pre><?= $argument ?></pre></td>
										</tr>

									<?php endforeach; ?>

								</table>

							<?php endif; ?>

						<?php endif; ?>

						<?php if(!empty($frame['source'])): ?>

							<div class="source">

<code><pre><?php $startLine = $frame['line'] - $frame['source_padding']; ?>
<?php foreach($frame['source'] as $key => $code): ?><div<?php if($startLine + $key === $frame['line']): ?> class="highlight"<?php endif; ?>><span class="code-line"><?= $startLine + $key; ?></span><?= str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $code); ?></div><?php endforeach; ?></pre></code>

							</div>

						<?php endif; ?>

					</div>

				<?php endforeach; ?>

			</div>

			<h2>Superglobals</h2>

			<div class="panel">

				<?php foreach($superglobals as $name => $superglobal): ?>

					<?php if(!empty($superglobal)): ?>

						<div class="frame">

							<span>$_<?= $name ?> ( <a class="toggle-table">toggle</a> )</span>

							<table>

								<tr>
									<th>Key</th>
									<th>Value</th>
								</tr>

								<?php foreach($superglobal as $key => $value): ?>

									<tr>
										<td><?= $key ?></td>
										<td><?= htmlspecialchars(var_export($value, true), ENT_QUOTES, $charset) ?></pre></td>
									</tr>

								<?php endforeach; ?>

							</table>

						</div>

					<?php endif; ?>

				<?php endforeach; ?>

			</div>

			<h2>Included files</h2>

			<div class="panel">

				<?= count($included_files) ?> files have been included <span class="toggle-table">( <a>toggle</a> )</span>

				<table>

					<tr>
						<th>#</th>
						<th>Path</th>
					</tr>

					<?php foreach($included_files as $key => $file): ?>

						<tr>
							<td><?= $key + 1 ?></td>
							<td><pre><?= htmlspecialchars($file, ENT_QUOTES, $charset) ?></pre></td>
						</tr>

					<?php endforeach; ?>

				</table>

			</div>

		</div>

		<script>

			var tables, codes, links;

			// Hide tables by default

			tables = document.getElementsByTagName('table');

			for(i = 0; i < tables.length; i++)
			{
				tables[i].style.display = 'none';
			}

			// Hide codes by default

			codes = document.getElementsByTagName('code');

			for(i = 0; i < codes.length; i++)
			{
				codes[i].style.display = 'none';
			}

			// Attach onClick events to table togglers

			links = document.getElementsByClassName('toggle-table');

			for(i = 0; i < links.length; i++)
			{
				links[i].onclick = function(e)
				{
					var table = e.target.parentNode.parentNode.getElementsByTagName('table');

					if(table.length !== 1) return;

					table[0].style.display = (table[0].style.display === 'none') ? 'table' : 'none';
				}
			}

			// Attach onClick events to code togglers

			links = document.getElementsByClassName('toggle-code');

			for(i = 0; i < links.length; i++)
			{
				links[i].onclick = function(e)
				{
					var code = e.target.parentNode.parentNode.getElementsByTagName('code');

					if(code.length !== 1) return;

					code[0].style.display = (code[0].style.display === 'none') ? 'block' : 'none';
				}
			}
		</script>

	</body>

</html>