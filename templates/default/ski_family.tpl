{if $GALETTE_MODE eq 'DEV'}
    {assign var="page_title" value="{$page_title} ({$GALETTE_MODE})"}
{/if}
{extends file="page.tpl"}
{block name="content"}
    {$parent_n=$members[$pid]['nom_adh']}
    {$parent_s=$members[$pid]['prenom_adh']}
    {$nom_adh=$members[$pid]['nom_adh']}
    {$prenom_adh=$members[$pid]['prenom_adh']}
    {$parent_id=$members[$pid]['id_adh']}
    {$adresse_adh=$members[$pid]['adresse_adh']}
    {$cp_adh=$members[$pid]['cp_adh']}
    {$ville_adh=$members[$pid]['ville_adh']}
    {$email_adh=$members[$pid]['email_adh']}
    {$tel_adh=$members[$pid]['tel_adh']}
    {$sexe_adh=$members[$pid]['sexe_adh']}
    {$gsm_adh=$members[$pid]['gsm_adh']}
    {$info_adh=$members[$pid]['info_adh']}
    {$id='store'}
    <div class="bigtable">
        {$path="ski_store_family"}
        <form action="{path_for name=$path}" method="post" id={$id} enctype="multipart/form-data">
            {foreach from=$members[$pid] item=value key=type}
                <input type="hidden" name="members[{$pid}][{$type}]" value="{$value}">
            {/foreach}
            <fieldset class="galette_form" id="general">
                <legend>{_T string='General informations' domain='ski'}</legend>
                <div>
                    {if $parent_n == '?' }
                        {$email_adh='?'}
                        <p><label for="Name">{_T string='Name"'domain='ski'}</label>
                            <input type="text" id="nom_adh" name="members[{$parent_id}][nom_adh]" value="{$nom_adh}" size="40"></p>
                        <p><label for="Surname">{_T string='First Name' domain='ski'}</label>
                            <input type="text" id="prenom_adh" name="members[{$parent_id}][prenom_adh]" value="{$prenom_adh}" size="40"></p>
                    {else}
                        <p><label for="Family">{_T string='Parent' domain='ski'}</label><b>{$parent_id} </b>({$parent_n} {$parent_s})</p>
                    {/if}
                    <p><label for="Address">{_T string='Address' domain='ski'}</label>
                        <input type="text" id="adresse_adh" name="members[{$parent_id}][adresse_adh]" value="{$adresse_adh}" size="40"></p>
                    <p><label for="zip">{_T string='Zip - Town' domain='ski'}</label>
                        <input type="text" id="cp_adh" name="members[{$parent_id}][cp_adh]" value="{$cp_adh}" size="40"></p>
                    <p><label for="city">{_T string='City' domain='ski'}</label>
                        <input type="text" id="ville_adh" name="members[{$parent_id}][ville_adh]" value="{$ville_adh}" size="40"></p>
                    <p><label for="Email">{_T string='Email' domain='ski'}</label>
                        <input type="text" id="email_adh" name="members[{$parent_id}][email_adh]" value="{$email_adh}" size="40"></p>
                    <p><label for="Phone">{_T string='Phone' domain='ski'}</label>
                        <input type="text" id="tel_adh" name="members[{$parent_id}][tel_adh]" value="{$tel_adh}" size="40"></p>
                    <p><label for="GSM">{_T string='GSM' domain='ski'}</label>
                        <input type="text" id="gsm_adh" name="members[{$parent_id}][gsm_adh]" value="{$gsm_adh}" size="40"></p>
                    <p><label for="Comment">{_T string='Comment' domain='ski'}</label>
                        <input type="text" id="info_adh" name="members[{$parent_id}][info_adh]" value="{$info_adh}" size="40"></p>
                </div>
                <div class="button-container" id="button_container">
                    <input type="submit" id="btnsave" name="save" value="Save">
                </div>
                <br>
            </fieldset>
        </form>
        <br>
        {if $parent_n != '?' }
            <fieldset class="galette_form" id="general">
                <legend>{_T string='Member' domain='ski'}</legend>
                <form action="{path_for name=$path}" method="post" id={$id} enctype="multipart/form-data">
                    <div class="left" id="button_container">
                        <br>
                        <input type="submit" id="btnsave" name="plus" value="Nouveau membre">
                        <br>
                    </div>
                    <br>
                </form>
                <form action="{path_for name=$path}" method="post" id={$id} enctype="multipart/form-data">
                    {foreach from=$members item=member key=ordre}
                        {foreach from=$member item=value key=k}
                            <input type="hidden" name="members[{$ordre}][{$k}]" value="{$value}">
                        {/foreach}
                    {/foreach}
                    <table width=90%>
                        <thead>
                            <tr>
                                <th class="center">Id</th>
                                <th class="center">{_T string='Name'}</th>
                                <th class="center">{_T string='First name'}</th>
                                <th class="center">{_T string='Gender:'}</th>
                                <th class="center">{_T string='Birth date' domain='ski'}</th>
                                <th class="center">Age</th>
                                <th class="center">€</th>
                                {foreach $dynval item=dyn key=id_dyn }
                                    <th class="center">{$dyn['fname']} ({$id_dyn})</th>
                                {/foreach}
                                <th class="actions_row">{_T string='Actions'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {* debut table saisie adherents *}
                            {foreach from=$members item=member key=id_member}
                                {$mid=(int)$member['id_adh']}
                                {$ddn_adh=$member['ddn_adh']}
                                {$prenom_adh=$member['prenom_adh']}
                                {$nom_adh=$member['nom_adh']}
                                {$sexe_adh=$member['sexe_adh']}
                                <tr>
                                    <td class="left">
                                        <a href="{path_for name=$path data=['action'=>'edit','id'=>$mid]}">
                                            {$mid} </a>
                                    </td>
                                    <td class="left">
                                        <input type="text" id="nom_adh" name="members[{$mid}][nom_adh]" value="{$nom_adh}" size="10">
                                    </td>
                                    <td class="left">
                                        <input type="text" id="prenom_adh" name="members[{$mid}][prenom_adh]" value="{$prenom_adh}" size="10">
                                    </td>
                                    <td class="left">
                                        <select name="members[{$mid}][sexe_adh]" style="width: 90% " class="center">
                                            <option value="null">
                                                {if $sexe_adh eq 1}M{/if}
                                                {if $sexe_adh eq 2}F{/if}
                                            </option>
                                            <option value="1">M</option>
                                            <option value="2">F</option>
                                        </select>
                                    </td>
                                    <td class="center">
                                        <input type="text" id="ddn_adh" name="members[{$mid}][ddn_adh]" value="{$ddn_adh}" size="10">
                                    </td>
                                    <td class="center">{$member['age']}</td>
                                    <td class="center"></td>
                                    {foreach $dynval item=dyn key=id_dyn }
                                        {$fname=$dyn['fname']}
                                        {$values=$dyn['values']}
                                        {$fval=$dynadh[$mid][$id_dyn]['fval']}
                                        {$ftext=$dynadh[$mid][$id_dyn]['ftext']}
                                        {assign var=field value="info_field_"|cat:$id_dyn|cat:"_1"}
                                        {if $ftext == ''}
                                            {$ftext='?'}
                                            {$fval='0' }
                                        {/if}
                                        <td class="center">
                                            {if $ftext == 'O' || $ftext=='N'}
                                                {if $ftext == 'O' }
                                                    {'&#9989;'}
                                                {/if}
                                            {else}
                                                <select name="members[{$mid}][{$field}]" style="width: 90% " class="center">
                                                    <option value="null">
                                                        {if $fval ne '' }
                                                            {$ftext}
                                                        {else}
                                                            ?
                                                        {/if}
                                                    </option>
                                                    {foreach from=$values item=val key=id_val}
                                                        <option value="{$id_val}">
                                                            {$val}
                                                        </option>
                                                    {/foreach}
                                                </select>
                                            {/if}
                                        </td>
                                    {/foreach}
                                    <td class="rclass center nowrap actions_row">
                                        <a href="{path_for name=$path data=['action'=>'edit','id'=>$mid]}" class="tooltip action">
                                            <i class="fas fa-user-edit fa-fw" aria-hidden="true"></i>
                                            <span class="sr-only">{_T string="%membername: edit informations" pattern="/%membername/" replace=$prenom_adh}</span>
                                        </a>
                                        <a href="{path_for name=$path data=['id'=>$mid]}" class="delete tooltip">
                                            <i class="fas fa-user-times fa-fw" aria-hidden="true"></i>
                                            <span class="sr-only">{_T string="%membername: remove from database" pattern="/%membername/" replace=$prenom_adh}</span>
                                        </a>
                                    </td>
                                </tr>
                            {/foreach}
                            {* fin table saisie adherents *}
                        </tbody>
                    </table>
                    <br>
                    <div class="button-container" id="button_container">
                        <input type="submit" id="btnsave" name="save" value="Save">
                    </div>
                    <br>
            </fieldset>
        {/if}
    </div>
    </form>
{/block}