{if $GALETTE_MODE eq 'DEV'} 
{assign var='page_title' value="{$page_title} ({$GALETTE_MODE})"} 
{/if}
{extends file='page.tpl'}
{block name='content'}
    {if $GALETTE_MODE eq 'DEV'} 
    {*debug*} 
{/if}
    <section id='desktop'>
        <header class="ui-state-default ui-state-active">
            {_T string='Activities'}
        </header>
        <div>
            <a id="transactions" href="{path_for name=ski_form_list}" title="{_T string='View Forms'}">{_T string='List Forms' domain='ski'}</a>
            <a id="contribs" href="{path_for name=ski_form}" title="{_T string='Add Form'}">{_T string='New Form' domain='ski'}</a>
            <a id="members" href="{path_for name=ski_members}" title="{_T string='List Members'}">{_T string='List Members' domain='ski'}</a>
            <a id="prefs" href="{path_for name=ski_family}" title="{_T string='Add Family'}">{_T string='New Family' domain='ski'}</a>
        </div>
    </section>

{/block}