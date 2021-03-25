<p>
    The <b>{$portlet->getMissingClass()}</b> Portlet is either not installed or currently just set inactive.
</p>
<p>
    Please install and activate the missing Plugin
    {if $portlet->getInactivePlugin() !== null}
        <b>{$portlet->getInactivePlugin()->getPluginID()}</b>
    {/if}
    that provides this Portlet!
</p>