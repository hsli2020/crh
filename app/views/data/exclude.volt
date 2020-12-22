{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  .w3-table td, .w3-table th, .w3-table-all td, .w3-table-all th { padding: 5px 8px; border: 1px solid #ddd; }
  .w3-table th.text-left, .w3-table td.text-left { text-align: left; }
  .v-middle { vertical-align: middle; }
  .text-left{ text-align: left; }
</style>

<div class="container">
  <div style="display: block;margin: 0 auto;width: 500px;">
      <form class="w3-container" method="POST" autocomplete="off">
        <div class="w3-section">
          <div class="w3-row-padding">
            <div class="w3-quarter w3-padding-16">
              <label><b>Date</b></label>
            </div>
            <div class="w3-threequarter w3-padding-8">
              <input class="w3-input w3-border datepicker" name="date" required type="text" value="">
            </div>
          </div>

          <div class="w3-row-padding">
            <div class="w3-quarter w3-padding-16">
              <label><b>Note</b></label>
            </div>
            <div class="w3-threequarter w3-padding-8">
              <input class="w3-input w3-border" name="note" required type="text" value="">
            </div>
          </div>

          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}"/>

          <button class="w3-btn-block w3-indigo w3-section w3-padding" type="submit">Exclude Date</button>
        </div>
      </form>
  </div>

  <div style="display: block;margin: 0 auto;width: 800px;">
      <table id="table1" class="w3-table w3-white w3-bordered w3-border">
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Note</th>
          <th>User</th>
          <th>Created On</th>
        </tr>

        {% for d in dates %}
        <tr>
          <td>{{ loop.index }}</td>
          <td>{{ d['date'] }}</td>
          <td>{{ d['note'] }}</td>
          <td>{{ d['user'] }}</td>
          <td>{{ d['createdon'] }}</td>
        </tr>
        {% endfor %}
      </table>
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
  $('.datepicker').datetimepicker({format: 'Y-m-d', timepicker:false, step: 30});
{% endblock %}
