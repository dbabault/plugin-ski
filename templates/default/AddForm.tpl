{if $GALETTE_MODE eq 'DEV'}
	{assign var="page_title" value="{$page_title} ({$GALETTE_MODE})"}
	{$file1="/home/galette/galette/data/logs/template-AddForm.txt"}
	{$output = print_r($form_status, true)}
	{file_put_contents($file1, "\nform_status : ",FILE_APPEND)}
	{file_put_contents($file1,  $output,FILE_APPEND)}
{/if}
{extends file="page.tpl"}
{block name="content"}
{if $GALETTE_MODE eq 'DEV'} {*debug*} {/if}

{if $form_status eq 'Done'}
	{$form_status='Open'}
{/if}
{if $form_status eq 'Create'}
	{$path="ski_do_add_form"}
	{$id=Create}
	{$bt=Create}
{elseif $form_status eq 'Open'}
	{if (in_array("SkiAdmin",$group))}
		{$path="ski_do_add_form"}
		{$id=Open}
		{$id1=lock}
		{$bt=Done}
		{$bt1=Lock}
	{else}
		{$path="ski_done_form"}
		{$id=Open}
		{$bt=Done}
	{/if}
{elseif $form_status eq 'Lock'}
	{$path="ski_do_add_form"}
	{if (in_array("SkiAdmin",$group))}
		{$id=Open}
		{$bt=Open}
	{else}
		{$id=Close}
		{$bt=Close}
	{/if}
{/if}

