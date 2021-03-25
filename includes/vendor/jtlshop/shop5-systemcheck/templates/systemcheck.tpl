<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="JTL-Shop-Systemcheck">
    <meta name="author" content="JTL-Software GmbH">
    <link rel="shortcut icon" href="favicon.ico" />

    <title>JTL-Shop-Systemcheck</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Open+Sans" media="screen, projection">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Ubuntu" media="screen, projection">
    <link href="layout/css/bootstrap.css" rel="stylesheet">
    <link href="layout/css/systemcheck.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>

<body>
<div class="container">
    <a class="logo" id="logo-headline">
        <img src="layout/images/JTL-beLogo.png" alt="JTL-Software GmbH" style="height:55px;margin-left:5px;">
    </a>
    <div class="navbar navbar-inverse" role="navigation" id="nav-headline">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li><a href="http://www.jtl-software.de/">JTL-Software GmbH</a></li>
                <li><a href="https://guide.jtl-software.de/jtl/JTL-Shop">Wiki</a></li>
                <li><a href="https://forum.jtl-software.de/">JTL-Supportforum</a></li>
            </ul>
        </div>
    </div>
</div>
<div class="container">
    <h1>JTL-Shop-Systemcheck</h1>

    <div class="form-horizontal">
        <h4>Webhosting-Plattform</h4>
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
                        <em>unbekannt</em> (Hostname: {$platform->getHostname()})
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
                        {if $platform->getProvider() === 'hosteurope'}
                            Sie können die PHP-Einstellungen im <a href="https://kis.hosteurope.de/">HostEurope-KIS</a> (<a href="https://kis.hosteurope.de/">https://kis.hosteurope.de/</a>) anpassen.
                        {elseif $platform->getProvider() === 'strato'}
                            Bitte laden Sie <a href="http://www.ioncube.com/loaders.php">hier</a> den ionCube-Loader herunter und entpacken Sie das Archiv nach {$platform->getDocumentRoot()} auf dem Server.<br>
                            Erstellen Sie auf dem Server eine Datei <code>php.ini</code> mit dem folgenden Inhalt:<br><br>
                    <pre>[Zend]
zend_extension = {$platform->getDocumentRoot()}/ioncube/ioncube_loader_lin_{$platform->getPhpVersion()|substr:0:3}.so</pre>
                    {elseif $platform->getProvider() === '1und1'}
                        Bitte laden Sie <a href="http://www.ioncube.com/loaders.php">hier</a> den ionCube-Loader herunter und entpacken Sie das Archiv nach {$platform->getDocumentRoot()} auf dem Server.<br>
                        Erstellen Sie auf dem Server eine Datei <code>php.ini</code> mit dem folgenden Inhalt:<br><br>
                    <pre>[Zend]
zend_extension = {$platform->getDocumentRoot()}/ioncube/ioncube_loader_lin_{$platform->getPhpVersion()|substr:0:3}.so</pre>
                    {/if}
                    </p>
                </div>
            </div>
        {/if}
    </div>

    {if $tests.programs|count > 0}
        <table class="table table-striped table-hover">
            <caption>Installierte Software</caption>
            <thead>
            <tr>
                <th class="col-xs-7">Software</th>
                <th class="col-xs-3">Voraussetzung</th>
                <th class="col-xs-2">Ihr System</th>
            </tr>
            </thead>
            <tbody>
            {foreach $tests.programs as $progTest}
                {if !$progTest->getIsOptional() || $progTest->getIsRecommended()}
                    <tr>
                        <td>
                            <div class="test-name">
                                <strong>{$progTest->getName()}</strong><br>
                                <p class="hidden-xs expandable">{$progTest->getDescription()}</p>
                            </div>
                        </td>
                        <td>{$progTest->getRequiredState()}</td>
                        <td>{getResults test=$progTest}</td>
                    </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
    {/if}

    {if $tests.php_modules|count > 0}
        <table class="table table-striped table-hover">
            <caption>Benötigte PHP-Erweiterungen und -Funktionen:</caption>
            <thead>
            <tr>
                <th class="col-xs-10">Extension/Funktion</th>
                <th class="col-xs-2">Ihr System</th>
            </tr>
            </thead>
            <tbody>
            {foreach $tests.php_modules as $test}
                {if !$test->getIsOptional() || $test->getIsRecommended()}
                    <tr>
                        <td>
                            <div class="test-name">
                                <strong>{$test->getName()}</strong><br>
                                <p class="hidden-xs expandable">{$test->getDescription()}</p>
                            </div>
                        </td>
                        <td>{getResults test=$test}</td>
                    </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
    {/if}

    {if $tests.php_config|count > 0}
        <table class="table table-striped table-hover">
            <caption>Benötigte PHP-Einstellungen:</caption>
            <thead>
            <tr>
                <th class="col-xs-7">Einstellung</th>
                <th class="col-xs-3">Benötigter Wert</th>
                <th class="col-xs-2">Ihr System</th>
            </tr>
            </thead>
            <tbody>
            {foreach $tests.php_config as $test}
                {if !$test->getIsOptional() || $test->getIsRecommended()}
                    <tr>
                        <td>
                            <div class="test-name">
                                <strong>{$test->getName()}</strong><br>
                                <p class="hidden-xs expandable">{$test->getDescription()}</p>
                            </div>
                        </td>
                        <td>{$test->getRequiredState()}</td>
                        <td>{getResults test=$test}</td>
                    </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
    {/if}
    {if $tests.recommendations|count > 0}
        <table class="table table-striped table-hover">
            <caption>Empfohlene Anpassungen:</caption>
            <thead>
            <tr>
                <th class="col-xs-7">&nbsp;</th>
                <th class="col-xs-3">Empfohlener Wert</th>
                <th class="col-xs-2">Ihr System</th>
            </tr>
            </thead>
            <tbody>
            {foreach $tests.recommendations as $test}
                <tr>
                    <td>
                        <div class="test-name">
                            <strong>{$test->getName()}</strong><br>
                            <p class="hidden-xs expandable">{$test->getDescription()}</p>
                        </div>
                    </td>
                    <td>{$test->getRequiredState()}</td>
                    <td>{getResults test=$test}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {/if}
</div>

<div class="container">
    <div class="pull-right">
        <img src="layout/images/JTL-Shop-Logo.svg" alt="JTL-Shop 4">
    </div>
</div>

<script src="layout/js/jquery.js"></script>
<script src="layout/js/jquery.expander.js"></script>
<script src="layout/js/bootstrap.min.js"></script>
<script src="layout/js/init.js"></script>
</body>
</html>
