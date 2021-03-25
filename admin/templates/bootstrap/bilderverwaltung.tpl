{include file='tpl_inc/header.tpl'}
{$corruptedPicsTypes = []}
{$corruptedPics = false}

{include file='tpl_inc/seite_header.tpl' cTitel=__('bilderverwaltung') cBeschreibung=__('bilderverwaltungDesc')
        cDokuURL=__('bilderverwaltungURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            {if isset($success)}
                <div class="alert alert-success"><i class="fal fa-info-circle"></i> {$success}</div>
            {/if}
            <div class="table-responsive">
                <table class="list table" id="cache-items" style="width: 100%">
                    <thead>
                    <tr>
                        <th class="text-left">{__('headlineTyp')}</th>
                        <th class="text-center">{__('headlineTotal')}</th>
                        <th class="text-center abbr">{__('headlineCache')}</th>
                        <th class="text-center">{__('faulty')}</th>
                        <th class="text-center" style="width:125px">{__('headlineSize')}</th>
                        <th class="text-center" style="width:200px">{__('actions')}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $items as $item}
                        {$corruptedPicsTypes[{$item->type}] = $item->stats->getCorrupted()}
                        <tr data-type="{$item->type}">
                            <td class="item-name">{$item->name}</td>
                            <td class="text-center">
                                <span class="item-total">
                                  {$item->stats->getTotal()}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="item-generated">
                                  {(($item->stats->getGeneratedBySize(Image::SIZE_XS)
                                  + $item->stats->getGeneratedBySize(Image::SIZE_SM)
                                  + $item->stats->getGeneratedBySize(Image::SIZE_MD)
                                  + $item->stats->getGeneratedBySize(Image::SIZE_LG)) / 4)|round:0}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="item-corrupted">{$item->stats->getCorrupted()}</span>
                            </td>
                            <td class="text-center item-total-size">
                                <i class="fa fa-spinner fa-spin"></i>
                            </td>
                            <td class="text-center action-buttons">
                                <a class="btn btn-outline-primary btn-sm mb-2" href="#" data-callback="flush"
                                   data-type="{$item->type}">
                                    <i class="fas fa-trash-alt"></i> {__('deleteCachedPics')}
                                </a>
                                <a class="btn btn-outline-primary btn-sm mb-2" href="#" data-callback="cleanup"
                                   data-type="{$item->type}">
                                    <i class="fas fa-trash"></i> {__('cleanup')}
                                </a>
                                <a class="btn btn-primary btn-sm" href="#" data-callback="generate"
                                   data-type="{$item->type}">
                                    <i class="fa fa-cog"></i> {__('generatePics')}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {foreach $corruptedPicsTypes as $corruptedPicsType}
        {if $corruptedPicsType > 0}
            {$corruptedPics = true}
        {/if}
    {/foreach}

    {if $corruptedPics}
        <h3 class="top40">
            {__('currentCorruptedPics')}
        </h3>
        <p class="small text-muted">{__('corruptedPicsNote')|sprintf:$smarty.const.MAX_CORRUPTED_IMAGES}</p>
        <table class="list table table-condensed">
            {foreach $corruptedImagesByType as $corruptedImages}
                <thead>
                <tr>
                    <th>{__('articlePic')}</th>
                    <th>{__('articlenr')}</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$corruptedImages key=key item='corruptedImage'}
                    <tr>
                        <td class="col-xs-7 word-break-all">{$corruptedImage->picture}</td>
                        <td class="col-xs-5">
                            {$moreCorruptedImages = false}
                            <div class="input-group">
                                {foreach $corruptedImage->article as $article}
                                    {if $article@iteration <= 3}
                                        <a href="{$article->articleURLFull}" rel="nofollow" target="_blank">
                                            {$article->articleNr}
                                        </a>
                                        {if !$article@last && $article@iteration < 3} |{/if}
                                    {else}
                                        {$moreCorruptedImages = true}
                                        {$moreCorruptedImage = $key}
                                        {break}
                                    {/if}
                                {/foreach}
                                {if $moreCorruptedImages}
                                    <a class="btn btn-default btn-sm" data-toggle="collapse"
                                        href="#dropdownCorruptedImages-{$moreCorruptedImage}"
                                        aria-controls="dropdownCorruptedImages-{$moreCorruptedImage}">
                                        {__('more')} <span class="caret"></span>
                                    </a>
                                    <div class="collapse" id="dropdownCorruptedImages-{$moreCorruptedImage}">
                                        {foreach $corruptedImage->article as $article}
                                            {if $article@iteration > 3}
                                                <a href="{$article->articleURLFull}" rel="nofollow" target="_blank">
                                                    {$article->articleNr}
                                                </a>
                                                {if !$article@last} |{/if}
                                            {/if}
                                        {/foreach}
                                    </div>
                                {/if}
                            </div>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            {/foreach}
        </table>
    {/if}
