{if $GALETTE_MODE eq 'DEV'}
	{assign var="page_title" value="{$page_title} ({$GALETTE_MODE})"}
	{$file1="/home/galette/galette/data/logs/template-ski_members.txt"}
	{file_put_contents($file1, "\npage_title : " )}
	{file_put_contents($file1,  $page_title,FILE_APPEND)}
	{$output = print_r($P, true)}
	{file_put_contents($file1,  $output,FILE_APPEND)}
{/if}
{extends file="page.tpl"}
{block name="content"}
{if $GALETTE_MODE eq 'DEV'} {*debug*} {/if}

<div id="lend_content">
 <form id="filtre" method="POST" action='{path_for name="ski_filter_members" data=["type"=> "list"] }'  method="POST" id="filtre">
<div id="listfilter">
      <label for="filter_str">{_T string="Search:" domain="ski"}&nbsp;
      </label>
      <input type="text" name="filter_str" id="filter_str" value="{$filters->filter_str}" type="search"
             placeholder="{_T string="Enter a value" domain="ski"}"/>&nbsp;
      <label for="field_filter"> {_T string="in:" domain="ski"}&nbsp;
      </label>
      <select name="field_filter" id="field_filter" onchange="form.submit()">
        {html_options options=$field_filter_options selected=$filters->field_filter}
      </select>
      <input type="submit" class="inline" value="{_T string="Filter" domain="ski"}"/>
      <input name="clear_filter" type="submit" value="{_T string="Clear filter" domain="ski"}">
    </div>
    <div class="infoline">
      <div class="fright">
        <label for="nbshow">{_T string="Records per page:" domain="ski"}
        </label>
        <select name="nbshow" id="nbshow">
          {html_options options=$nbshow_options selected=$numrows}
        </select>
        <noscript>
          <span>
            <input type="submit" value="{_T string="Change" domain="ski"}"/>
          </span>
        </noscript>
      </div>
    </div> 
