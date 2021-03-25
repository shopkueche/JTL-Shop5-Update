<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title></title>
</head>
<body>
{if isset($oMailObjekt->oLogEntry_arr)}
    <h2>Log-Einträge ({$oMailObjekt->oLogEntry_arr|@count}):</h2>
    {foreach $oMailObjekt->oLogEntry_arr as $oLogEntry}
        <h3>
            [{$oLogEntry->dErstellt|date_format:'%d.%m.%Y %H:%M:%S'}]
            {if $oLogEntry->nLevel === $smarty.const.JTLLOG_LEVEL_NOTICE}
                <span style="color:#00f;">[Hinweis]</span>
            {elseif $oLogEntry->nLevel === $smarty.const.JTLLOG_LEVEL_DEBUG}
                <span style="color:#fa0;">[Debug]</span>
            {elseif $oLogEntry->nLevel >= $smarty.const.JTLLOG_LEVEL_ERROR}
                <span style="color:#f00;">[Fehler]</span>
            {/if}
        </h3>
        <pre>{$oLogEntry->cLog}</pre>
    {/foreach}
{/if}
</body>
</html>