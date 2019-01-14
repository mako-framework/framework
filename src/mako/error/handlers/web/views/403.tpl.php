{% extends:'mako-error::error' %}

{% block:title %}403 Forbidden{% endblock %}

{% block:message %}
	<h1>403 <small>Forbidden</small></h1>
	<hr>
	<p>You don't have permission to access the requested resource.</p>
{% endblock %}