</div>
<script>
    var lastResults = null,
        lastTick = null,
        running = false,
        notify = null;

    var shortGermanHumanizer = humanizeDuration.humanizer({
        round: true,
        delimiter: ' ',
        units: ['h', 'm', 's'],
        language: 'shortDE',
        languages: {
            shortDE: {
                h: function () {
                    return 'Std'
                },
                m: function () {
                    return 'Min'
                },
                s: function () {
                    return 'Sek'
                }
            }
        }
    });

    $(function () {
        updateStats();
    });

    $('[data-callback]').on('click', clickButton);

    function clickButton(e)
    {
        e.preventDefault();

        var $element = $(e.target);
        var callback = $element.data('callback');

        if ($element.attr('disabled') !== undefined) {
            return false;
        }

        if (!$element.attr('disabled')) {
            window[callback]($element);
        }
    }

    function updateStats(typeToUpdate)
    {
        typeToUpdate = typeToUpdate || null;
        $('#cache-items tbody > tr').each(function (i, item) {
            var type = $(item).data('type');
            if (typeToUpdate === null || typeToUpdate === type) {
                ioCall('loadStats', [type], function (data) {
                    var totalCached = 0;
                    $('.item-total', item).text(data.total);
                    $('.item-corrupted', item).text(data.corrupted);
                    $('.item-total-size', item).text(formatSize(data.totalSize));
                    $(['xs', 'sm', 'md', 'lg']).each(function (i, size) {
                        totalCached += data.generated[size];
                    });
                    $('.item-generated', item).text(Math.round(totalCached / 4, 0));
                }, undefined, undefined, true);
            }
        });
    }

    function cleanup(param)
    {
        var type = (typeof param.data('type') !== 'undefined') ? param.data('type') : 'product';
        running = true;
        lastResults = [];
        lastTick = new Date();
        notify = showCleanupNotify('{__('pendingImageCleanup')}', '{__('successImageDelete')}', type);
        $('.action-buttons a').attr('disabled', true);
        doCleanup(type, 0);
    }

    function stopCleanup()
    {
        running = false;
        $('.action-buttons a').attr('disabled', false);
    }

    function finishCleanup(result)
    {
        stopCleanup();

        notify.update({
            progress: 100,
            message: result.deletedImages + ' {__('successImageDelete')}',
            type: 'success',
            title: '{__('successImageCleanup')}'
        });
    }

    function doCleanup(type, index)
    {
        lastTick = new Date().getTime();
        ioCall('cleanupStorage', [type, index], function (result) {
            var items = result.deletes,
                deleted = result.deletedImages,
                total = result.total,
                offsetTick = new Date().getTime() - lastTick,
                perItem = Math.floor(offsetTick / result.checkedFiles),
                avg,
                remaining,
                eta,
                readable,
                percent;
            if (lastResults.length >= 10) {
                lastResults.splice(0, 1);
            }
            lastResults.push(perItem);
            avg = average(lastResults);
            remaining = total - result.checkedFilesTotal;
            eta = Math.max(0, Math.ceil(remaining * avg));
            readable = shortGermanHumanizer(eta);
            percent = Math.round(result.checkedFilesTotal / total * 100, 0);
            notify.update({
                message: '<div class="row">' +
                '<div class="col-sm-4"><strong>' + percent + '</strong>%</div>' +
                '<div class="col-sm-4 text-center">' + result.checkedFilesTotal + ' / ' + total + '</div>' +
                '<div class="col-sm-4 text-right">' + readable + '</div>' +
                '</div>',
                progress: percent
            });

            if (result.nextIndex >= total) {
                finishCleanup(result);
                return;
            }

            if (result.nextIndex > 0 && result.nextIndex < total && running) {
                doCleanup(type, result.nextIndex);
            }
        }, undefined, undefined, true);
    }

    function generate(param)
    {
        startGenerate((typeof param.data('type') !== 'undefined') ? param.data('type') : 'product');
    }

    function flush(param)
    {
        var type = (typeof param.data('type') !== 'undefined') ? param.data('type') : 'product';
        return ioCall('clearImageCache', [type, true], function (result) {
            updateStats(type);
            showFlushNotify(result.msg, '', type).update({
                progress: 100,
                message: '&nbsp;',
                type: result.ok == true ? 'success' :  'danger',
                title: result.msg
            });
        }, undefined, undefined, true);
    }

    function startGenerate(type)
    {
        running = true;
        lastResults = [];
        lastTick = new Date();
        notify = showGenerateNotify('{__('pendingImageGenerate')}', '{__('pendingStatisticCalc')}', type);

        $('.action-buttons a').attr('disabled', true);
        doGenerate(type, 0);
    }

    function stopGenerate()
    {
        running = false;
        $('.action-buttons a').attr('disabled', false);
    }

    function finishGenerate(type) {
        type = type || null;
        stopGenerate();
        updateStats(type);

        notify.update({
            progress: 100,
            message: '&nbsp;',
            type: 'success',
            title: '{__('successImageGenerate')}'
        });
    }

    function doGenerate(type, index)
    {
        lastTick = new Date().getTime();

        var call = loadGenerate(type, index, function (result) {
            if (result.lastRenderError) {
                stopGenerate();
                updateStats(type);
                notify.close();
                createNotify({
                    title: '{__('errorImageGenerate')}',
                    message: result.lastRenderError,
                }, {
                    type: 'danger',
                });
            }

            var items = result.images,
                rendered = result.renderedImages,
                total = result.total,
                offsetTick = new Date().getTime() - lastTick,
                perItem = Math.floor(offsetTick / items.length),
                avg,
                remaining,
                eta,
                readable,
                percent;

            items.forEach(item => {
                console.log(item.success);
            });

            if (lastResults.length >= 10) {
                lastResults.splice(0, 1);
            }
            lastResults.push(perItem);

            avg = average(lastResults);
            remaining = total - rendered;
            eta = Math.max(0, Math.ceil(remaining * avg));
            readable = shortGermanHumanizer(eta);
            percent = Math.round(rendered * 100 / total, 0);

            notify.update({
                message: '<div class="row">' +
                '<div class="col-sm-4"><strong>' + percent + '</strong>%</div>' +
                '<div class="col-sm-4 text-center">' + rendered + ' / ' + total + '</div>' +
                '<div class="col-sm-4 text-right">' + readable + '</div>' +
                '</div>',
                progress: percent
            });

            if (rendered >= total) {
                finishGenerate(type);
                return;
            }

            if (rendered < total && running) {
                doGenerate(type, rendered);
            }
        });
        $.when(call).done();
    }

    function average(array)
    {
        var t = 0,
            i = 0;
        while (i < array.length) t += array[i++];
        return t / array.length;
    }

    function loadGenerate(type, index, callback)
    {
        return ioCall('generateImageCache', [type, index], function (result) {
            callback(result);
        }, undefined, undefined, true);
    }

    function showFlushNotify(title, message, type)
    {
        return createNotify({
            title: title,
            message: message
        }, {
            allow_dismiss: true,
            showProgressbar: true,
            delay: 0
        });
    }

    function showCleanupNotify(title, message, type)
    {
        return createNotify({
            title: title,
            message: message
        }, {
            allow_dismiss: true,
            showProgressbar: true,
            delay: 0,
            onClose: function () {
                stopCleanup();
            }
        });
    }

    function showGenerateNotify(title, message, type)
    {
        return createNotify({
            title: title,
            message: message
        }, {
            allow_dismiss: true,
            showProgressbar: true,
            delay: 0,
            onClose: function () {
                stopGenerate();
                updateStats(type);
            }
        });
    }
</script>
{include file='tpl_inc/footer.tpl'}
