function setzeBrutto(elem, targetElemID, fSteuersatz)
{    
   document.getElementById(targetElemID).value = Math.round(Number(elem.value) * ((100 + Number(fSteuersatz)) / 100) * 100) / 100;
}

function setzeNetto(elem, targetElemID, fSteuersatz)
{
   document.getElementById(targetElemID).value = Math.round(Number(elem.value) * (100 / (100 + Number(fSteuersatz))) * 100) / 100;
}

/**
 * @deprecated since 4.06
 * @param cTargetID
 * @param elem
 * @param targetElemID
 * @param fSteuersatz
 */
function setzeBruttoAjax(cTargetID, elem, targetElemID, fSteuersatz)
{
   offset = $(elem).offset();
   if ($('#' + cTargetID).length > 0)
      $('#' + cTargetID).fadeIn('fast');

   setzeBrutto(elem, targetElemID, fSteuersatz);
   ioCall('getCurrencyConversion',
       [lem.value, 0, cTargetID],
       undefined,
       undefined,
       undefined,
       true
   );

   $('#' + cTargetID).css({
      position: 'absolute',
      top: offset.top + $(elem).outerHeight(),
      left: offset.left
   }).addClass('pstooltip');
   
   $(elem).attr('autocomplete', 'off');
   $(elem).blur(function() {
      $('#' + cTargetID).fadeOut('fast');
   });
}

/**
 * @deprecated since 4.06
 * @param cTargetID
 * @param elem
 * @param targetElemID
 * @param fSteuersatz
 */
function setzeNettoAjax(cTargetID, elem, targetElemID, fSteuersatz)
{   
   offset = $(elem).offset();
   if ($('#' + cTargetID).length > 0)
      $('#' + cTargetID).fadeIn('fast');

    setzeNetto(elem, targetElemID, fSteuersatz);
    ioCall(
        'getCurrencyConversion',
        [0, elem.value, cTargetID],
        function () { },
        function () { },
        { },
        true
    );
   
   $('#' + cTargetID).css({
      position: 'absolute',
      top: offset.top + $(elem).outerHeight(),
      left: offset.left
   }).addClass('pstooltip');
   
   $(elem).attr('autocomplete', 'off');
   $(elem).blur(function() {
      $('#' + cTargetID).fadeOut('fast');
   });
}

function setzePreisAjax(bNetto, cTargetID, elem)
{
    let args;
    if (bNetto) {
        args = [elem.value, 0, cTargetID];
    } else {
        args = [0, elem.value, cTargetID];
    }

    ioCall(
        'getCurrencyConversion',
        args,
        undefined,
        undefined,
        undefined,
        true
    );
}

function setzePreisTooltipAjax(bNetto, cTooltipID, sourceElem)
{
    let args;
    if (bNetto) {
        args = [parseFloat($(sourceElem).val().replace(',', '.')), 0, cTooltipID];
    } else {
        args = [0, parseFloat($(sourceElem).val().replace(',', '.')), cTooltipID]
    }

    ioCall(
        'setCurrencyConversionTooltip',
        args,
        undefined,
        undefined,
        undefined,
        true
    );
}

function setzeAufpreisTyp(elem, bruttoElemID, nettoElemID)
{
   if(elem.value == "festpreis")
   {
      document.getElementById(bruttoElemID).style.visibility = 'visible';
      setzeBrutto(document.getElementById(nettoElemID), bruttoElemID);
   }
   else
      document.getElementById(bruttoElemID).style.visibility = 'hidden';             
}

function makeCurrencyTooltip (sourceId) {
   changeCurrencyTooltipText (sourceId);
   $('#' + sourceId).keyup(function (e) { changeCurrencyTooltipText (sourceId); });
}

function changeCurrencyTooltipText (sourceId) {
   var sourceInput = $('#' + sourceId)[0];
   setzePreisTooltipAjax(false, sourceId + 'Tooltip', sourceInput);
}