{if $GALETTE_MODE eq 'DEV'} {assign var="page_title" value="{$page_title} ({$GALETTE_MODE})"} {/if}
{extends file="page.tpl"}
{block name="content"}
{if $GALETTE_MODE eq 'DEV'} {debug} {/if}


{foreach from=$members item=member key=ordre}
  {if intval($member->id) == intval($member->parent)}
    {$parent=$member->name}
    {$parent_s=$member->surname}
    {$parent_id=$member->parent}
    {$address=$member->address}
    {$zip=$member->zipcode}
    {$city=$member->town}
    {$email=$member->email}
    {$phone=$member->phone}
    {$gsm=$member->gsm}
    {$job=$member->job}
  {/if}
{/foreach}

<form action="{path_for name="ski_members" data=["option"=> "edit", "value" => $parent_id]}" method="post" id="filtre">
  <div class="bigtable">
    <fieldset class="galette_form" id="general">
      <legend>{_T string="General informations" domain="ski"}</legend>
      <div>
        <p><label for="Family">{_T string="Parent" domain="ski"}</label><b> {$parent} {$parent_s} ({$parent_id})</b></p>
        <p><label for="Address">{_T string="Address" domain="ski"}</label><b>{$address}</b></p>
        <p><label for="Zip">{_T string="Zip - Town" domain="ski"}</label><b>{$zip}</b></p>
        <p><label for="City">{_T string="City" domain="ski"}</label><b>{$city}</b></p>
        <p><label for="Email">{_T string="Email" domain="ski"}</label><b>{$email}</b></p>
        <p><label for="Phone">{_T string="Phone" domain="ski"}</label><b>{$phone}</b></p>
        <p><label for="date_begin">{_T string="GSM" domain="ski"}</label><b>{$gsm}</b></p>
        <p>
      </div>
    </fieldset>
    <fieldset class="galette_form" id="general">
      <legend>{_T string="Member" domain="ski"}</legend>
      <table width=100% >
        <thead>
          <tr>
            <th class="center">{_T string="Name"}</th>
            <th class="center">{_T string="Birth date" domain="ski"}</th>
            <th class="center">{_T string="Card Nbr" domain="ski"}</th>
            {foreach $dynval item=dyn key=id }
              {$fname=$dyn['fname']}
              <th class="center">{$fname}</th>
            {/foreach}
          </tr>
        </thead>
        <tbody>

          {* debut table saisie adherents *}
          {foreach from=$members item=member key=ordre}
            {$mid=(int)$member->id}
            <tr >
              <td class="left">
              <a href="{path_for name="member" data=["id" => $mid]}">{$member->surname} {$member->name}</a>
            </td>
              <td class="center">{$member->birthdate} {$member->getAge()}</td>
              <td class="center"> - </td>
              {foreach $dynval item=dyn key=id }
                <td class="center">
                  {$fname=$dyn['fname']}
                  {$values=$dyn['values']}
                  {$fval=$dynadh[$mid][$id]['fval']}
                  {if $fval ==  ''}
                    {$fval=0}
                  {/if}
                  <select name="{$fname}" id="{$id}">
                    <option value="">?</option>
                    {html_options options=$values selected=$fval}
                  </select>
                </td>
              {/foreach}
            </tr>
          {/foreach}
          {* fin table saisie adherents *}
        </tbody>
      </table>
      <br>
    </fieldset>
  </div>
  <div class="button-container" id="button_container">
    <a href="{path_for name="ski_members"}" class="button" id="btnsave">{_T string="Save"}</a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="{path_for name="ski_form"}" class="button" id="btncancel">{_T string="Cancel"}</a>
  </div>
</form>
{/block}

