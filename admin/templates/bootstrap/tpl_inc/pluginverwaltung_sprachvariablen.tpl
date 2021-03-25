<script type="text/javascript">
    function ackCheck(kPluginSprachvariable, kPlugin)
    {
        var bCheck = confirm('{__('sureResetLangVar')}');
        if (bCheck) {
            window.location.href = 'pluginverwaltung.php?pluginverwaltung_sprachvariable=1&kPlugin=' + kPlugin +
                '&kPluginSprachvariable=' + kPluginSprachvariable + '&token={$smarty.session.jtl_token}';
        }
    }
</script>
{include file='tpl_inc/seite_header.tpl' cTitel=__('pluginverwaltung') cBeschreibung=__('pluginverwaltungDesc')}
<div id="content">
    {if $plugin->getLocalization()->getLangVars()->count() > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_sprachvariable" value="1" />
            <input type="hidden" name="kPlugin" value="{$kPlugin}" />
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('pluginverwaltungLocales')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="table-responsive card-body">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="text-left">{__('pluginName')}</th>
                            <th class="text-left">{__('description')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $plugin->getLocalization()->getLangVars() as $var}
                            <tr>
                                <td><i>{$var->name}</i></td>
                                <td>{__($var->description)}</td>
                            </tr>
                            {foreach $pluginLanguages as $lang}
                                <tr>
                                    {assign var=cISOSprache value=strtoupper($lang->getIso())}
                                    <td>
                                        <label for="lv-{$var->id}_{$cISOSprache}">{$lang->getLocalizedName()}</label>
                                    </td>
                                    <td>
                                        {if isset($var->values[$cISOSprache]) && $var->values[$cISOSprache]|strlen > 0}
                                            {$value = $var->values[$cISOSprache]|escape:'html'}
                                        {else}
                                            {$value = ''}
                                        {/if}
                                        {if $var->type === 'textarea'}
                                            <textarea id="lv-{$var->id}_{$cISOSprache}" class="form-control" style="width: 350px;" name="{$var->id}_{$cISOSprache}" type="{$var->type}">{$value}</textarea>
                                        {else}
                                            <input id="lv-{$var->id}_{$cISOSprache}" class="form-control" style="width: 350px;" name="{$var->id}_{$cISOSprache}" type="{$var->type}" value="{$value}" />
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                            <tr>
                                <td>&nbsp;</td>
                                <td>
                                    <button onclick="ackCheck({$var->id}, {$kPlugin}); return false;" class="btn btn-danger">
                                        <i class="fal fa-exclamation-triangle"></i> {__('pluginLocalesStd')}
                                    </button>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <a class="btn btn-outline-primary btn-block" href="pluginverwaltung.php">
                                {__('cancelWithIcon')}
                            </a>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                                {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    {/if}
</div>
