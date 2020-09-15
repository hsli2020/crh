<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->
  <title>{% if pageTitle is defined %}{{ pageTitle }} - {% endif %}Great Circle Solar</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  {{ stylesheet_link("/css/w3.css") }}
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
  <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">
  {% block cssfile %}{% endblock %}

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
<body>
  {% block sidebar %}{# include "partials/sidebar.volt" #}{% endblock %}
  {% block navbar %}{% include "partials/navbar.volt" %}{% endblock %}

  <div class="w3-main" style="margin-top:43px;">
    {% block titlebar %}
    <!-- Header -->
    <header class="w3-container w3-padding-top w3-padding-bottom">
      <img class="w3-left" src="/img/gcs-logo-64x55.png" style="width: 64px; height: 55px; margin-right: 15px;">
      <h3 class="w3-left">{{ pageTitle }}</h3>
    </header>
    {% endblock %}

    {% block breadcrumb %}{% include "partials/breadcrumb.volt" %}{% endblock %}
    {% block main %}{% endblock %}

    <!-- Footer -->
    <footer class="w3-container w3-padding-16" style="text-align:center">
      {% block footer %}{% endblock %}
    </footer>
  </div>

  {% if flashSession.has('error') -%}
    <div id="toast" class="error" style="display:none;">{{ flashSession.output() }}</div>
  {% endif %}

  {% if flashSession.has('success') -%}
    <div id="toast" class="success" style="display:none;">{{ flashSession.output() }}</div>
  {% endif %}

  <!-- Overlay effect when opening sidenav on small screens -->
  <div class="w3-overlay w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="close side menu"></div>

  <script type='text/javascript' src='/js/jquery-2.1.0.min.js'></script>
  {% block jsfile %}{% endblock %}

  <script type="text/javascript">
    {% block jscode %}{% endblock %}
  </script>

  <script type="text/javascript">
    $(document).ready(function() {
      $('#toast').fadeIn(400).delay(3000).fadeOut(400);
      {% block domready %}{% endblock %}
    });
  </script>

  <script type="text/javascript">
    // Script to open and close sidenav
    function w3_open() {
      document.getElementsByClassName("w3-sidenav")[0].style.display = "block";
      document.getElementsByClassName("w3-overlay")[0].style.display = "block";
    }

    function w3_close() {
      document.getElementsByClassName("w3-sidenav")[0].style.display = "none";
      document.getElementsByClassName("w3-overlay")[0].style.display = "none";
    }
  </script>
</body>
</html>
