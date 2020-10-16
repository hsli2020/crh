{% extends "layouts/base.volt" %}

{% block main %}

<style type="text/css">
  .w3-table td, .w3-table th, .w3-table-all td, .w3-table-all th { text-align: center; padding: 5px 8px; border: 1px solid #ddd; }
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
        <th>Time Stamp</th>
        <th>Actual Load</th>
        <th>Standard Baseline</th>
        <th>Variance</th>
      </tr>
      <tr>
        <th>(EST)</th>
        <th>kWh</th>
        <th>kWh</th>
        <th>kWh</th>
      </tr>

      {% for d in data %}
        <tr>
          <td>{{ date }} {{ d[0] }}</td>
          {% if d[2] is not empty %}
            <td class="w3-text-pink">{{ d[2] }}</td>
          {% else %}
            <td>-</td>
          {% endif %}

          <td class="w3-text-blue">{{ d[1] }}</td>

          {% if d[2] is not empty %}
            <td class="w3-text-black">{{ d[3] }}</td>
          {% else %}
            <td>-</td>
          {% endif %}
        </tr>
      {% endfor %}

      <tr><th colspan="4" class="text-left"><br>Current 5 min Load</th><tr>
      <tr>
        <td>{{ cur5min['time_est'] }}</td>
        <td colspan="2">{{ cur5min['kw'] }}</td>
        <td>kW</td>
      </tr>

      <tr><th colspan="4" class="text-left">Standard Baseline</th><tr>
      <tr>
        <td colspan="4" class="text-left">
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
    label: "Hourly Baseline", // "Avg. of Top 15 Days",
    data: {{ jsonBase }},
    color: "#069",
    points: { show: true },
    shadowSize: 4,	// Drawing is faster without shadows
    lines: { show: true, lineWidth: 2 }
}

var line2 = {
    label: "5 Min Load",
    data: {{ jsonMin5Load }},
    color: "#c40",
    points: { show: false },
    shadowSize: 4,
    lines: { show: true, lineWidth: 2 }
}

{#
var line3 = {
    label: "Curtailment Marker",
    data: {{ jsonMarker }},
    color: "#9c27b0",
    points: { show: true },
    shadowSize: 4,
    lines: { show: true, lineWidth: 2 },
}

var line4 = {
    label: "20% Band",
    data: {{ jsonBand }},
    color: "#00bcd4",
    points: { show: true },
    shadowSize: 4,
    lines: { show: true, lineWidth: 2 },
}
#}

var options = {
    //series: { },
    //crosshair: { mode: "x" },
    grid: {
        hoverable: true,
        //clickable: true,
        autoHighlight: false,
    },
    legend: { position: "se" },
    yaxes: { },
    xaxis: {
        mode: 'time',
        //mode: "categories", // x-axis is non-numeric, THIS IS IMPORTANT!
        show: true,
        //autoscaleMargin: 0.01,
    }
}

//plot1 = $.plot("#placeholder1", [ line2, line1, line3, line4 ], options);
plot1 = $.plot("#placeholder1", [ line2, line1 ], options);

$("<div id='tooltip'></div>").css({
    position: "absolute",
    display: "none",
    border: "1px solid #fdd",
    padding: "2px 5px",
    color: "white",
    "font-size": "16px",
    "font-weight": "bold",
    "background-color": "#fee",
    //opacity: 0.80
}).appendTo("body");

$("#placeholder1").bind("plothover", function (event, pos, item) {
    if (item) {
        var i = item.dataIndex,
            x = item.series.data[i][0],
            y = item.series.data[i][1];

        var tm = timefmt(x);

        $("#tooltip").html(item.series.label + " at " + tm + " = " + y)
            .css({top: item.pageY+5, left: item.pageX+5, backgroundColor: item.series.color})
            .fadeIn(20);
    } else {
        $("#tooltip").hide();
    }
});

function timefmt(x) {
    //return new Date(x).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', hour12: false});
    return new Date(x).toISOString().substr(11, 5);
}
{% endblock %}

{% block jsfile %}
{{ javascript_include("/flot/jquery.flot.js") }}
{{ javascript_include("/flot/jquery.flot.time.js") }}
{{ javascript_include("/flot/jquery.flot.crosshair.js") }}
{{ javascript_include("/flot/jquery.flot.categories.js") }}

{{ javascript_include("/pickadate/picker.js") }}
{{ javascript_include("/pickadate/picker.date.js") }}

{{ javascript_include("/js/script.js") }}
{% endblock %}

{% block cssfile %}
  {{ stylesheet_link("/pickadate/themes/classic.css") }}
  {{ stylesheet_link("/pickadate/themes/classic.date.css") }}
{% endblock %}

{% block csscode %}
.legend table { border: none; }
.legend tr { display: inline-table; }
{% endblock %}

{% block domready %}
{% endblock %}
