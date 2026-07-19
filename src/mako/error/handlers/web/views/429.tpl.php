{% extends:'mako-error::error' %}

{% block:'title' %}Too Many Requests{% endblock %}

<?php

use mako\http\exceptions\TooManyRequestsException;

if (isset($_exception_) && $_exception_ instanceof TooManyRequestsException) {
	/** @var TooManyRequestsException $_exception_ */
	$retryAfter = $_exception_->getRetryAfter();

	if ($retryAfter !== null) {
		$retryAfter = $retryAfter->format('Y-m-d\TH:i:sP');
	}
}

?>

{% block:'message' %}
	<h1>429</h1>
	<p>
		You have made too many requests to the server.
		<span id="retryAt" hidden aria-hidden="true">
			<i>You can retry at <span id="retryDate" data-date="{{$retryAfter, default: ''}}"></span></i>.
		</span>
	</p>
	<script type="text/javascript">
		const retryDate = document.getElementById('retryDate');
		const retryDateString = retryDate.dataset.date;

		if (retryDateString) {
			retryDate.textContent = (new Date(retryDateString)).toLocaleString();

			const retryAt = document.getElementById('retryAt');
			retryAt.hidden = false;
			retryAt.removeAttribute('aria-hidden');
		}
	</script>
{% endblock %}
