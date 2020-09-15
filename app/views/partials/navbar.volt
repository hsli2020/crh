<div class="w3-container w3-top w3-teal w3-medium" style="z-index:4">
<ul class="w3-navbar">
  <li class="w3-dropdown-hover w3-teal w3-hover-grey">
    <a href="javascript:;" class="w3-hover-grey">Dashboard <i class="fa fa-caret-down"></i></a>
    <div class="w3-dropdown-content w3-white w3-card-4">
      <a href="/crh1" class="w3-hover-teal">CRH1 Dashboard</a>
      <a href="/crh2" class="w3-hover-teal">CRH2 Dashboard</a>
    </div>
  </li>

  <li class="w3-dropdown-hover w3-right">
    <a href="javascript:;" class="w3-teal w3-hover-grey">Profile <i class="fa fa-caret-down"></i></a>
    <div class="w3-dropdown-content w3-white w3-card-4" style="right:0">
{#
      <a href="#" class="w3-hover-teal">Settings</a>
      <a href="/user/change-password" class="w3-hover-teal">Change Password</a>
#}
      <a href="/user/logout" class="w3-hover-teal">Log out</a>
    </div>
  </li>

  <li class="w3-right"><a href="#" class="w3-hover-teal">{{ auth['username'] }}</a></li>
</ul>
</div>
