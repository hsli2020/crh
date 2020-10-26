{% extends "layouts/base.volt" %}

{% block main %}
<div class="container">
  <div style="display: block;margin: 0 auto;width: 500px;">
      <form class="w3-container" method="POST" autocomplete="off">
        <div class="w3-section">
{#
          <div class="w3-row-padding">
            <div class="w3-third w3-padding-16">
              <label><b>Meter</b></label>
            </div>
            <div class="w3-twothird w3-padding-8">
              <select class="w3-select w3-border" name="meter" required>
                <option disabled selected>Select Meter</option>
                <option value="1">Meter 1</option>
                <option value="2">Meter 2</option>
              </select>
            </div>
          </div>
#}
          <div class="w3-row-padding">
            <div class="w3-third w3-padding-16">
              <label><b>Data Type</b></label>
            </div>
            <div class="w3-twothird w3-padding-8">
              <select class="w3-select w3-border" name="datatype" required>
{#              <option disabled selected>Select Data Type</option> #}
                <option value="raw-data">Raw data</option>
                <option value="baseline-actual-load">Baseline + Actual Load</option>
              </select>
            </div>
          </div>

          <div class="w3-row-padding">
            <div class="w3-third w3-padding-16">
              <label><b>Start Date</b></label>
            </div>
            <div class="w3-twothird w3-padding-8">
              <input class="w3-input w3-border datepicker" name="start-time" required type="text" value="{{ startTime }}">
            </div>
          </div>

          <div class="w3-row-padding">
            <div class="w3-third w3-padding-16">
              <label><b>End Date</b></label>
            </div>
            <div class="w3-twothird w3-padding-8">
              <input class="w3-input w3-border datepicker" name="end-time" required type="text" value="{{ endTime }}">
            </div>
          </div>

          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}"/>

          <button class="w3-btn-block w3-indigo w3-section w3-padding" type="submit">Export/Download Data</button>
        </div>
      </form>
  </div>
</div>
{% endblock %}

{% block cssfile %}
  {{ stylesheet_link("/datetimepicker/jquery.datetimepicker.min.css") }}
{% endblock %}

{% block jsfile %}
  {{ javascript_include("/datetimepicker/jquery.datetimepicker.full.min.js") }}
{% endblock %}

{% block domready %}
  $('.datepicker').datetimepicker({format: 'Y-m-d H:i', timepicker:true, step: 30});
{% endblock %}
