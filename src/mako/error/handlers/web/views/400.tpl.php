{% extends:'mako-error::error' %}

{% block:'title' %}Bad Request{% endblock %}

{% block:'message' %}
	<h1>400</h1>
	<p>The server was unable to process the request.</p>
{% endblock %}
