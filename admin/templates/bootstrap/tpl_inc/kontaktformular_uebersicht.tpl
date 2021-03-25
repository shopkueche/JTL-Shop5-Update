{include file='tpl_inc/seite_header.tpl' cTitel=__('configureContactform') cBeschreibung=__('contanctformDesc') cDokuURL=__('cURL')}
<div id="content">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || $cTab === 'config'} active{/if}" data-toggle="tab" role="tab" href="#config">
                        {__('settings')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'subjects'} active{/if}" data-toggle="tab" role="tab" href="#subjects">
                        {__('subjects')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'content'} active{/if}" data-toggle="tab" role="tab" href="#contents">
                        {__('contents')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="config" class="tab-pane fade {if $cTab === '' || $cTab === 'config'} active show{/if}">
                <form name="einstellen" method="post" action="kontaktformular.php">
                    {$jtl_token}
                    <input type="hidden" name="einstellungen" value="1" />
                    <div class="settings">
                        <div class="subheading1">{__('settings')}</div>
                        <hr class="mb-3">
                        <div>
                            {foreach $Conf as $cnf}
                                {if $cnf->cConf === 'Y'}
                                    <div class="form-group form-row align-items-center">
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$cnf->cWertName}">{$cnf->cName}:</label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            {if $cnf->cInputTyp === 'selectbox'}
                                                <select name="{$cnf->cWertName}" id="{$cnf->cWertName}" class="custom-select combo">
                                                    {foreach $cnf->ConfWerte as $wert}
                                                        <option value="{$wert->cWert}" {if $cnf->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                                    {/foreach}
                                                </select>
                                            {else}
                                                <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                                            {/if}
                                        </div>
                                        {if !empty($cnf->cBeschreibung)}
                                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=$cnf->cBeschreibung}</div>
                                        {/if}
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                        <div class="card-footer save-wrapper">
                            <div class="row">
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <button type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                                        {__('saveWithIcon')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div id="subjects" class="tab-pane fade {if $cTab === 'subjects'} active show{/if}">
                <div class="alert alert-info">{__('contanctformSubjectDesc')}</div>
                <div>
                    <div class="subheading1">{__('subjects')}</div>
                    <hr class="mb-3">
                    <div>
                        <div class="table-responsive">
                            <table class="list table">
                                <thead>
                                <tr>
                                    <th class="text-left">{__('subject')}</th>
                                    <th class="text-left">{__('mail')}</th>
                                    <th>{__('customerGroup')}</th>
                                    <th class="text-center">{__('actions')}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $Betreffs as $Betreff}
                                    <tr>
                                        <td>
                                            <a href="kontaktformular.php?kKontaktBetreff={$Betreff->kKontaktBetreff}&token={$smarty.session.jtl_token}">{$Betreff->cName}</a>
                                        </td>
                                        <td>{$Betreff->cMail}</td>
                                        <td>{$Betreff->Kundengruppen}</td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="kontaktformular.php?del={$Betreff->kKontaktBetreff}&token={$smarty.session.jtl_token}"
                                                   class="btn btn-link px-2"
                                                   title="{__('delete')}"
                                                   data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-trash-alt"></span>
                                                        <span class="fas fa-trash-alt"></span>
                                                    </span>
                                                </a>
                                                <a href="kontaktformular.php?kKontaktBetreff={$Betreff->kKontaktBetreff}&token={$smarty.session.jtl_token}"
                                                   class="btn btn-link px-2"
                                                   title="{__('modify')}"
                                                   data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-edit"></span>
                                                        <span class="fas fa-edit"></span>
                                                    </span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer save-wrapper">
                        <div class="row">
                            <div class="ml-auto col-sm-6 col-xl-auto">
                                <a class="btn btn-primary btn-block" href="kontaktformular.php?neu=1&token={$smarty.session.jtl_token}">
                                    <i class="fa fa-share"></i> {__('newSubject')}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="contents" class="tab-pane fade {if $cTab === 'content'} active show{/if}">
                <form name="einstellen" method="post" action="kontaktformular.php">
                    {$jtl_token}
                    <input type="hidden" name="content" value="1" />
                    <div>
                        <div class="subheading1">{__('contents')}</div>
                        <hr class="mb-3">
                        <div>
                            <div class="settings">
                                {foreach $availableLanguages as $language}
                                    {assign var=cISO value=$language->getIso()}
                                    {assign var=cISOcat value=$cISO|cat:'_titel'}
                                    <div class="form-group form-row align-items-center">
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="cTitle_{$cISO}">
                                            {__('title')} ({$language->getLocalizedName()}):
                                        </label>
                                        <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            <input class="form-control" type="text" name="cTitle_{$cISO}" id="cTitle_{$cISO}" value="{if !empty($Content[$cISOcat])}{$Content[$cISOcat]}{/if}" tabindex="1" />
                                        </span>
                                    </div>
                                {/foreach}
                                {foreach $availableLanguages as $language}
                                    {assign var=cISO value=$language->getIso()}
                                    {assign var=cISOcat value=$cISO|cat:'_oben'}
                                    <div class="form-group form-row align-items-center">
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="cContentTop_{$cISO}">
                                            {__('topContent')} ({$language->getLocalizedName()}):
                                        </label>
                                        <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            <textarea class="ckeditor form-control" name="cContentTop_{$cISO}" id="cContentTop_{$cISO}">{if !empty($Content[$cISOcat])}{$Content[$cISOcat]}{/if}</textarea>
                                        </span>
                                    </div>
                                {/foreach}
                                {foreach $availableLanguages as $language}
                                    {assign var=cISO value=$language->getIso()}
                                    {assign var=cISOcat value=$cISO|cat:'_unten'}
                                    <div class="form-group form-row align-items-center">
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="cContentBottom_{$cISO}">
                                            {__('bottomContent')} ({$language->getLocalizedName()}):
                                        </label>
                                        <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            <textarea class="ckeditor form-control" name="cContentBottom_{$cISO}" id="cContentBottom_{$cISO}">{if !empty($Content[$cISOcat])}{$Content[$cISOcat]}{/if}</textarea>
                                        </span>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                        <div class="card-footer save-wrapper">
                            <div class="row">
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        {__('saveWithIcon')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
