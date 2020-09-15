<!-- Sidenav/menu -->
<nav class="w3-sidenav w3-collapse w3-white" style="z-index:3;width:240px;"><br>
  <div class="w3-container w3-row">
    <div class="w3-col s4">
      <img src="/img/img_avatar2.png" class="w3-circle w3-margin-right" style="width:46px">
    </div>
    <div class="w3-col s8">
      <span>Welcome, <strong>Buddy</strong></span><br>
      <a href="#" class="w3-hover-none w3-hover-text-red w3-show-inline-block"><i class="fa fa-envelope"></i></a>
      <a href="#" class="w3-hover-none w3-hover-text-green w3-show-inline-block"><i class="fa fa-user"></i></a>
      <a href="#" class="w3-hover-none w3-hover-text-blue w3-show-inline-block"><i class="fa fa-cog"></i></a>
    </div>
  </div>
  <hr>
  <div class="w3-container">
    <h5>Dashboard</h5>
  </div>
  <a href="#" class="w3-padding-16 w3-hide-large w3-dark-grey w3-hover-black" onclick="w3_close()" title="close menu"><i class="fa fa-remove fa-fw"></i>&nbsp; Close Menu</a>
  <a href="/index/chart" class="w3-padding{% if (dispatcher.getActionName() == 'chart')%} w3-blue{% endif %}"><i class="fa fa-users fa-fw"></i>&nbsp; Chart</a>
  <a href="/index/table" class="w3-padding{% if (dispatcher.getActionName() == 'table')%} w3-blue{% endif %}"><i class="fa fa-eye fa-fw"></i>&nbsp; Table</a>
<!--
  <a href="#" class="w3-padding"><i class="fa fa-users fa-fw"></i>&nbsp; Traffic</a>
  <a href="#" class="w3-padding"><i class="fa fa-bullseye fa-fw"></i>&nbsp; Geo</a>
  <a href="#" class="w3-padding"><i class="fa fa-diamond fa-fw"></i>&nbsp; Orders</a>
  <a href="#" class="w3-padding"><i class="fa fa-bell fa-fw"></i>&nbsp; News</a>
  <a href="#" class="w3-padding"><i class="fa fa-bank fa-fw"></i>&nbsp; General</a>
  <a href="#" class="w3-padding"><i class="fa fa-history fa-fw"></i>&nbsp; History</a>
  <a href="#" class="w3-padding"><i class="fa fa-cog fa-fw"></i>&nbsp; Settings</a><br><br>
-->
</nav>
