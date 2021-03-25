{include file='tpl_inc/header.tpl'}

{assign var=preferences value=__('preferences')}
{include file='tpl_inc/seite_header.tpl' cTitel="Skrill "|cat:$preferences}
<div id="content">
    {if $actionError != null}
        <div class="alert alert-danger">
            {if $actionError == 1}{__('mbEmailValidationError')}
            {elseif $actionError == 2}{__('mbSecretWordVeloctiyCheckExceeded')}
            {elseif $actionError == 3}{__('mbSecretWordValidationError')}
            {elseif $actionError == 99}{__('nofopenError')}
            {/if}
        </div>
    {/if}

    {if $showEmailInput}
        <div class="card">
            <div class="card-body">
                <p>{__('mbIntro')}</p>
                <p class="center" style="text-align: center">
                    <img src="{$templateBaseURL}/gfx/skrill_intro.jpg" alt="Skrill" />
                </p>
            </div>
            <div class="card-footer">
                {if $actionError != 99}
                    <form method="post" action="">
                        {$jtl_token}
                        <div class="input-group" style="margin-bottom: 0;">
                                <label class="input-group-addon" for="email">{__('mbEmailAddress')}:</label>
                            <input type="text" name="email" class="form-control" id="email" value="{if isset($smarty.post.email)}{$smarty.post.email}{/if}" />
                            <span class="input-group-btn">
                                <input class="btn btn-primary" type="submit" name="actionValidateEmail" value="{__('mbValidateEmail')}" />
                            </span>
                        </div>
                    </form>
                {/if}
            </div>
        </div>
    {else}
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('mbHeaderEmail')}</div>
            </div>
            <div class="card-body">
                <p>{{__('mbEmailValidationSuccess')}|sprintf:{$email}:{$customerId}}</p>
            </div>
            <div class="card-footer">
                <form method="post" action="">
                    {$jtl_token}
                    <button class="btn btn-danger" type="submit" name="actionDelete" value="{__('mbDelete')}"><i class="fas fa-trash-alt"></i> {__('mbDelete')}</button>
                </form>
            </div>
        </div>
        {*<div class="card">*}
            {*<div class="card-header">*}
                {*<div class="subheading1">{__('mbHeaderActivation')}</div>*}
            {*</div>*}
            {*{if $showActivationButton}*}
                {*<div class="card-body">*}
                    {*<p>{__('mbActivationText')} {__('mbActivationDescription')}</p>*}
                {*</div>*}
                {*<div class="card-footer">*}
                    {*<form method="post" action="">*}
                        {*{$jtl_token}*}
                        {*<input class="btn btn-primary" type="submit" name="actionActivate" value="{__('mbActivate')}" />*}
                    {*</form>*}
                {*</div>*}
            {*{else}*}
                {*<div class="card-body">*}
                    {*<p>{__('mbActivationRequestText')|sprintf:$activationRequest} {__('mbActivationDescription')}</p>*}
                {*</div>*}
            {*{/if}*}
        {*</div>*}
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('mbSecretWord')}</div>
            </div>
            {if $showSecretWordValidation}
                <div class="card-body">
                    <form method="post" action="">
                        {$jtl_token}
                        <span class="input-group">
                            <span class="input-group-addon">
                                <label for="secretWord">{__('mbSecretWord')}:</label>
                            </span>
                            <input class="form-control" type="text" name="secretWord" id="secretWord" value="{if isset($smarty.post.secretWord)}{$smarty.post.secretWord}{/if}" />
                            <span class="input-group-btn">
                                <input class="btn btn-primary" type="submit" name="actionValidateSecretWord" value="{__('mbValidateSecretWord')}" />
                            </span>
                        </span>
                    </form>
                </div>
            {else}
                <div class="card-body">
                    <p>{__('mbSecretWordValidationSuccess')}</p>
                </div>
                <div class="card-footer">
                    <form method="post" action="">
                        {$jtl_token}
                        <button class="btn btn-danger" type="submit" name="actionDeleteSecretWord" value="{__('mbDelete')}"><i class="fas fa-trash-alt"></i> {__('mbDelete')}</button>
                    </form>
                </div>
            {/if}
        </div>
    {/if}

    <div class="card">
        <div class="card-header">
            <div class="subheading1">{__('mbHeaderSupport')}</div>
        </div>
        <div class="card-body">
            {__('mbSupportText')}
        </div>
    </div>

</div>
{include file='tpl_inc/footer.tpl'}