{block name="javascripts"}
<script type="text/javascript">
  {
    if $nb_members != 0
  }
  var _checkselection = function() {
      var _checkeds = $('table.listing').find('input[type=checkbox]:checked').length;
      if (_checkeds == 0) {
        var _el = $('<div id="pleaseselect" title="{_T string="No member selected" escape="js"}">{_T string="Please make sure to select at least one member from the list to perform this action." escape="js"}</div>');
        _el.appendTo('body').dialog({
          modal: true,
          buttons: {
            Ok: function() {
              $(this).dialog("close");
            }
          },
          close: function(event, ui) {
            _el.remove();
          }
        });
        return false;
      }
      return true;
    } {
      /if} {
        * Use of Javascript to draw specific elements that are not relevant is JS is inactive *
      }
      $(function() {

            _initTooltips('#listform'); {
              if $nb_members != 0
            }
            var _checklinks =
              '<div class="checkboxes"><span class="fleft"><a href="#" class="checkall tooltip"><i class="fas fa-check-square"></i> {_T string="(Un)Check all"}</a> | <a href="#" class="checkinvert tooltip"><i class="fas fa-exchange-alt"></i> {_T string="Invert selection"}</a></span><a href="#" class="show_legend fright">{_T string="Show legend"}</a></div>';
            $('.listing').before(_checklinks);
            $('.listing').after(_checklinks);
            _bind_check();
            _bind_legend();
            $('#nbshow').change(function() {
              this.form.submit();
            });
            $('.selection_menu *[type="submit"], .selection_menu *[type="button"]').click(function() {
              if (this.id == 'delete') {
                //mass removal is handled from 2 steps removal
                return;
              }

              if (!_checkselection()) {
                return false;
              } else {
                {
                  if $existing_mailing eq true
                }
                if (this.id == 'sendmail') {
                  var _el = $('<div id="existing_mailing" title="{_T string="Existing mailing"}">{_T string="A mailing already exists. Do you want to create a new one or resume the existing?"}</div>');
                  _el.appendTo('body').dialog({
                    modal: true,
                    hide: 'fold',
                    width: '25em',
                    height: 150,
                    close: function(event, ui) {
                      _el.remove();
                    },
                    buttons: {
                      '{_T string="Resume"}': function() {
                        $(this).dialog("close");
                        location.href = '{path_for name="mailing"}';
                      },
                      '{_T string="New"}': function() {
                        $(this).dialog("close");
                        //add required controls to the form, change its action URI, and send it.
                        var _form = $('#listform');
                        _form.append($('<input type="hidden" name="mailing_new" value="true"/>'));
                        _form.append($('<input type="hidden" name="mailing" value="true"/>'));
                        _form.submit();
                      }
                    }
                  });
                  return false;
                } {
                  /if}
                  if (this.id == 'attendance_sheet') {
                    _attendance_sheet_details();
                    return false;
                  }
                  return true;
                }
              }); {
              /if}
              if (_shq = $('#showhideqry')) {
                _shq.click(function() {
                  $('#sql_qry').toggleClass('hidden');
                  return false;
                });
              }
            });

            var _bindmassres = function(res) {
              res.find('#btncancel')
                .button()
                .on('click', function(e) {
                  e.preventDefault();
                  res.dialog('close');
                });

              res.find('input[type=submit]')
                .button();
            }

            $('#masschange').off('click').on('click', function(event) {
                  event.preventDefault();
                  var _this = $(this);

                  if (!_checkselection()) {
                    return false;
                  }
                  $.ajax({
                    url: '{path_for name="batch-memberslist"}',
                    type: "POST",
                    data: {
                      ajax: true,
                      masschange: true,
                      member_sel: $('#listform input[type=\"checkbox\"]:checked').map(function() {
                        return $(this).val();
                      }).get()
                    },
                    datatype: 'json',
                    {
                      include file = "js_loader.tpl"
                    },
                    success: function(res) {
                      var _res = $(res);
                      _bindmassres(_res);

                      _res.find('form').on('submit', function(e) {
                            e.preventDefault();
                            var _form = $(this);
                            var _data = _form.serialize();
                            $.ajax({
                                url: _form.attr('action'),
                                type: "POST",
                                data: _data,
                                datatype: 'json',
                                {
                                  include file = "js_loader.tpl"
                                },
                                success: function(html) {
                                  var _html = $(html);
                                  _bindmassres(_html);

                                  $('#mass_change').remove();
                                  $('body').append(_html);

                                  _initTooltips('#mass_change');
                                  //_massCheckboxes('#mass_change');

                                  _html.dialog({
                                    width: 'auto',
                                    modal: true,
                                    close: function(event, ui) {
                                      $(this).dialog('destroy').remove()
                                    }
                                  });

                                  _html.find('form').on('submit', function(e) {
                                    e.preventDefault();
                                    var _form = $(this);
                                    var _data = _form.serialize();
                                    $.ajax({
                                      url: _form.attr('action'),
                                      type: "POST",
                                      data: _data,
                                      datatype: 'json',
                                      {
                                        include file = "js_loader.tpl"
                                      },
                                      success: function(res) {
                                        if (res.success) {
                                          window.location.href = _form.find('input[name=redirect_uri]').val();
                                        } else {
                                          $.ajax({
                                            url: '{path_for name="ajaxMessages"}',
                                            method: "GET",
                                            success: function(message) {
                                              $('#asso_name').after(message);
                                            }
                                          });
                                        }
                                      }
                                    });
                                  });
                                },
                                error: function() {
                                  alert("{_T string="
                                    An error occurred: (" escape="
                                      js "}");
                                  }
                                });
                            });

                          $('body').append(_res);

                          _initTooltips('#mass_change'); _massCheckboxes('#mass_change');

                          _res.dialog({
                            width: 'auto',
                            modal: true,
                            close: function(event, ui) {
                              $(this).dialog('destroy').remove()
                            }
                          });
                        },
                        error: function() {
                          alert("{_T string="
                            An error occurred: (" escape="
                              js "}");
                          }
                        });
                  });

                  var _attendance_sheet_details = function() {
                      var _selecteds = [];
                      $('table.listing').find('input[type=checkbox]:checked').each(function() {
                        _selecteds.push($(this).val());
                      });
                      $.ajax({
                            url: '{path_for name="attendance_sheet_details"}',
                            type: "POST",
                            data: {
                              ajax: true,
                              selection: _selecteds
                            },
                            dataType: 'html',
                            success: function(res) {
                              var _el = $('<div id="attendance_sheet_details" title="{_T string="Attendance sheet details" escape="js"}"> </div>');
                              _el.appendTo('body').dialog({
                                modal: true,
                                hide: 'fold',
                                width: '60%',
                                height: 400,
                                close: function(event, ui) {
                                  _el.remove();
                                },
                                buttons: {
                                  Ok: function() {
                                    $('#sheet_details_form').submit();
                                    $(this).dialog("close");
                                  },
                                  Cancel: function() {
                                    $(this).dialog("close");
                                  }
                                }
                              }).append(res);
                              $('#sheet_date').datepicker({
                                changeMonth: true,
                                changeYear: true,
                                showOn: 'button',
                                yearRange: 'c:c+5',
                                buttonText: '<i class="far fa-calendar-alt"></i> <span class="sr-only">{_T string="Select a date" escape="js"}</span>'
                              });
                            },
                            error: function() {
                              alert("{_T string="
                                An error occurred displaying attendance sheet details interface: (" escape="
                                  js "}");
                              }
                            });
                        } {
                          /if}
</script>
{/block}