<form action="{path_for name=$path }" method="post" id={$id} enctype="multipart/form-data">
	<input type="hidden" name="form_id" value="{$form_id}">
							<input type="hidden" name="parent_id" value="{$parent_id}">
							<input type="hidden" name="period" value="{$period}">
							<input type="hidden" name="duration" value="{$duration}">
							<input type="hidden" name="date_begin" value="{$date_begin}">
							<input type="hidden" name="date_forecast" value="{$date_forecast}">
							<input type="hidden" name="date_end" value="{$date_end}">
	<div class="bigtable">
		<fieldset class="galette_form" id="general">
			<legend>{_T string="General informations" domain="ski"}</legend>

			{if $form_status eq 'Create'}
			<div>
				<p>
					<label for="Id">{_T string="Id" domain="ski"}</label>
					<b>{$form_id}</b>
				</p>
				<p>
					<label for="Status">{_T string="Status:" domain="ski"}</label>
					<b>{$form_status}</b><br>
				</p>
				<p>
					<label for="Period">{_T string="Period" domain="ski"}</label>
					<select name="period" id="period">
						<option value="null">{_T string="--- Select a period ---" domain="ski"}</option>
						{foreach from=$periods item=per}
						<option value="{$per}">{$per}</option>
						{/foreach}
					</select>
				</p>
				<p>
					<label for="Duration">{_T string="Duration" domain="ski"}</label>
					<select name="duration" id="duration">
						<option value="null">{_T string="--- Select a duration ---" domain="ski"}</option>
						{foreach from=$durations item=dur}
						<option value="{$dur}">{$dur}</option>
						{/foreach}
					</select>
				</p>
				<p>
					<label for="date_begin">{_T string="Begin date" domain="ski"}</label>
					<input type="text" name="date_begin" id="date_begin" maxlength="10" size="10" value={$date_begin} required="required" />
				</p>
				<p>
					<label for="date_forecast">{_T string="End date" domain="ski"}</label>
					<input type="text" name="date_forecast" id="date_forecast" maxlength="10" size="10" value={$date_forecast} required="required" />
				</p>
				<p>
					<label for="Comments">{_T string="Comments" domain="ski"}</label>
					<input type="text" id="comment" name="comment" size="80">
				</p>
			</div>
			{else}
			<div>
				<table width=100%>
					<thead>
						<tr>
							<th class="center">{_T string="Id" domain="ski"}</th>
							<th class="center">{_T string="Status:" domain="ski"}</th>
							<th class="center">{_T string="Period" domain="ski"}</th>
							<th class="center">{_T string="Duration" domain="ski"}</th>
							<th class="center">{_T string="Begin date" domain="ski"}</th>
							<th class="center">{_T string="Forecast date" domain="ski"}</th>
							<th class="center">{_T string="End date" domain="ski"}</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="center">{$form_id}</td>
							<td class="center">{$form_status}</td>
							<td class="center">{$period}</td>
							<td class="center">{$duration}</td>
							<td class="center">{$date_begin}</td>
							<td class="center">{$date_forecast}</td>
							<td class="center">{$date_end}</td>
						</tr>
					</tbody>
				</table>
				<br>

				<table width=100%>
					<thead>
						<tr>
							<th class="center">{_T string="Comments" domain="ski"}</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td> -{$comments}- </td>
						</tr>
					</tbody>
				</table>
				<br>
				{/if}
		</fieldset>
		<fieldset class="galette_form" id="general">
			<legend>{_T string="Member" domain="ski"}</legend>
			{if $form_status eq 'Create'}
				<div>
					<select name="parent_id" id="parent_id">
						<option value="null">{_T string="--- Select a Family ---" domain="ski"}</option>
						{foreach from=$members item=mbr}
						<option value="{$mbr['parent_id']}" >{$mbr['sname']}</option>
						{/foreach}
					</select>
				</div>
			{else}
				<table width=100%>
					<thead>
						<tr>
							<th class="left"><a href="{path_for name="objectslend_objects"}"> {_T string="Name" domain="ski"}</a></th>
							{foreach $categories as $cat}
								<th class="center">{$cat->name}</th>
								<th class="center">€</th>
							{/foreach}
						</tr>
					</thead>
					<tbody>
						{* debut table saisie adherents *}
						{foreach from=$members item=$mbr}
							{if $mbr['parent_id'] == $parent_id}
							{$id_adh=$mbr['id_adh']}
							{$sname=$mbr['sname']}
							<tr>
									<td class="left"> 
									<a href="{path_for name="ski_members" data=["option" => "edit", "value" => $parent_id] }"> 
									{$sname}</a> </td>
									{foreach $categories as $cat}
									{$category_id=$cat->category_id}
									{$rentobj=$objects[$form_rents[$form_id][$id_adh][$category_id]['object_id']]['name']}
									{foreach from=$form_rents item=fr key=fid}
										{$aid=$form_rents[$fid][$id_adh][$category_id]}
											{foreach from=$aid item=b key=c}
												{if $c == 'object_id'}
													{$serial=$objects[$b]['serial_number']}
													{if $objects[$b]['state'] == 'used'}
														{$rentobj=$objects[$b]['name1']}
													{elseif $objects[$b]['state'] == 'during'}
														{assign var=rentobj value=$objects[$b]['name']}
														{assign var=rentobj value=$rentobj|cat:"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;loué sur fiche "}
														{assign var=rentobj value=$rentobj|cat:$objects[$b]['used']}
													{else}
														{$rentobj=$objects[$b]['name']}
														{assign var=rentobj value=$rentobj|cat:"\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;loué sur fiche "}
														{assign var=rentobj value=$rentobj|cat:$objects[$b]['used']}
													{/if}
												{/if}
											{/foreach}
										{if $fid == $form_id}{break}{/if}
									{/foreach}
									<td class="center">
										<select name="object" id="object" style="width: 90% " class="center">
											<option value="null">
												{*if (isset($form_rents[$form_id][$id_adh][$category_id]['object_id'])) *}
													{*$obj_id=$form_rents[$id_adh][$category_id]['object_id']*}
													{if $serial != "000"}
													{$rentobj}
												{else}
													--
												{/if}
											</option>
											{foreach from=$objects item=obj}
											{$object_category_id=$obj['category_id']} {$object_id=$obj['object_id']} {$object_name=$obj['name']}
										
												{if $category_id eq $object_category_id}
													{assign var=mbr1 value="object="}
													{assign var=mbr1 value=$mbr1|cat:"form_id="|cat:$form_id}
													{assign var=mbr1 value=$mbr1|cat:":id_adh="|cat:$id_adh}
													{assign var=mbr1 value=$mbr1|cat:":sname="|cat:$sname}
													{assign var=mbr1 value=$mbr1|cat:":category_id="|cat:$category_id}
													{assign var=mbr1 value=$mbr1|cat:":object_id="|cat:$object_id}
													{assign var=mbr1 value=$mbr1|cat:":object_name="|cat:$obj['name1']}
													{assign var=mbr1 value=$mbr1|cat:":date_begin="|cat:$date_begin}
													{assign var=mbr1 value=$mbr1|cat:":date_forecast="|cat:$date_forecast}
													{assign var=mbr1 value=$mbr1|cat:":date_end="|cat:$date_end}
													{assign var=mbr1 value=$mbr1|cat:":period="|cat:$period}
													{assign var=mbr1 value=$mbr1|cat:":duration="|cat:$duration}
													{assign var=mbr1 value=$mbr1|cat:":before="|cat:$before}
													{assign var=mbr1 value=$mbr1|cat:":after="|cat:$after}
													{assign var=mbr1 value=$mbr1|cat:":during="|cat:$during}
													<option value="{$mbr1}">
														{$obj['name']}
													</option>
												{/if}
											{/foreach}
										</select>
									</td>
									<td></td>
									{/foreach}
							</tr>
							{/if}
						{/foreach}
						{* fin table saisie adherents *}
					</tbody>
				</table>
			{/if}
			<br>
		</fieldset>
	</div>
	<div class="button-container" id="button_container">
		<input type="submit" id="btnsave" name="form_status" value="{$bt}">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		{if $bt1 == Lock}
		<input type="submit" id="btnsave" name="form_status" value="{$bt1}">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		{/if}
		<a href="{path_for name="ski_form"}" class="button" id="btncancel">{_T string="Cancel"}</a>
	</div>
