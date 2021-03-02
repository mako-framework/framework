{% extends:'mako-error::error' %}

{% block:'title' %}Method Not Allowed{% endblock %}

{% block:'message' %}
	<h1>405</h1>
	<p>The request method that was used is not supported by this resource.</p>
{% endblock %}
