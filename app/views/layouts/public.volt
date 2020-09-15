<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  {% if refreshInterval is defined %}<meta http-equiv="refresh" content="{{ refreshInterval }}">{% endif %}

  <title>{% block title %}{% if pageTitle is defined %}{{ pageTitle }} - {% endif %}Great Circle Solar{% endblock %}</title>

  {{ stylesheet_link("/css/w3.css") }}
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
  <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">

  <style>
    html,body {font-family: -apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif }
    #toast {
        height:auto;
        position:absolute;
        right:20px;
        bottom:20px;
        color: #F0F0F0;
        font-family: Calibri;
        font-size: 20px;
        padding:10px;
        text-align:center;
        border-radius: 2px;
        -webkit-box-shadow: 0px 0px 24px -1px rgba(56, 56, 56, 1);
        -moz-box-shadow: 0px 0px 24px -1px rgba(56, 56, 56, 1);
        box-shadow: 0px 0px 24px -1px rgba(56, 56, 56, 1);
        z-index: 999;
    }
    #toast.error {
        background-color: #880000;
    }
    #toast.success {
        background-color: #008800;
    }
    {% block csscode %}{% endblock %}
  </style>

  {{ stylesheet_link("/css/style.css") }}
</head>
<body class="w3-light-grey">

  <div class="w3-main" style="margin-top:43px;">
    {% block main %}{% endblock %}
  </div>

  {% if flashSession.has('error') -%}
    <div id="toast" class="error" style="display:none;">{{ flashSession.output() }}</div>
  {% endif %}

  {% if flashSession.has('success') -%}
    <div id="toast" class="success" style="display:none;">{{ flashSession.output() }}</div>
  {% endif %}

  <script type='text/javascript' src='/js/jquery-2.1.0.min.js'></script>
  {% block jsfile %}{% endblock %}
  {% block jscode %}{% endblock %}

  <script type="text/javascript">
    $(document).ready(function() {
      $('#toast').fadeIn(400).delay(3000).fadeOut(400);
      {% block domready %}{% endblock %}
    });
  </script>
</body>
</html>
