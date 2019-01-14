{% extends:'mako-error::error' %}

{% block:title %}Forbidden{% endblock %}

{% block:message %}
	<h1>403</h1>
	<p>You don't have permission to access the requested resource.</p>
{% endblock %}
