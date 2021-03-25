<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="JTL-Shop4-Systemcheck">
    <meta name="author" content="JTL-Software GmbH">
    <link rel="shortcut icon" href="favicon.ico">
    <title>Shop4-Wizard</title>
    <script src="https://code.jquery.com/jquery-latest.js"></script>
    <script>
        function ajaxCall(qid)
        {
            var questions = [];
            {foreach $wizard->getQuestions() as $questionId => $question}
            {if $question->getType() === 0}
            questions.push($('#question-{$questionId}').is(':checked'));
            {else}
            questions.push($('#question-{$questionId}').val());
            {/if}
            {/foreach}

            $.post('ajax.php',
                {
                    stepId: {$wizard->getStepId()},
                    questions: questions,
                },
                function (result) {
                    $('.question').hide();
                    result.forEach(function(questionId) {
                        $('#checkboxDiv-' + questionId).show();
                    });
                },
                'json'
            );
        }
    </script>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Open+Sans" media="screen, projection">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Ubuntu" media="screen, projection">
    <link href="layout/css/bootstrap.css" rel="stylesheet">
    <link href="layout/css/wizard.css" rel="stylesheet">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">
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

    <h1>Shop4-Wizard</h1>
    <h2>{$wizard->getTitle()}</h2>
    <form action="gui.php" method="post">
        <input type="hidden" name="stepId" value="{$wizard->getStepId()}">
        <div class="panel-body">
            {foreach $wizard->getQuestions() as $questionId => $question}
                <div class="input-group question col-lg-7"
                     id="checkboxDiv-{$questionId}"
                     style="margin: 15px">

                    <span class="input-group-addon "
                          id="header-{$questionId}"
                          style="width:65%; text-align: left">

                        <label for="">{$question->getText()}</label>
                    </span>

                    {if $question->getType() === 0}
                        <div class="input-group-addon">
                            <div class="checkboxDiv">
                                <input type="checkbox" class="checkbox checkboxDiv"
                                       onchange="ajaxCall()"
                                       id="question-{$questionId}"
                                       name="question-{$questionId}"

                                       {if $question->getValue()}checked{/if}>

                                <label for="question-{$questionId}"></label>
                            </div>
                        </div>
                    {elseif $question->getType() === 1}
                        <input type="text"
                               class="form-control"
                               name="question-{$questionId}"
                               id="question-{$questionId}"
                               value="{$question->getValue()}"
                               aria-describedby="header-{$questionId}">
                    {elseif $question->getType() === 2}
                        <input type="email"
                               class="form-control"
                               name="question-{$questionId}"
                               id="question-{$questionId}"
                               value="{$question->getValue()}"
                               aria-describedby="header-{$questionId}">
                    {/if}
                </div>
            {/foreach}
            {include file='step'|cat:$wizard->getStepId()|cat:'.tpl'}
        </div>
    </form>
    <div class="container">
        <div class="pull-right">
            <img src="layout/images/JTL-Shop-Logo.svg" alt="JTL-Shop 4">
        </div>
    </div>
</div>
</body>
</html>