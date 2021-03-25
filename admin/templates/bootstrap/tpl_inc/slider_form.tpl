<div id="settings">
    <form action="slider.php?action={$action}" method="post" accept-charset="iso-8859-1" id="slider" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="action" value="{$action}" />
        <input type="hidden" name="kSlider" value="{$oSlider->getID()}" />
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('general')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <ul class="jtl-list-group">
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="cName">{__('internalName')}</label>
                        </div>
                        <div class="for">
                            <input type="text" name="cName" id="cName" class="form-control" value="{$oSlider->getName()}" />
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bAktiv">{__('status')}</label>
                        </div>
                        <div class="for">
                            <select id="bAktiv" name="bAktiv" class="custom-select">
                                <option value="0"{if $oSlider->getIsActive() === false} selected="selected"{/if}>{__('deactivated')}</option>
                                <option value="1"{if $oSlider->getIsActive() === true} selected="selected"{/if}>{__('activated')}</option>
                            </select>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bRandomStart">{__('randomStart')}</label>
                        </div>
                        <div class="for">
                            <select id="bRandomStart" name="bRandomStart" class="custom-select">
                                <option value="0"{if $oSlider->getRandomStart() === false} selected="selected"{/if}>{__('no')}</option>
                                <option value="1"{if $oSlider->getRandomStart() === true} selected="selected"{/if}>{__('yes')}</option>
                            </select>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bPauseOnHover">{__('pauseOnHover')}</label>
                        </div>
                        <div class="for">
                            <select id="bPauseOnHover" name="bPauseOnHover" class="custom-select">
                                <option value="0"{if $oSlider->getPauseOnHover() === false} selected="selected"{/if}>{__('no')}</option>
                                <option value="1"{if $oSlider->getPauseOnHover() === true} selected="selected"{/if}>{__('yes')}</option>
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('presentation')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <ul class="jtl-list-group">
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bUseKB">{__('kenBurnsEffect')}</label>
                        </div>
                        <div class="for">
                            <select class="custom-select" id="bUseKB" name="bUseKB">
                                <option value="0"{if $oSlider->getUseKB() === false} selected="selected"{/if}>{__('deactivated')}</option>
                                <option value="1"{if $oSlider->getUseKB() === true} selected="selected"{/if}>{__('activated')}</option>
                            </select>
                        </div>
                        <p><i class="fal fa-exclamation-triangle"></i> {__('overrideDescription')}</p>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bControlNav">{__('dotNavigation')}</label>
                        </div>
                        <div class="for">
                            <select class="custom-select" id="bControlNav" name="bControlNav">
                                <option value="0"{if $oSlider->getControlNav() === false} selected="selected"{/if}>{__('hide')}
                                </option>
                                <option value="1"{if $oSlider->getControlNav() === true} selected="selected"{/if}>{__('show')}</option>
                            </select>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bThumbnail">{__('thumbnailNavigation')}</label>
                        </div>
                        <div class="for">
                            <select class="custom-select" id="bThumbnail" name="bThumbnail">
                                <option value="0"{if $oSlider->getThumbnail() === false} selected="selected"{/if}>{__('deactivated')}
                                </option>
                                <option value="1"{if $oSlider->getThumbnail() === true} selected="selected"{/if}>{__('activated')}</option>
                            </select>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bDirectionNav">{__('directionNavigation')}</label>
                        </div>
                        <div class="for">
                            <select class="custom-select" id="bDirectionNav" name="bDirectionNav">
                                <option value="0"{if $oSlider->getDirectionNav() === false} selected="selected"{/if}>{__('hide')}</option>
                                <option value="1"{if $oSlider->getDirectionNav() === true} selected="selected"{/if}>{__('show')}</option>
                            </select>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <strong>{__('effects')}</strong>
                        </div>
                        <div class="for">
                            <div class="custom-control custom-checkbox">
                                <input id="cRandomEffects" type="checkbox" value="random" class="custom-control-input random_effects" {if isset($checked)}{$checked} {/if}name="cEffects" />
                                <label class="custom-control-label" for="cRandomEffects">{__('randomEffects')}</label>
                            </div>
                            <div class="select_container row">
                                <div class="col-xs-12 col-md-6 select_box">
                                    <label for="cSelectedEffects">{__('selectedEffects')}</label>
                                    <select class="custom-select" id="cSelectedEffects" name="cSelectedEffects" size="10" multiple {$disabled}>
                                        {if isset($cEffects)}{$cEffects}{/if}
                                    </select>
                                    <input type="hidden" name="cEffects" value="{if isset($oSlider)}{$oSlider->getEffects()}{/if}" {$disabled}/>
                                    <button type="button" class="select_remove button remove btn btn-danger" value="entfernen" {$disabled}>{__('remove')}</button>
                                </div>
                                <div class="col-xs-12 col-md-6 select_box">
                                    <label for="cAvaibleEffects">{__('availableEffects')}</label>
                                    <select class="custom-select" id="cAvaibleEffects" name="cAvaibleEffects" size="10" multiple {$disabled}>
                                        <option value="sliceDown">{__('sliceDown')}</option>
                                        <option value="sliceDownLeft">{__('sliceDownLeft')}</option>
                                        <option value="sliceUp">{__('sliceUp')}</option>
                                        <option value="sliceUpLeft">{__('sliceUpLeft')}</option>
                                        <option value="sliceUpDown">{__('sliceUpDown')}</option>
                                        <option value="sliceUpDownLeft">{__('sliceUpDownLeft')}</option>
                                        <option value="fold">{__('fold')}</option>
                                        <option value="fade">{__('fade')}</option>
                                        <option value="slideInRight">{__('slideInRight')}</option>
                                        <option value="slideInLeft">{__('slideInLeft')}</option>
                                        <option value="boxRandom">{__('boxRandom')}</option>
                                        <option value="boxRain">{__('boxRain')}</option>
                                        <option value="boxRainReverse">{__('boxRainReverse')}</option>
                                        <option value="boxRainGrow">{__('boxRainGrow')}</option>
                                        <option value="boxRainGrowReverse">{__('boxRainGrowReverse')}</option>
                                    </select>
                                    <button type="button" class="select_add button add btn btn-default" value="hinzufügen" {$disabled}>{__('add')}</button>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="cTheme">{__('theme')}</label>
                        </div>
                        <div class="for">
                            <select id="cTheme" name="cTheme" class="custom-select">
                                <option value="default"{if $oSlider->getTheme() === 'default'} selected="selected"{/if}>{__('default')}</option>
                                <option value="bar"{if $oSlider->getTheme() === 'bar'} selected="selected"{/if}>{__('bar')}</option>
                                <option value="light"{if $oSlider->getTheme() === 'light'} selected="selected"{/if}>{__('light')}</option>
                                <option value="dark"{if $oSlider->getTheme() === 'dark'} selected="selected"{/if}>{__('dark')}</option>
                            </select>
                        </div>
                    </li>

                    <li class="list-group-item item">
                        <div class="name">
                            <label for="nAnimationSpeed">{__('animationSpeed')}</label>
                        </div>
                        <div class="for">
                            <input type="text" name="nAnimationSpeed" id="nAnimationSpeed" value="{$oSlider->getAnimationSpeed()}" class="form-control" />
                            <p id="nAnimationSpeedWarning" class="nAnimationSpeedWarningColor">{__('warningAnimationTimeLower')}</p>
                        </div>
                    </li>

                    <li class="list-group-item item">
                        <div class="name">
                            <label for="nPauseTime">{__('pauseTime')}</label>
                        </div>
                        <div class="for">
                            <input type="text" name="nPauseTime" id="nPauseTime" value="{$oSlider->getPauseTime()}" class="form-control" />
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('displayOptions')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="kSprache">{__('language')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select id="kSprache" name="kSprache" class="custom-select">
                            <option value="0">{__('all')}</option>
                            {foreach $availableLanguages as $language}
                                <option value="{$language->getId()}" {if isset($oExtension->kSprache) && (int)$oExtension->kSprache === $language->getId()}selected="selected"{/if}>{$language->getLocalizedName()}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="kKundengruppe">{__('customerGroup')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select id="kKundengruppe" name="kKundengruppe" class="custom-select">
                            <option value="0">{__('all')}</option>
                            {foreach $customerGroups as $customerGroup}
                                <option value="{$customerGroup->getID()}" {if isset($oExtension->kKundengruppe) && $oExtension->kKundengruppe == $customerGroup->getID()}selected="selected"{/if}>{$customerGroup->getName()}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="nSeitenTyp">{__('pageType')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        {if isset($oExtension->nSeite)}
                            <select class="custom-select" id="nSeitenTyp" name="nSeitenTyp"> {include file='tpl_inc/seiten_liste.tpl' nPage=$oExtension->nSeite}</select>
                        {else}
                            <select class="custom-select" id="nSeitenTyp" name="nSeitenTyp"> {include file='tpl_inc/seiten_liste.tpl' nPage=0}</select>
                        {/if}
                    </div>
                </div>
                <div id="type2" class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="cKey">{__('filter')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select class="custom-select" name="cKey" id="cKey">
                            <option value="" {if isset($oExtension->cKey) && $oExtension->cKey === ''} selected="selected"{/if}>
                                {__('noFilter')}
                            </option>
                            <option value="kMerkmalWert" {if isset($oExtension->cKey) && $oExtension->cKey === 'kMerkmalWert'} selected="selected"{/if}>
                                {__('attribute')}
                            </option>
                            <option value="kKategorie" {if isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie'} selected="selected"{/if}>
                                {__('category')}
                            </option>
                            <option value="kHersteller" {if isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller'} selected="selected"{/if}>
                                {__('manufacturer')}
                            </option>
                            <option value="cSuche" {if isset($oExtension->cKey) && $oExtension->cKey === 'cSuche'} selected="selected"{/if}>
                                {__('searchTerm')}
                            </option>
                        </select>
                    </div>
                </div>
                <div id="keykArtikel" class="form-group form-row align-items-center key">
                    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="article_name">{__('product')}:</label>
                    <input type="hidden" name="article_key" id="article_key"
                           value="{if (isset($cKey) && $cKey === 'kArtikel') || (isset($oExtension->cKey) && $oExtension->cKey === 'kArtikel')}{$oExtension->cValue}{/if}">
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" name="article_name" id="article_name">
                        <i class="fas fa-spinner fa-pulse typeahead-spinner"></i>
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        {getHelpDesc cDesc=__('typeAheadProduct')}
                    </div>
                    <script>
                        enableTypeahead('#article_name', 'getProducts', 'cName', null, function(e, item) {
                            $('#article_name').val(item.cName);
                            $('#article_key').val(item.kArtikel);
                        }, $('#keykArtikel .fa-spinner'));
                        {if (isset($cKey) && $cKey === 'kArtikel') || (isset($oExtension->cKey) && $oExtension->cKey === 'kArtikel')}
                            ioCall('getProducts', [[$('#article_key').val()]], function (data) {
                                $('#article_name').val(data[0].cName);
                            });
                        {/if}
                    </script>
                </div>
                <div id="keykLink" class="form-group form-row align-items-center key">
                    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="link_name">{__('pageSelf')}:</label>
                    <input type="hidden" name="link_key" id="link_key"
                           value="{if (isset($cKey) && $cKey === 'kLink') || (isset($oExtension->cKey) && $oExtension->cKey === 'kLink')}{$oExtension->cValue}{/if}">
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" name="link_name" id="link_name">
                        <i class="fas fa-spinner fa-pulse typeahead-spinner"></i>
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        {getHelpDesc cDesc=__('typeAheadPages')}
                    </div>
                    <script>
                        enableTypeahead('#link_name', 'getPages', 'cName', null, function(e, item) {
                            $('#link_name').val(item.cName);
                            $('#link_key').val(item.kLink);
                        }, $('#keykLink .fa-spinner'));
                        {if (isset($cKey) && $cKey === 'kLink') || (isset($oExtension->cKey) && $oExtension->cKey === 'kLink')}
                            ioCall('getPages', [[$('#link_key').val()]], function (data) {
                                $('#link_name').val(data[0].cName);
                            });
                        {/if}
                    </script>
                </div>
                <div id="keykMerkmalWert" class="form-group form-row align-items-center key">
                    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="attribute_name">{__('attribute')}:</label>
                    <input type="hidden" name="attribute_key" id="attribute_key"
                           value="{if (isset($cKey) && $cKey === 'kMerkmalWert') || (isset($oExtension->cKey) && $oExtension->cKey === 'kMerkmalWert')}{$oExtension->cValue}{/if}">
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" name="attribute_name" id="attribute_name">
                        <i class="fas fa-spinner fa-pulse typeahead-spinner"></i>
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        {getHelpDesc cDesc=__('typeAheadAttribute')}
                    </div>
                    <script>
                        enableTypeahead('#attribute_name', 'getAttributes', 'cWert', null, function(e, item) {
                            $('#attribute_name').val(item.cWert);
                            $('#attribute_key').val(item.kMerkmalWert);
                        }, $('#keykMerkmalWert .fa-spinner'));
                        {if (isset($cKey) && $cKey === 'kMerkmalWert') || (isset($oExtension->cKey) && $oExtension->cKey === 'kMerkmalWert')}
                            ioCall('getAttributes', [[$('#attribute_key').val()]], function (data) {
                                $('#attribute_name').val(data[0].cWert);
                            });
                        {/if}
                    </script>
                </div>
                <div id="keykKategorie" class="form-group form-row align-items-center key">
                    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="categories_name">{__('category')}:</label>
                    <input type="hidden" name="categories_key" id="categories_key"
                           value="{if (isset($cKey) && $cKey === 'kKategorie') || (isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie')}{$oExtension->cValue}{/if}">
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" name="categories_name" id="categories_name">
                        <i class="fas fa-spinner fa-pulse typeahead-spinner"></i>
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        {getHelpDesc cDesc=__('typeAheadCategory')}
                    </div>
                    <script>
                        enableTypeahead('#categories_name', 'getCategories', function(item) {
                            var parentName = '';
                            if (item.parentName !== null) {
                                parentName = ' (' + item.parentName + ')';
                            }
                            return item.cName + parentName;
                        }, null, function(e, item) {
                            $('#categories_name').val(item.cName);
                            $('#categories_key').val(item.kKategorie);
                        }, $('#keykKategorie .fa-spinner'));
                        {if (isset($cKey) && $cKey === 'kKategorie') || (isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie')}
                            ioCall('getCategories', [[$('#categories_key').val()]], function (data) {
                                $('#categories_name').val(data[0].cName);
                            });
                        {/if}
                    </script>
                </div>
                <div id="keykHersteller" class="form-group form-row align-items-center key">
                    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="manufacturer_name">{__('manufacturer')}:</label>
                    <input type="hidden" name="manufacturer_key" id="manufacturer_key"
                           value="{if (isset($cKey) && $cKey === 'kHersteller') || (isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller')}{$oExtension->cValue}{/if}">
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" name="manufacturer_name" id="manufacturer_name">
                        <i class="fas fa-spinner fa-pulse typeahead-spinner"></i>
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        {getHelpDesc cDesc=__('typeAheadAttribute')}
                    </div>
                <script>
                        enableTypeahead('#manufacturer_name', 'getManufacturers', 'cName', null, function(e, item) {
                            $('#manufacturer_name').val(item.cName);
                            $('#manufacturer_key').val(item.kHersteller);
                        }, $('#keykHersteller .fa-spinner'));
                        {if (isset($cKey) && $cKey === 'kHersteller') || (isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller')}
                            ioCall('getManufacturers', [[$('#manufacturer_key').val()]], function (data) {
                                $('#manufacturer_name').val(data[0].cName);
                            });
                        {/if}
                    </script>
                </div>
                <div id="keycSuche" class="key form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="ikeycSuche">{__('searchTerm')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" id="ikeycSuche" name="keycSuche"
                               value="{if (isset($cKey) &&  $cKey === 'cSuche') || (isset($oExtension->cKey) && $oExtension->cKey === 'cSuche')}{if isset($keycSuche) && $keycSuche !== ''}{$keycSuche}{else}{$oExtension->cValue}{/if}{/if}">
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        {getHelpDesc cDesc=__('enterSearchTerm')}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <button type="button" class="btn btn-default btn-block" onclick="window.location.href = 'slider.php';" value="zurück">
                        {__('cancelWithIcon')}
                    </button>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    <button type="submit" class="btn btn-primary btn-block" value="{__('save')}">
                        {__('saveWithIcon')}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
