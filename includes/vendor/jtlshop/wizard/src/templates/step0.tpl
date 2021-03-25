{if $wizard->getStep()->isSync()}
    <div class="alert alert-success" role="alert"><i class="glyphicon glyphicon-ok"></i> Bereits durchgef&uumlhrt !
    </div>
    <br>
    <table class="table table-striped table-hover">
        <caption>Firmendaten</caption>
        <thead>
        <tr>
            <th class="col-xs-7"></th>
            <th class="col-xs-5"></th>
        </tr>
        </thead>
        <tbody>
        {foreach $wizard->getStep()->getCompany() as $key => $value}
            <tr>
                <td>{$key|substr:1}</td>
                <td>{$value}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    <table class="table table-striped table-hover">
        <caption>Kundengruppen</caption>
        <thead>
        <tr>
            <th class="col-xs"></th>
        </tr>
        </thead>
        <tbody>
        {foreach $wizard->getStep()->getGroups() as $group}
            <tr>
                {if $group->cStandard === 'Y'}
                    <td><strong>{$group->cName}</strong> (Standard)</td>
                {else}
                    <td>{$group->cName}</td>
                {/if}
            </tr>
        {/foreach}
        </tbody>
    </table>
    <table class="table table-striped table-hove">
        <caption>Sprachen</caption>
        <thead>
        <tr>
            <th class="col-xs"></th>
        </tr>
        </thead>
        <tbody>
        {foreach $wizard->getStep()->getLanguages() as $language}
            <tr>
                {if $language->cShopStandard === 'Y'}
                    <td><strong>{$language->cNameDeutsch}</strong> (Standard)</td>
                {else}
                    <td>{$language->cNameDeutsch}</td>
                {/if}

                {if $language->cShopStandard === 'N' && $language->cNameDeutsch === 'Deutsch'}
                    <td style="color: red; text-align: center">Achtung, Deutsch ist nicht die Standardsprache!</td>
                {/if}
            </tr>
        {/foreach}
        </tbody>
    </table>
    <p><a href="https://guide.jtl-software.de/Sprachen_in_JTL-Wawi_ausw%C3%A4hlen" target="_blank">Zus&aumltzliche
            Sprache aktivieren und importieren</a></p>
    <br>
    <table class="table table-striped table-hove">
        <caption>W&aumlhrungen</caption>
        <thead>
        <tr>
            <th class="col-xs"></th>
        </tr>
        </thead>
        <tbody>
        {foreach $wizard->getStep()->getCurrencies() as $currency}
            {if $currency->cStandard === 'Y'}
                <tr>
                    <td><strong>{$currency->cName}</strong> (Standard)</td>
                </tr>
            {else}
                <tr>
                    <td>{$currency->cName}</td>
                </tr>
            {/if}
        {/foreach}
        </tbody>
    </table>
    <p><a href="https://guide.jtl-software.de/W%C3%A4hrungen_in_JTL-Wawi_anlegen_/_bearbeiten" target="_blank">W&aumlhrungen
            &uumlber Wawi &aumlndern</a></p>
    <br>
    <br>
    <p>
        <button type="submit" name="submit" class="btn btn-primary" value="yes">Weiter</button>
    </p>
{else}
    <div class="alert alert-danger" role="alert"><i class="glyphicon glyphicon-danger"></i>Bitte zuerst Wawi Abgleich
        durchführen!
    </div>
{/if}

