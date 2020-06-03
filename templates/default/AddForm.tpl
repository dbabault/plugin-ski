{if $GALETTE_MODE eq 'DEV'} {assign var="page_title" value="{$page_title} ({$GALETTE_MODE})"} {/if}
{extends file="page.tpl"}
{block name="content"}
{if $GALETTE_MODE eq 'DEV'} {debug} {/if}

{if $form_status eq 'Create'}
	{$path="ski_do_add_form"}
	{$id=Create}
	{$bt=Create}
{elseif $form_status eq 'Open'}
	{if (in_array("SkiAdmin",$group))}
		{$path="ski_do_add_form"}
		{$id=lock}
		{$bt=Lock}
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

{*$file = fopen("/home/daniel/fichier.txt", "a")*}
{*fwrite($file, "\n ---------------AddForm.tpl ")*}
{*fwrite($file, "\n form_status: $form_status")*}
{*fwrite($file, "\n 2---------------AddForm.tpl \n")*}

<form action="{path_for name=$path }" method="post" id={$id} enctype="multipart/form-data">
	<input type="hidden" name="form_id" value="{$form_id}">
	<input type="hidden" name="test" value="test">
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
					<option value="{$mbr['parent_id']}">{$mbr['sname']}</option>
					{/foreach}
				</select>
			</div>
			{*elseif $form_status eq 'Open'*}
			{else}
			<table width=100%>
				<thead>
					<tr>
						<th class="left"><a href="{path_for name="objectslend_objects"}"> {_T string="Name" domain="ski"}</a></th>
						{foreach $categories as $cat}
						<th class="center">{$cat->name}</th>
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
						<td class="left"> <a href="{path_for name="member" data=["id"=> $id_adh]}">{$sname}</a> </td>
						{foreach $categories as $cat}
						{$category_id=$cat->category_id}
						<td class="center">
							<select name="object" id="object" style="width: 90% " class="center">
								<option value="null">
									{if (isset($form_rents[$id_adh][$category_id]['object_id'])) }
									{$obj_id=$form_rents[$id_adh][$category_id]['object_id']}{$objects[$obj_id]['name']}
									{else}
									--- Select an Object ---
									{/if}
								</option>
								{foreach from=$objects item=obj}
								{$object_category_id=$obj['category_id']} {$object_id=$obj['object_id']} {$object_name=$obj['name']}
								{if $category_id eq $object_category_id}
								<option value="object:{$form_id}:{$id_adh}:{$sname}:{$category_id}:{$object_id}:{$object_name}:{$date_begin}:{$date_forecast}:{$date_end}:{$period}:{$duration}">
									{$obj['name']}
								</option>
								{/if}
								{/foreach}
							</select>
						</td>
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
		//var msg=this.value;
		var res = this.value.split(":");
		if (res[0] === "object") {
			//	<option value="object:{$form_id}:{$mbr['id_adh']}:{$mbr['sname']}:{$obj['category_id']}:{$obj['object_id']}:{$obj['name']} ">{$obj['name']}</option>
			var _form_id = res[1];
			var _id_adh = res[2];
			var _sname = res[3];
			var _category_id = res[4];
			var _object_id = res[5];
			var _name = res[6];
			var _date_begin = res[7];
			var _date_forecast = res[8];
			var _date_end = res[9];
			var _period = res[10];
			var _duration = res[11];
			var _form_status = res[12];
			$.ajax({
				url: '{path_for name="ski_do_add_object" domain="ski"}',
				type: "post",
				data: {
					form_id: _form_id,
					id_adh: _id_adh,
					sname: _sname,
					category_id: _category_id,
					object_id: _object_id,
					name: _name,
					date_begin: _date_begin,
					date_forecast: _date_forecast,
					date_end: _date_end,
					period: _period,
					duration: _duration,
					form_status: _form_status
				},

			});
			window.confirm(_sname);
			window.location.reload(true);
		}
	});
</script>
{/block}