</form>
<form action="{path_for name="ski_members"}" method="post" id="listform">
  <table class="listing">
    <thead>
      <tr>
        {if $preferences->pref_show_id}
        <th class="id_row">
          <a href="{path_for name="ski_members" data=["option"=> "order", "value" => "Galette\Repository\Members::ORDERBY_ID"|constant]}">
            {_T string="Mbr num"}
            {if $filters->orderby eq constant('Galette\Repository\Members::ORDERBY_ID')}
            {if $filters->ordered eq constant('Galette\Filters\MembersList::ORDER_ASC')}
            <img src="{base_url}/{$template_subdir}images/down.png" width="10" height="6" alt="" />
            {else}
            <img src="{base_url}/{$template_subdir}images/up.png" width="10" height="6" alt="" />
            {/if}
            {/if}
          </a>
        </th>
        {else}
        <th class="id_row">#</th>
        {/if}
        <th class="left">
          <a href="{path_for name="ski_members" data=["option"=> "order", "value" => "Galette\Repository\Members::ORDERBY_NAME"|constant]}">
            {_T string="Name"}
            {if $filters->orderby eq constant('Galette\Repository\Members::ORDERBY_NAME')}
            {if $filters->ordered eq constant('Galette\Filters\MembersList::ORDER_ASC')}
            <img src="{base_url}/{$template_subdir}images/down.png" width="10" height="6" alt="" />
            {else}
            <img src="{base_url}/{$template_subdir}images/up.png" width="10" height="6" alt="" />
            {/if}
            {/if}
          </a>
        </th>

        <th class="left">
            {_T string="Family"}
        </th>
        {foreach $dynval item=dyn key=id_dyn }
          <th class="center">
            {_T string=$dyn['fname']}
          </th>
        {/foreach}

        <th class="left">
          {_T string="Phone"}
        </th>
        <th class="left">
          {_T string="GSM"}
        </th>
        <th class="left">
          <a href="{path_for name="ski_members" data=["option"=> "order", "value" => "Galette\Repository\Members::ORDERBY_MODIFDATE"|constant]}">
            {_T string="Modified"}
            {if $filters->orderby eq constant('Galette\Repository\Members::ORDERBY_MODIFDATE')}
            {if $filters->ordered eq constant('Galette\Filters\MembersList::ORDER_ASC')}
            <img src="{base_url}/{$template_subdir}images/down.png" width="10" height="6" alt="" />
            {else}
            <img src="{base_url}/{$template_subdir}images/up.png" width="10" height="6" alt="" />
            {/if}
            {/if}
          </a>
        </th>
        <th class="actions_row">{_T string="Actions"}</th>
      </tr>
    </thead>
    <tbody>


      {foreach from=$list_members item=member key=ordre}
      {assign var=rclass value=$member->getRowClass()}
      {$parent_id=$members[$member->id]['parent_id']}
      {$parent_name=$members[$member->id]['parent_name']}
      <tr>
        {if $preferences->pref_show_id}
        <td class="{$rclass} right" data-scope="id">{$member->id}</td>
        {else}
        <td class="{$rclass} right" data-scope="id">{$ordre+1+($filters->current_page - 1)*$numrows}</td>
        {/if}
        <td class="{$rclass} nowrap username_row" data-scope="row">

          {if $member->isMan()}
          <span class="tooltip">
            <img src="{base_url}/{$template_subdir}images/icon-male.png" alt="" width="16" height="16" />
            <span class="sr-only">{_T string="Is a man"}</span>
          </span>
          {elseif $member->isWoman()}
          <span class="tooltip">
            <img src="{base_url}/{$template_subdir}images/icon-female.png" alt="" width="16" height="16" />
            <span class="sr-only">{_T string="Is a women"}</span>
          </span>
          {else}
          <img src="{base_url}/{$template_subdir}images/icon-empty.png" alt="" width="16" height="16" />
          {/if}
          {if $member->email != ''}
          <a href="mailto:{$member->email}" class="tooltip">
            <img src="{base_url}/{$template_subdir}images/icon-mail.png" alt="" width="16" height="16" />
            <span class="sr-only">{_T string="Mail"}</span>
          </a>
          {else}
          <img src="{base_url}/{$template_subdir}images/icon-empty.png" alt="" width="16" height="16" />
          {/if}

          {if $member->isStaff()}
          <span class="tooltip">
            <img src="{base_url}/{$template_subdir}images/icon-staff.png" alt="" width="16" height="16" />
            <span class="sr-only">{_T string="Staff member"}</span>
          </span>
          {else}
          <img src="{base_url}/{$template_subdir}images/icon-empty.png" alt="" width="16" height="16" />
          {/if}
          {assign var="mid" value=$member->id}
          {if $parent_id == ''}
            {$pid=$member->id}
            <a href="{path_for name="ski_members" data=["option" => "edit" , "value" => $pid] }">{$member->sname}</a>
          {else}
            {$member->sname} 
          {/if}
        </td>
        <td class="{$rclass}" data-title="{_T string=" Family"}"><strong>
          <a href="{path_for name="ski_members" data=["option" => "edit/" , "value" => $parent_id] }" >
          {if $parent_id != ''}
            {$parent_name} ({$parent_id})
          {/if}
          </a>
        </strong></td>
        {foreach $dynval item=dyn key=id_dyn }

        {$fname=$dyn['fname']}
        {$values=$dyn['values']}
        {$fval=$dynadh[$mid][$id_dyn]['fval']}
        {$ftext=$dynadh[$mid][$id_dyn]['ftext']}
        {if $fval == ''}{$fval=0}{/if}
        <td class="{$rclass} nowrap" >
            {if $ftext eq 'non' }  {$ftext=''} {/if}
              {if $ftext ne '' }
              {$ftext}
              {/if}
        </td>
        {/foreach}
        {if $login->isAdmin() or $login->isStaff()}
        <td class="{$rclass}" data-title="{_T string=" Phone"}">{$member->phone}</td>
        <td class="{$rclass}" data-title="{_T string=" GSM"}">{$member->gsm}</td>
        <td class="{$rclass}" data-title="{_T string=" Modified"}">{$member->modification_date}</td>
        <td class="{$rclass} center nowrap actions_row">
          <a href="{path_for name="ski_members" data=["option" => "edit", "value" => $mid]}" class="tooltip action">
            <i class="fas fa-user-edit fa-fw" aria-hidden="true"></i>
            <span class="sr-only">{_T string="%membername: edit informations" pattern="/%membername/" replace=$member->sname}</span>
          </a>
          <a href="{path_for name="contributions" data=["type"=> "contributions", "option" => "member", "value" => $member->id]}" class="tooltip">
            <i class="fas fa-cookie fa-fw" aria-hidden="true"></i>
            <span class="sr-only">{_T string="%membername: contributions" pattern="/%membername/" replace=$member->sname}</span>
          </a>

          {/if}

          {* If some additionnals actions should be added from plugins, we load the relevant template file
          We have to use a template file, so Smarty will do its work (like replacing variables). *}
          {if $plugin_actions|@count != 0}
          {foreach from=$plugin_actions key=plugin_name item=action}
          {include file=$action module_id=$plugin_name|replace:'actions_':''}
          {/foreach}
          {/if}
        </td>
      </tr>
      {foreachelse}
      <tr>
        <td colspan="7" class="emptylist">{_T string="No member has been found"}</td>
      </tr>
      {/foreach}
    </tbody>
  </table>
  {*if $nb_members != 0*}
  <div class="center cright">
    {_T string="Pages:"}<br />
    <ul class="pages">{$pagination}</ul>
  </div>

  {*/if*}

