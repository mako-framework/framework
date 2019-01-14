{% extends:'mako-error::error' %}

{% block:title %}404 Not Found{% endblock %}

{% block:message %}
	<h1>404 <small>Not Found</small></h1>
	<hr>
	<p>The resource you requested could not be found. It may have been moved or deleted.</p>
{% endblock %}
