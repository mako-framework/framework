{% extends:'mako-error::error' %}

{% block:title %}Too Many Requests{% endblock %}

{% block:message %}
	<h1>429</h1>
	<p>You have made too many requests to the server.</p>
{% endblock %}