</form>
{if $nb_members != 0}
<div id="legende" title="{_T string=" Legend"}">
  <h1>{_T string="Legend"}</h1>
  <table>
    <tbody>
      <tr>
        <th class="" colspan="4">{_T string="Reading the list"}</th>
      </tr>
      <tr>
        <td> &nbsp;</td>
      </tr>
      <tr>
        <th class="back">{_T string="Name"}</th>
        <td class="back">{_T string="Active account"}</td>
        <th class="inactif back">{_T string="Name"}</th>
        <td class="back">{_T string="Inactive account"}</td>
      </tr>
      <tr>
        <th class="cotis-ok color-sample">&nbsp;</th>
        <td class="back">{_T string="Membership in order"}</td>
        <th class="cotis-soon color-sample">&nbsp;</th>
        <td class="back">{_T string="Membership will expire soon (&lt;30d)"}</td>
      </tr>
      <tr>
        <th class="cotis-never color-sample">&nbsp;</th>
        <td class="back">{_T string="Never contributed"}</td>
        <th class="cotis-late color-sample">&nbsp;</th>
        <td class="back">{_T string="Lateness in fee"}</td>
      </tr>
    </tbody>

    <br><br>
    <tbody>
      <tr>
        <td> &nbsp;</td>
      </tr>
      <tr>
        <th class="" colspan="4">{_T string="Actions"}</th>
      </tr>
      <tr>
        <td> &nbsp;</td>
      </tr>
      <tr>
        <th class="action">
          <i class="fas fa-user-edit fa-fw"></i>
        </th>
        <td class="back">{_T string="Modification"}</td>
        <th>
          <i class="fas fa-cookie fa-fw"></i>
        </th>
        <td class="back">{_T string="Contributions"}</td>
      </tr>

    </tbody>
    <tbody>
      <tr>
        <td> &nbsp;</td>
      </tr>
      <tr>
        <th colspan="4">{_T string="User status/interactions"}</th>
      </tr>
      <tr>
        <td> &nbsp;</td>
      </tr>
      <tr>
        <th><img src="{base_url}/{$template_subdir}images/icon-mail.png" alt="{_T string=" Mail"}" width="16" height="16" /></th>
        <td class="back">{_T string="Send a mail"}</td>

      </tr>

      <tr>
        <th><img src="{base_url}/{$template_subdir}images/icon-male.png" alt="{_T string=" Is a man"}" width="16" height="16" /></th>
        <td class="back">{_T string="Is a man"}</td>
        <th><img src="{base_url}/{$template_subdir}images/icon-female.png" alt="{_T string=" Is a woman"}" width="16" height="16" /></th>
        <td class="back">{_T string="Is a woman"}</td>
      </tr>

      <th><img src="{base_url}/{$template_subdir}images/icon-star.png" alt="{_T string=" Admin"}" width="16" height="16" /></th>
      <td class="back">{_T string="Admin"}</td>
      <th><img src="{base_url}/{$template_subdir}images/icon-staff.png" alt="{_T string=" Staff member"}" width="16" height="16" /></th>
      <td class="back">{_T string="Staff member"}</td>

      </tr>
    </tbody>
  </table>
</div>
{/if}
</div>
{/block}

{block name="javascripts"}
<script type="text/javascript">
  $(function () {
    $('#nbshow').change(function () {
      this.form.submit();
    });
  });
</script>

script type="text/javascript">
  {if nb_forms != 0}
    var _is_checked = true;
    var _bind_check = function () {
      $('#checkall').click(function () {
        $('table.listing :checkbox[name="form_ids[]"]').each(function () {
          this.checked = _is_checked;
        });
        _is_checked = !_is_checked;
        return false;
      });
      $('#checkinvert').click(function () {
        $('table.listing :checkbox[name="form_ids[]"]').each(function () {
          this.checked = !$(this).is(':checked');
        });
        return false;
      });
    };
    {* Use of Javascript to draw specific elements that are not relevant is JS is inactive *}
      $(function () {
        $('#table_footer').parent().before('<tr><td id="checkboxes" colspan="4"><span class="fleft"><a href="#" id="checkall">{_T string='(Un)Check all' domain='ski'}</a> | <a href="#" id="checkinvert">{_T string='Invert selection'}</a></span></td></tr>');
        _bind_check();
        {* No legend?
            $('#checkboxes').after('<td class="right" colspan="3"><a href="#" id="show_legend">{_T string='Show legend' domain='ski'}</a></td>');
          $('#legende h1').remove();
          $('#legende').dialog({
          autoOpen: false,
            modal: true,
              hide: 'fold',
                width: '40%'
        }).dialog('close');
        $('#show_legend').click(function(){
          $('#legende').dialog('open');
          return false;
        });*}
        $('.selection_menu input[type="submit"], .selection_menu input[type="button"]').click(function () {
          if (this.id == 'delete') {
            //mass removal is handled from 2 steps removal
            return;
          }
          return _checkselection();
        });
                                                                                             );
      });
      {if $login->isAdmin() || $login->isStaff()}
        var _checkselection = function () {
          var _checkeds = $('table.listing').find('input[type=checkbox]:checked').length;
          if (_checkeds == 0) {
            var _el = $("
                        < div;
                        id = 'pleaseselect';
                        title = "{_T string='No form selected' domain='ski' escape='js'}" >
                        {_T string='Please make sure to select at least one form from the list to perform this action.' domain='ski' escape='js'}
                        < /div>;
                        ");;
                        _el.appendTo('body').dialog({
              modal: true,
              buttons: {
                Ok: function () {
                  $(this).dialog("close");
                }
              }
              ,
              close: function (event, ui) {
                _el.remove();
              }
            });
            return false;
          }
          else {
            return true;
          };
          {/if}
        {/if}
</script>
{/block}
