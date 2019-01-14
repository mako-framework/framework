{% extends:'mako-error::error' %}

{% block:title %}429 Too Many Requests{% endblock %}

{% block:message %}
	<h1>429 <small>Too Many Requests</small></h1>
	<hr>
	<p>You have made too many requests to the server.</p>
{% endblock %}
