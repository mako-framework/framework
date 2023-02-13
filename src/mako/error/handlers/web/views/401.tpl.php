{% extends:'mako-error::error' %}

{% block:'title' %}Unauthorized{% endblock %}

{% block:'message' %}
	<h1>401</h1>
	<p>You don't have permission to access the requested resource.</p>
{% endblock %}
