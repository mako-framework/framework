{% extends:'mako-error::error' %}

{% block:title %}405 Method Not Allowed{% endblock %}

{% block:message %}
	<h1>405 <small>Method Not Allowed</small></h1>
	<hr>
	<p>The request method that was used is not supported by this resource.</p>
{% endblock %}
