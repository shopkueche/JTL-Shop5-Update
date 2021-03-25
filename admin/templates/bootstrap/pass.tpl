{include file='tpl_inc/header.tpl'}

<script type="text/javascript">
    {literal}
    $(document).ready(function () {
        $("input.field:first").focus();
    });
    {/literal}
</script>

<div class="vertical-center">
    <div class="container">
        <div id="login_wrapper">
            <div id="login_outer" class="card">
                <div class="card-body">
                    <p id="login_logo" class="text-center mb-4">
                        <a href="index.php">
                            <img class="brand-logo" width="120" height="38" src="{$templateBaseURL}gfx/JTL-Shop-Logo-rgb.png" alt="JTL-Shop">
                        </a>
                    </p>
                    {include file='snippets/alert_list.tpl'}
                    <form method="post" action="pass.php" class="form-horizontal" role="form">
                        {$jtl_token}
                        {if $step === 'prepare'}
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                </div>
                                <input type="text" tabindex="10" size="20" value="" id="admin_user_mail" name="mail" placeholder="{__('email')}" class="form-control" />
                            </div>
                        {elseif $step === 'confirm'}
                            <input type="hidden" name="fpwh" value="{$fpwh}" />
                            <input type="hidden" name="fpm" value="{$fpm}" />
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                </div>
                                <input type="password" tabindex="10" size="20" value="" id="user_pw" name="pw_new" placeholder="{__('newPassword')}" class="form-control" />
                            </div>
                            <div class="input-group mt-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-unlock"></i></span>
                                </div>
                                <input type="password" tabindex="10" size="20" value="" id="user_pw_confirm" name="pw_new_confirm" placeholder="{__('confirmNewPassword')}" class="form-control" />
                            </div>
                        {/if}
                        <p class="text-center mt-3">
                            <button type="submit" value="Passwort zurücksetzen" tabindex="100" class="btn btn-primary btn-block">{__('resetPassword')}</button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
