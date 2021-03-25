{if $wizard->getStep()->isUstidStatus() === false}
    <p id="ustid" style="color: red; margin-left: 2%;">Es wurde keine UstID hinterlegt, bitte tragen Sie diese in der Wawi nach!  <a href="https://guide.jtl-software.de/Kategorie:JTL-Wawi:Steuern"> Link zum Guide</a></p>
{/if}

<script>

    //  Ustid Warnung ausblenden
    $("#ustid").insertAfter($("#checkboxDiv-5").hide()).hide();

    $("#question-4").click(function() {
        if($(this).is(":checked")) {
            $("#ustid").show();
        }
        else {
            $("#ustid").hide(200);
        }
    });

    //  Checkboxes checked per default
    $("#question-1").attr('checked', true);
    $("#question-2").attr('checked', true);
    $("#question-3").attr('checked', true);

    // Hide on first load
    $("#checkboxDiv-1").hide();
    $("#checkboxDiv-2").hide();
    $("#checkboxDiv-3").hide();

</script>

<p>
    <br>
    <button type="submit" class="btn btn-primary" name="submit" value="yes">Weiter</button>
</p>