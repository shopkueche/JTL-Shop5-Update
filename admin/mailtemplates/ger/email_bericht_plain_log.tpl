{if isset($oMailObjekt->oLogEntry_arr)}
    Log-Einträge ({$oMailObjekt->oLogEntry_arr|@count}):

    {foreach $oMailObjekt->oLogEntry_arr as $oLogEntry}
        [{$oLogEntry->dErstellt|date_format:'%d.%m.%Y %H:%M:%S'}] [{if $oLogEntry->nLevel === $smarty.const.JTLLOG_LEVEL_NOTICE}Hinweis{elseif $oLogEntry->nLevel === $smarty.const.JTLLOG_LEVEL_DEBUG}Debug{elseif $oLogEntry->nLevel >= $smarty.const.JTLLOG_LEVEL_ERROR}Fehler{/if}]
        {for $i=0 to $oLogEntry->cLog|strlen step 120}
            "{$oLogEntry->cLog|replace:"\n":' '|substr:$i:120}"
        {/for}
    {/foreach}
{/if}
