{% extends "layouts/public.volt" %}

{% block main %}
<div class="container">
  <div class="w3-modal" style="display: block;">
    <div class="w3-modal-content w3-card-8" style="max-width:500px">
      <header class="w3-container w3-teal">
        <h5>Change Password</h5>
      </header>

      <p style="text-align: center;">
        <img src="/img/gcs-logo-name-223x38.png">
      </p>

      <form class="w3-container" method="POST">
        <div class="w3-section">
          <div class="w3-row-padding">
            <div class="w3-third w3-padding-8">
              <label><b>Old Password</b></label>
            </div>
            <div class="w3-twothird">
              <input class="w3-input w3-border w3-margin-bottom" placeholder="Enter Old Password" name="password_old" required autofocus type="password">
            </div>
          </div>

          <div class="w3-row-padding">
            <div class="w3-third w3-padding-8">
              <label><b>New Password</b></label>
            </div>
            <div class="w3-twothird">
              <input class="w3-input w3-border w3-margin-bottom" placeholder="Enter New Password" name="password_new" required type="password">
            </div>
          </div>

          <div class="w3-row-padding">
            <div class="w3-third w3-padding-8">
              <label><b>Confirmation</b></label>
            </div>
            <div class="w3-twothird">
              <input class="w3-input w3-border w3-margin-bottom" placeholder="Re-type New Password" name="password_new_retype" required type="password">
            </div>
          </div>

          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}"/>

          <button class="w3-btn-block w3-teal w3-section w3-padding" type="submit">Change Password</button>
        </div>
      </form>
    </div>
  </div>
</div>
{% endblock %}

{% block csscode %}
{% endblock %}
