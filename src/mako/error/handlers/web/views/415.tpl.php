{% extends:'mako-error::error' %}

{% block:'title' %}Unsupported Media Type{% endblock %}

{% block:'message' %}
	<h1>415</h1>
	<p>The media type is not supported.</p>
{% endblock %}
