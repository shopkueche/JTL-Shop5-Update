{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('systemcheck') cBeschreibung=__('systemcheckDesc') cDokuURL=__('systemcheckURL')}
{include file='tpl_inc/systemcheck.tpl'}
<style type="text/css">
{literal}
    .phpinfo pre {margin: 0; font-family: monospace;}
    .phpinfo a, .phpinfo a:link {color: #000; text-decoration: none;}
    .phpinfo a:hover {text-decoration: none;}
    .phpinfo table {width: 100%; max-width: 100%; margin-bottom: 20px;}
    .phpinfo .center {text-align: center;}
    .phpinfo .center table {margin: 1em auto; text-align: left;}
    .phpinfo .center th {text-align: center !important;}
    .phpinfo td, .phpinfo th {padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #ddd;}
    .phpinfo h1 {font-size: 150%;}
    .phpinfo h2 {font-size: 125%;}
    .phpinfo .p {text-align: left;}
    .phpinfo .e {background-color: #f9f9f9; width: 300px; font-weight: bold;}
    .phpinfo .h {background-color: #ddd; font-weight: bold;}
    .phpinfo .v {max-width: 300px; overflow-x: auto;}
    .phpinfo .v i {color: #999;}
    .phpinfo img {float: right; border: 0;}
    .phpinfo hr {}
{/literal}
</style>

<div id="content">
    {if !empty($phpinfo)}
        <div class="phpinfo">{$phpinfo}</div>
    {/if}

    <div class="systemcheck">
        {*
        <div class="form-horizontal">
            <div class="page-header">
                <h1>Webhosting-Plattform</h1>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Provider:</label>
                <div class="col-sm-10">
                    <p class="form-control-static">
                        {if $platform->getProvider() === 'jtl'}
                            JTL-Software GmbH
                        {elseif $platform->getProvider() === 'hosteurope'}
                            HostEurope
                        {elseif $platform->getProvider() === 'strato'}
                            Strato
                        {elseif $platform->getProvider() === '1und1'}
                            1&amp;1
                        {elseif $platform->getProvider() === 'alfahosting'}
                            Alfahosting
                        {else}
                            <em>Unbekannt</em> ({$platform->getHostname()})
                        {/if}
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">PHP-Version:</label>
                <div class="col-sm-10">
                    <p class="form-control-static">{$platform->getPhpVersion()}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Document Root:</label>
                <div class="col-sm-10">
                    <p class="form-control-static">{$platform->getDocumentRoot()}</p>
                </div>
            </div>
            {if $platform->getProvider() === 'hosteurope' || $platform->getProvider() === 'strato' || $platform->getProvider() === '1und1'}
            <div class="form-group">
                <label class="col-sm-2 control-label">Hinweise:</label>
                <div class="col-sm-10">
                    <p class="form-control-static">
                        {$version = $platform->getPhpVersion()}
                        {if $platform->getProvider() === 'hosteurope'}
                            Sie können die PHP-Einstellungen im <a href="https://kis.hosteurope.de/">HostEurope-KIS</a> (<a href="https://kis.hosteurope.de/">https://kis.hosteurope.de/</a>) anpassen.
                        {elseif $platform->getProvider() === 'strato'}
                            Bitte laden Sie <a href="http://www.ioncube.com/loaders.php">hier</a> den ionCube-Loader herunter und entpacken Sie das Archiv nach {$platform->getDocumentRoot()} auf dem Server.<br>
                            Erstellen Sie auf dem Server eine Datei <code>php.ini</code> mit dem folgenden Inhalt:<br><br>
                        <pre>[Zend]
    zend_extension = {$platform->getDocumentRoot()}/ioncube/ioncube_loader_lin_{$version|substr:0:3}.so</pre>
                        {elseif $platform->getProvider() === '1und1'}
                            Bitte laden Sie <a href="http://www.ioncube.com/loaders.php">hier</a> den ionCube-Loader herunter und entpacken Sie das Archiv nach {$platform->getDocumentRoot()} auf dem Server.<br>
                            Erstellen Sie auf dem Server eine Datei <code>php.ini</code> mit dem folgenden Inhalt:<br><br>
                        <pre>[Zend]
    zend_extension = {$platform->getDocumentRoot()}/ioncube/ioncube_loader_lin_{$version|substr:0:3}.so</pre>
                        {/if}
                    </p>
                </div>
            </div>
            {/if}
        </div>
        *}

        {if !$passed}
            <div class="alert alert-warning">
                {__('noteImportantCheckSettings')}
            </div>
        {/if}
        
        {if $tests.recommendations|count > 0}
            <div class="page-header">
                <h1>{__('suggestedAdjustments')}</h1>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="col-xs-7">&nbsp;</th>
                            <th class="col-xs-3 text-center">{__('suggestedValue')}</th>
                            <th class="col-xs-2 text-center">{__('yourSystem')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $tests.recommendations as $test}
                        <tr>
                            <td>
                                <div class="test-name">
                                    <strong>{$test->getName()}</strong><br>
                                    {$description=$test->getDescription()}
                                    {if $description !== null && $description|strlen > 0}
                                        <p class="hidden-xs expandable">{$description}</p>
                                    {/if}
                                </div>
                            </td>
                            <td class="text-center">{$test->getRequiredState()}</td>
                            <td class="text-center">{call test_result test=$test}</td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {/if}

        {if $tests.programs|count > 0}
            <div class="page-header">
                <h1>{__('installedSoftware')}</h1>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="col-xs-7">{__('software')}</th>
                            <th class="col-xs-3 text-center">{__('requirements')}</th>
                            <th class="col-xs-2 text-center">{__('available')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $tests.programs as $test}
                            {if !$test->getIsOptional() || $test->getIsRecommended()}
                            <tr>
                                <td>
                                    <div class="test-name">
                                        <strong>{$test->getName()}</strong><br>
                                        {$description=$test->getDescription()}
                                        {if $description !== null && $description|strlen > 0}
                                            <p class="hidden-xs expandable">{$description}</p>
                                        {/if}
                                    </div>
                                </td>
                                <td class="text-center">{$test->getRequiredState()}</td>
                                <td class="text-center">{call test_result test=$test}</td>
                            </tr>
                            {/if}
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {/if}

        {if $tests.php_modules|count > 0}
            <div class="page-header">
                <h1>{__('neededPHPExtensions')}</h1>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="col-xs-10">{__('designation')}</th>
                            <th class="col-xs-2 text-center">{__('status')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $tests.php_modules as $test}
                            {if !$test->getIsOptional() || $test->getIsRecommended()}
                                <tr>
                                    <td>
                                        <div class="test-name">
                                            <strong>{$test->getName()}</strong><br>
                                            {$description = $test->getDescription()}
                                            {if $description !== null && $description|strlen > 0}
                                                <p class="hidden-xs expandable">{$description}</p>
                                            {/if}
                                        </div>
                                    </td>
                                    <td class="text-center">{call test_result test=$test}</td>
                                </tr>
                            {/if}
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {/if}

        {if $tests.php_config|count > 0}
            <div class="page-header">
                <h1>{__('needPHPSetting')}</h1>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="col-xs-7">{__('setting')}</th>
                            <th class="col-xs-3 text-center">{__('neededValue')}</th>
                            <th class="col-xs-2 text-center">{__('yourSystem')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $tests.php_config as $test}
                            {if !$test->getIsOptional() || $test->getIsRecommended()}
                                <tr>
                                    <td>
                                        <div class="test-name">
                                            <strong>{$test->getName()}</strong><br>
                                            {$description=$test->getDescription()}
                                            {if $description !== null && $description|strlen > 0}
                                                <p class="hidden-xs expandable">{$description}</p>
                                            {/if}
                                        </div>
                                    </td>
                                    <td class="text-center">{$test->getRequiredState()}</td>
                                    <td class="text-center">{call test_result test=$test}</td>
                                </tr>
                            {/if}
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {/if}
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
