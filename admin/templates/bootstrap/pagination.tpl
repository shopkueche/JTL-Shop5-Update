{if !isset($hash)}
    {assign var=hash value=''}
{/if}

{if !isset($cParams)}
    {assign var=cParams value=''}
{/if}

{if isset($oBlaetterNavi->nAktiv) && $oBlaetterNavi->nAktiv == 1}
    <div class="block clearall">
        <div class="pages text-left">
            <span class="pageinfo">
                {__('entry')}: <strong>{$oBlaetterNavi->nVon}</strong> - {$oBlaetterNavi->nBis} {__('of')} {$oBlaetterNavi->nAnzahl}
            </span>
            <ul class="pagination">
                {if $oBlaetterNavi->nAktuelleSeite == 1}
                    <li class="pagination-item"><span class="page">&laquo;</span></li>
                {else}
                    <li class="pagination-item"><a class="back" href="{$cUrl}?s{$cSite}={$oBlaetterNavi->nVoherige}{$cParams}{$hash}">&laquo;</a></li>
                {/if}

                {if $oBlaetterNavi->nAnfang != 0}
                    <li class="pagination-item">
                        <a class="page" href="{$cUrl}?s{$cSite}={$oBlaetterNavi->nAnfang}{$cParams}{$hash}">{$oBlaetterNavi->nAnfang}</a>
                    </li>
                    <li class="pagination-item"><span class="page">...</span></li>
                {/if}
                {foreach $oBlaetterNavi->nBlaetterAnzahl_arr as $Blatt}
                    <li class="pagination-item{if $oBlaetterNavi->nAktuelleSeite == $Blatt} active{/if}">
                        <a class="page" href="{$cUrl}?s{$cSite}={$Blatt}{$cParams}{$hash}">{$Blatt}</a>
                    </li>
                {/foreach}

                {if $oBlaetterNavi->nEnde != 0}
                    <li class="pagination-item"><span class="page">...</span></li>
                    <li class="pagination-item">
                        <a class="page" href="{$cUrl}?s{$cSite}={$oBlaetterNavi->nEnde}{$cParams}{$hash}">{$oBlaetterNavi->nEnde}</a>
                    </li>
                {/if}

                {if $oBlaetterNavi->nAktuelleSeite == $oBlaetterNavi->nSeiten}
                    <li class="pagination-item"><span class="page">&raquo;</span></li>
                {else}
                    <li class="pagination-item"><a class="next" href="{$cUrl}?s{$cSite}={$oBlaetterNavi->nNaechste}{$cParams}{$hash}">&raquo;</a></li>
                {/if}
            </ul>
        </div>
    </div>
{/if}
