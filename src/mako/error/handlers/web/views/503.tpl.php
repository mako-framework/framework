{% extends:'mako-error::error' %}

{% block:title %}503 Service Unavailable{% endblock %}

{% block:message %}
	<h1>503 <small>Service Unavailable</small></h1>
	<hr>
	<p>The service is currently unavailable.</p>
{% endblock %}
