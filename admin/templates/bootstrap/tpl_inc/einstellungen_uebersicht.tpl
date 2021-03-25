{if isset($Sektion->cName)}
    {assign var=cTitel value=__('settings')|cat:': '|cat:$Sektion->cName}
{else}
    {assign var=cTitel value=__('settings')}
{/if}
{if isset($cSearch) && $cSearch|strlen  > 0}
    {assign var=cTitel value=$cSearch}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=__('settings') cBeschreibung=__('preferencesDesc') cDokuURL=__('preferencesURL')}
<div id="content">
    <div class="table-responsive">
        <table class="list table">
            <tbody>
            {foreach $Sektionen as $Sektion}
                <tr>
                    <td>{$Sektion->cName}</td>
                    <td>{$Sektion->anz} {__('settings')}</td>
                    <td>
                        <a href="einstellungen.php?kSektion={$Sektion->kEinstellungenSektion}" class="btn btn-primary">{__('configure')}</a>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
