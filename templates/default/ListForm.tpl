{if $GALETTE_MODE eq 'DEV'}
	{assign var="page_title" value="{$page_title} ({$GALETTE_MODE})"}
{/if}
{extends file="page.tpl"}
{block name="content"}
{if $GALETTE_MODE eq 'DEV'} {debug} {/if}
<div id="lend_content">
  <form id="filtre" method="POST" action='{path_for name="ski_filter_form" data=["type"=> "list"] }'  method="POST" id="filtre">
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
  <form action="{path_for name="ski_form_list"}" method="post" id="form_list">
    <table class="listing">
      <thead>
        <tr>
          <th>
            <a href="{path_for name="ski_form_list" data=["option" => "order", "value" =>
              "GaletteSki\Repository\Form::ORDERBY_FORM"|constant]}">
              {_T string="Id" domain="ski"}
              {if $filters->orderby eq constant('GaletteSki\Repository\Form::ORDERBY_FORM')}
              {if $filters->ordered eq constant('GaletteSki\Filters\FormFilter::ORDER_DESC')}
              <img src="{base_url}/{$template_subdir}images/down.png" width="10" height="6"alt=""/>
              {else}
              <img src="{base_url}/{$template_subdir}images/up.png" width="10" height="6" alt=""/>
              {/if}
              {/if}
            </a>
          </th>
          <th>
            <a href="{path_for name="ski_form_list" data=["option" => "order", "value" =>
             "GaletteSki\Repository\Form::ORDERBY_BDATE"|constant]}">
              {_T string="Begin Date" domain="ski"}
              {if $filters->orderby eq constant('GaletteSki\Repository\Form::ORDERBY_BDATE')}
              {if $filters->ordered eq constant('GaletteSki\Filters\FormFilter::ORDER_ASC')}
              <img src="{base_url}/{$template_subdir}images/down.png" width="10" height="6"alt=""/>
              {else}
              <img src="{base_url}/{$template_subdir}images/up.png" width="10" height="6" alt=""/>
              {/if}
              {/if}
            </a>
          </th>
          <th>
            <a href="{path_for name="ski_form_list" data=["option" => "order", "value" =>
                                                                                      "GaletteSki\Repository\Form::ORDERBY_FDATE"|constant]}">
              {_T string="Forecast Date" domain="ski"}
              {if $filters->orderby eq constant('GaletteSki\Repository\Form::ORDERBY_FDATE')}
              {if $filters->ordered eq constant('GaletteSki\Filters\FormFilter::ORDER_ASC')}
              <img src="{base_url}/{$template_subdir}images/down.png" width="10" height="6"alt=""/>
              {else}
              <img src="{base_url}/{$template_subdir}images/up.png" width="10" height="6" alt=""/>
              {/if}
              {/if}
            </a>
          </th>
          <th>
            <a href="{path_for name="ski_form_list" data=["option" => "order", "value" =>
                                                                                      "GaletteSki\Repository\Form::ORDERBY_EDATE"|constant]}">
              {_T string="End Date" domain="ski"}
              {if $filters->orderby eq constant('GaletteSki\Repository\Form::ORDERBY_EDATE')}
              {if $filters->ordered eq constant('GaletteSki\Filters\FormFilter::ORDER_ASC')}
              <img src="{base_url}/{$template_subdir}images/down.png" width="10" height="6"alt=""/>
              {else}
              <img src="{base_url}/{$template_subdir}images/up.png" width="10" height="6" alt=""/>
              {/if}
              {/if}
            </a>
          </th>
          <th>
            <a href="{path_for name="ski_form_list" data=["option" => "order", "value" =>
                                                                                      "GaletteSki\Repository\Form::ORDERBY_PERIOD"|constant]}">
              {_T string="Period" domain="ski"}
              {if $filters->orderby eq constant('GaletteSki\Repository\Form::ORDERBY_PERIOD')}
              {if $filters->ordered eq constant('GaletteSki\Filters\FormFilter::ORDER_ASC')}
              <img src="{base_url}/{$template_subdir}images/down.png" width="10" height="6"alt=""/>
              {else}
              <img src="{base_url}/{$template_subdir}images/up.png" width="10" height="6" alt=""/>
              {/if}
              {/if}
            </a>
          </th>
          <th>
            <a href="{path_for name="ski_form_list" data=["option" => "order", "value" =>"GaletteSki\Repository\Form::ORDERBY_FORM"|constant]}">
              {_T string="Parent_id" domain="ski"}
              {if $filters->orderby eq constant('GaletteSki\Repository\Form::ORDERBY_NAME')}
              {if $filters->ordered eq constant('GaletteSki\Filters\FormFilter::ORDER_ASC')}
              <img src="{base_url}/{$template_subdir}images/down.png" width="10" height="6"alt=""/>
              {else}
              <img src="{base_url}/{$template_subdir}images/up.png" width="10" height="6" alt=""/>
              {/if}
              {/if}
            </a>
          </th>
          <th>
            <a href="{path_for name="ski_form_list" data=["option" => "order", "value" =>"GaletteSki\Repository\Form::ORDERBY_STATUS"|constant]}">
              {_T string="Status" domain="ski"}
              {if $filters->orderby eq constant('GaletteSki\Repository\Form::ORDERBY_STATUS')}
              {if $filters->ordered eq constant('GaletteSki\Filters\FormFilter::ORDER_ASC')}
              <img src="{base_url}/{$template_subdir}images/down.png" width="10" height="6"alt=""/>
              {else}
              <img src="{base_url}/{$template_subdir}images/up.png" width="10" height="6" alt=""/>
              {/if}
              {/if}
            </a>
          </th>
          <th>{_T string="Comment" domain="ski"}
          </th>
          <th class="actions_row">{_T string="Actions" domain="ski"}
          </th>
        </tr>
      </thead>
      <tbody>
        {foreach $lform  as $form}
        {$form_id=$form["form_id"]}
        {$form_status=$form["status"]}
        {$parent_id=$form["parent_id"]}
        <tr class="{if $form@index is odd}even{else}odd{/if}">
          <td class="center">
            <a href="{path_for name="ski_form"}/{$form_id}" title="{_T string="Open the form" domain="ski"}">{$form_id} </a>
          </td>
          <td class="center nowrap">
            {if $form["date_begin"]}
              {$form["date_begin"]|date_format:_T("Y-m-d")}
            {else}
            -
            {/if}
          </td>
          <td class="center nowrap">
            {if $form["date_forecast"]}
              {$form["date_forecast"]|date_format:_T("Y-m-d")}
            {else}
            -
            {/if}
          </td>
          <td class="center nowrap">
            {if $form["date_end"]}
              {$form["date_end"]|date_format:_T("Y-m-d")}
            {else}
            -
            {/if}
          </td>
          <td class="left">
            {if $form["period"]}
              {$form["period"]}
            {/if}
          </td>
          <td class="left">
            {foreach $members as $member}
            {if $member["id_adh"] == $form["parent_id"]}
            <strong>
            <a href="{path_for name="ski_form"}/{$form_id}" title="{_T string="Open the form" domain="ski"}"> 
                {$form["parent_sname"]} ({$form["parent_id"]})
              </a>
              </strong>
            {/if}
            {/foreach}

          </td>
          {if $form["form_status"] == "Open"} <td class="left">
          {elseif $form["form_status"] == "Done"} <td class="center">
          {else} <td class="right">
          {/if}
            {$form["form_status"]}
          </td>
          <td>
            {$form["comment"]}
          </td>
          <td class="center nowrap">
            {if $login->isAdmin() || $login->isStaff()}
            <a class="tooltip action"
               href="{path_for name="ski_form"}/{$form_id}"
               title="{_T string="Open the form" domain="ski"}">
              <i class="fas fa-edit">
              </i>
              <span class="sr-only">{_T string="Edit the form" domain="ski"}
              </span>
            </a>
            <a class="tooltip"
               href="{path_for name="ski_form_printform"}/{$form_id}"
               title="{_T string="form card in PDF" domain="ski"}">
              <i class="fas fa-file-pdf">
              </i>
              <span class="sr-only">{_T string="form card in PDF" domain="ski"}
              </span>
            </a>
            {/if}
            {if $login->isAdmin()}
            <a class="delete tooltip"
               href="{path_for name="ski_remove_form"}/{$form_id}"
               title="{_T string="Remove %form from database" domain="ski" pattern="/%form/" replace=$form->name}">
              <i class="fas fa-trash">
              </i>
              <span class="sr-only">{_T string="Remove %form from database" domain="ski" pattern="/%form/" replace=$form->name}
              </span>
            </a>
            {/if}
          </td>
        </tr>
        {foreachelse}
        {* FIXME: calculate colspan *}
        <tr>
          <td colspan="14"
              class="emptylist">{_T string="No form has been found" domain="ski"}
          </td>
        </tr>
        {/foreach}
      </tbody>
      {if $nb_forms != 0}
      <tfoot>
        <tr>
          <td colspan="14" id="table_footer">
          </td>
        </tr>
        <tr>
          <td colspan="14" class="center">
            {_T string="Pages:" domain="ski"}
            <br/>
            <ul class="pages">{$pagination}
            </ul>
          </td>
        </tr>
      </tfoot>
      {/if}
    </table>
  </form>
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

</script>
<script type="text/javascript">
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
