{if $GALETTE_MODE eq 'DEV'} {assign var="page_title" value="{$page_title} ({$GALETTE_MODE})"} {/if}
{extends file="page.tpl"}
{block name="content"}
{if $GALETTE_MODE eq 'DEV'} {debug} {/if}
        <section id="desktop">
            <header class="ui-state-default ui-state-active">
                {_T string="Activities"}
            </header>
            <div>
                <a id="transactions" href="{path_for name="ski_form_list"}" title="{_T string="View Forms"}">{_T string="List Ski Forms"}</a>
                <a id="contribs" href="{path_for name="ski_form"}" title="{_T string="Add Form"}">{_T string="Add Ski Form "}</a>
                <a id="members" href="{path_for name="ski_members"}" title="{_T string="List Members"}">{_T string="List Ski Members "}</a>
                <a id="prefs" href="{path_for name="ski_preferences"}" title="{_T string="Add Form"}">{_T string="Ski preferences "}</a>
            </div>
        </section>

{/block}
