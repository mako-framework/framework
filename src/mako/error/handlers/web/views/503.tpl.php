{% extends:'mako-error::error' %}

{% block:'title' %}Service Unavailable{% endblock %}

{% block:'message' %}
	<h1>503</h1>
	<p>The service is currently unavailable.</p>
{% endblock %}
