{% extends:'mako-error::error' %}

{% block:'title' %}Range Not Satisfiable{% endblock %}

{% block:'message' %}
	<h1>416</h1>
	<p>The requested range is not satisfiable.</p>
{% endblock %}