</form>
{/block}

{block name="javascripts"}
<script type="text/javascript">
	$(function() {
		var $nmdt1 = $('#date_begin');
		var $nmdt2 = $('#date_forecast');
		if ($nmdt1.length > 0) {
			_collapsibleFieldsets();
			$.datepicker.setDefaults($.datepicker.regional['{$galette_lang}']);
			$('#date_begin').datepicker({
				beforeShowDay: function(date) {
					var day = date.getDay();
					return [(day == 3), ""]; // ! Wednesday.
				},
				changeMonth: true,
				changeYear: true,
				showOn: 'button',
				minDate: '-0d',
				//dateFormat: "yy-mm-dd",
				buttonText: '<i class="far fa-calendar-alt"></i> <span class="sr-only">{_T string="Select a date" escape="js"}</span>',
				onSelect: function(date) {
					$nmdt1 = $('#date_begin').datepicker('getDate');
					$nmdt1.setDate($nmdt1.getDate() + 7);
					$("#date_forecast").datepicker("option", "minDate", $nmdt1);
				}
			});
		}
		if ($nmdt2.length > 0) {
			$('#date_forecast').datepicker({
				beforeShowDay: function(date) {
					var day = date.getDay();
					return [(day == 3), ""]; // ! Wednesday.
				},
				changeMonth: true,
				changeYear: true,
				showOn: 'button',
				//dateFormat: "yy-mm-dd",
				buttonText: '<i class="far fa-calendar-alt"></i> <span class="sr-only">{_T string="Select a date" escape="js"}</span>',
				minDate: $("#date_forecast").datepicker("getDate")
			});
		}
	});

	$('select').on('change', function() {
		var $obj = $('#object_name');
		//var res = this.value.split(":");
    var res1= this.value;
    if (this.name === "object") {
			//window.confirm("{_T string="Do you want to lent "}" + $obj + "?" );
      $.ajax({
        url: '{path_for name="ski_do_add_object" domain="ski"}',
        type: "post",
        data: res1,
				error: function() {
          alert("{_T string="An error occurred storing "}" + $obj);
          window.location.reload(true);
        },
        success: function() {
          window.location.reload(true);
        }
			});

			//window.location.reload(true);
		}
	});
</script>
{/block}
