{if is_object($oNews_arr) && !empty($oNews_arr->channel->item)}
    <ul class="linklist">
        {strip}
            {foreach $oNews_arr->channel->item as $oNews}
                <li>
                    <p>
                        <a class="" href="{$oNews->link|urldecode}" target="_blank" rel="noopener">
                            <span class="date label label-default pull-right">{$oNews->pubDate|date_format:'%d.%m.%Y'}</span>{$oNews->title}
                        </a>
                    </p>
                </li>
            {/foreach}
        {/strip}
    </ul>
{else}
    <div class="widget-container"><div class="alert alert-error">{__('noDataAvailable')}</div></div>
{/if}
