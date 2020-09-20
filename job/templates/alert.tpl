<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <style>
    table { border-collapse: collapse; }
    table, td, th { border: 1px solid gray; padding: 5px 10px; }
  </style>
</head>
<body>

<p><b></b></p>

<table>
<tr>
  <th>Time</th>
  <th>Meter</th>
  <th>Alert</th>
</tr>

<?php foreach ($alerts as $alert) { ?>
<tr>
  <td><?= $alert['time']; ?></td>
  <td><?= $alert['meter']; ?></td>
  <td><?= $alert['message']; ?></td>
</tr>
<?php } ?>
</table>

<p></p>

</body>
</html>
