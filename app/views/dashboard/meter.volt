{% extends "layouts/base.volt" %}

{% block main %}

<style type="text/css">
  table { border: 5px solid #eee !important; }
  table, th, td { border: 1px solid #ddd; }
  .w3-table td, .w3-table th, .w3-table-all td, .w3-table-all th { text-align: center; padding: 5px 8px; }
  .w3-table th.text-left, .w3-table td.text-left { text-align: left; }
  .v-middle { vertical-align: middle; }
  #header { text-align: center; }
  .text-left{ text-align: left; }
  #chart-content {
    width: 100%;
    margin: 0 auto;
    padding: 0;
  }
  .chart-container {
    box-sizing: border-box;
    width: 100%;
    height: 700px;
    padding: 10px;
    margin: 0;
    border: 1px solid #ddd;
    background: none;
  }
</style>

<div class="w3-row">
  <!-- div id="header">
    <h2>DEMAND RESPONSE</h2>
  </div -->
  <div class="w3-container w3-third">
    <table id="table1" class="w3-table w3-white w3-bordered w3-border">
      <tr>
        <th>&nbsp;</th>
        <th>Actual Load</th>
        <th>Standard Baseline</th>
      </tr>
      <tr>
        <th>Time Stamp</th>
        <th>kWh</th>
        <th>kWh</th>
      </tr>

      {% for d in data %}
        <tr>
          <td>{{ date }} {{ d[0] }}:00</td>
          {% if d[2] is not empty %}
            <td class="w3-text-pink">{{ d[2] }}</td>
          {% else %}
            <td>-</td>
          {% endif %}
          <td class="w3-text-blue">{{ d[1] }}</td>
        </tr>
      {% endfor %}

      <tr><th colspan="3" class="text-left">Variance</th><tr>
      <tr>
        <td>{{ now }}</td>
        <td colspan="2">-</td>
      </tr>

      <tr><th colspan="3" class="text-left">Standard Baseline</th><tr>
      <tr>
        <td colspan="3" class="text-left">
            Average of the highest 15 measurement data values for the same hour that was 
            activated in the last 20 suitable business days prior to activation.
        </td>
      </tr>
    </table>
  </div>

  <div class="w3-container w3-twothird">
    <div id="chart-content">
      <div id="chart1">
        <div class="chart-container">
          <div id="placeholder1" class="chart-placeholder"></div>
        </div>
      </div>
    </div>
  </div>

</div>
{% endblock %}

{% block jscode %}
var line1 = {
    label: "Avg. of Top 15 Days",
    data: {{ jsonBase }},
    color: "#069",
    shadowSize: 0,
    yaxis: 2,
    lines: { show: true, lineWidth: 2 }
}

var line2 = {
    label: "Load",
    data: {{ jsonLoad }},
    color: "#c40",
    shadowSize: 0,
    yaxis: 2,
    lines: { show: true, lineWidth: 2 }
}

var options = {
    series: {
        shadowSize: 0,	// Drawing is faster without shadows
		lines: { show: true },
		points: { show: true },
    },
    //crosshair: { mode: "x" },
    grid: {
        hoverable: true,
        //clickable: true,
        autoHighlight: false,
    },
	legend: {
		position: "se",
	},
    yaxes: {
		ticks: 10,
		tickDecimals: 3,
    },
    xaxis: {
        //mode: 'time',
        show: true,
		autoscaleMargin: 0.01,
    }
}

plot1 = $.plot("#placeholder1", [ line1, line2 ], options);

{% endblock %}

{% block jsfile %}
{{ javascript_include("/flot/jquery.flot.js") }}
{{ javascript_include("/flot/jquery.flot.time.js") }}
{{ javascript_include("/flot/jquery.flot.crosshair.js") }}
{{ javascript_include("/pickadate/picker.js") }}
{{ javascript_include("/pickadate/picker.date.js") }}
{{ javascript_include("/js/script.js") }}
{% endblock %}

{% block cssfile %}
  {{ stylesheet_link("/pickadate/themes/classic.css") }}
  {{ stylesheet_link("/pickadate/themes/classic.date.css") }}
{% endblock %}

{% block domready %}
{% endblock %}
