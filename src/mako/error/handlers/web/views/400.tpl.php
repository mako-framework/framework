{% extends:'mako-error::error' %}

{% block:title %}400 Bad Request{% endblock %}

{% block:message %}
	<h1>400 <small>Bad Request</small></h1>
	<hr>
	<p>The server was unable to process the request.</p>
{% endblock %}
