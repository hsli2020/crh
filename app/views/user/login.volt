{% extends "layouts/public.volt" %}

{% block main %}
<div class="container">
  <section class="leftside">
      <div class="" style="display: block;">
        <div class="w3-modal-content w3-padding" style="max-width:400px; margin-top:20%;">
          <p style="text-align: center;">
            <img src="/img/gcs-logo-name-223x38.png">
          </p>

          <div class="w3-center">
            <img src="/img/avatar_2x.png" alt="Avatar" style="width:30%" class="w3-circle w3-margin-top">
          </div>

          <form class="w3-container" method="POST">
            <div class="w3-section">
              <label><b>Username</b></label>
              <input class="w3-input w3-border w3-margin-bottom" placeholder="Enter Username" name="username" required autofocus type="text" value="{{ username }}">

              <label><b>Password</b></label>
              <input class="w3-input w3-border" placeholder="Enter Password" name="password" required type="password">

              <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}"/>

              <button class="w3-btn-block w3-green w3-section w3-padding" type="submit">Login</button>
            </div>
          </form>
        </div>
      </div>
  </section>

  <section class="rightside">
    <img src="/img/rightbg.jpg" width="100%" class="vertical-center">
  </section>
</div>
{% endblock %}

{% block csscode %}
.leftside {
    display: table-cell;
    height: 100vh;
    vertical-align: middle;
    position: relative;
    float: left;
    width: 50%;
}
.rightside {
    display: table-cell;
    height: 100vh;
    vertical-align: middle;
    position: relative;
    background-color: #0e64ad;
    float: right;
    width: 50%;
}
.vertical-center {
  margin: 0;
  position: absolute;
  top: 50%;
  -ms-transform: translateY(-50%);
  transform: translateY(-50%);
}
</style>

{% endblock %}
