{% extends "layouts/public.volt" %}

{% block main %}
<div class="container">
  <div class="w3-modal" style="display: block;">
    <div class="w3-modal-content w3-card-8" style="max-width:450px">
      <header class="w3-container w3-blue-grey">
        <h5>Add New User</h5>
      </header>

      <p style="text-align: center;">
        <img src="/img/gcs-logo-name-223x38.png">
      </p>

      <form class="w3-container" method="POST">
        <div class="w3-section">
          <div class="w3-row-padding">
            <div class="w3-third w3-padding-8">
              <label><b>Username</b></label>
            </div>
            <div class="w3-twothird">
              <input class="w3-input w3-border w3-margin-bottom" placeholder="Enter Username" name="username" required autofocus type="text">
            </div>
          </div>

          <div class="w3-row-padding">
            <div class="w3-third w3-padding-8">
              <label><b>Email Address</b></label>
            </div>
            <div class="w3-twothird">
              <input class="w3-input w3-border w3-margin-bottom" placeholder="Enter Email" name="email" type="text">
            </div>
          </div>

          <div class="w3-row-padding">
            <div class="w3-third w3-padding-8">
              <label><b>Password</b></label>
            </div>
            <div class="w3-twothird">
              <input class="w3-input w3-border w3-margin-bottom" placeholder="Enter Password" name="password" required type="password">
            </div>
          </div>

          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}"/>

          <button class="w3-btn-block w3-blue-grey w3-section w3-padding" type="submit">Add New User</button>
        </div>
      </form>
    </div>
  </div>
</div>
{% endblock %}

{% block csscode %}
{% endblock %}
