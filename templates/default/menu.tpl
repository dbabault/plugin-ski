{if $login->isLogged()}
<h1 class="nojs"> {_T string="Ski" domain="ski"} </h1>
  <ul>
    <li{if $cur_route eq "dashboard"} class="selected"{/if}>
      <a href="{path_for name="ski_dashboard" domain="ski"}" title="{_T string="Ski dashboard" domain="ski"}">
		   {_T string="Dashboard" domain="ski"}
	    </a>
	  </li>
      <li{if $cur_route eq "ski_form_list"} class="selected"{/if}>
        <a href="{path_for name="ski_form_list" domain="ski"}" title="{_T string="List Forms" domain="ski"}">
          {_T string="List Forms" domain="ski"}
        </a>
      </li>
      <li{if $cur_route eq "ski_form"} class="selected"{/if}>
		    <a href="{path_for name="ski_form"  domain="ski"}" title="{_T string="New Form" domain="ski"}" >
          {_T string="New Form" domain="ski"}
        </a>
	    </li>
      <li{if $cur_route eq "ski_family"} class="selected"{/if}>
		    <a href="{path_for name="ski_family"  data=["option" => "add"] domain="ski"}" title="Nouvelle Famille" >
          {"Nouvelle Famille"}
        </a>
	    </li>
      <li{if $cur_route eq "ski_members"} class="selected"{/if}>
		    <a href="{path_for name="ski_members" domain="ski"}" title="{_T string="List Members" domain="ski"}">
          {_T string="List Members" domain="ski"}
        </a>
	    </li>
  </ul>
{/if}
