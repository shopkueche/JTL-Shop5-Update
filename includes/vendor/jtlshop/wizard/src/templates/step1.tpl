
{if $wizard->getStep()->getTax() !== '0'}
    <p id="standardTax" style="color: red; margin-left: 2%">Die Steuereinstellungen entsprechen nicht der Kleinunternehmerregelung !
        <br> Der Standard-Steuersatz f&uumlr Kleinunternehmer ist 0%, Ihr Standard-Steuersatz ist {$wizard->getStep()->getTax()} %.
        <br> Bitte &aumlndern Sie diese Einstellung in der Wawi. <a href="https://guide.jtl-software.de/Steuerverwaltung:_Steuerverwaltung">Link zum Guide</a>
    </p>
{/if}

<script>
    $("#standardTax").insertAfter($("#checkboxDiv-0")).hide();

    $("[name='question-0']").click(function() {
        if($(this).is(":checked")) {
            $("#standardTax").show(300);
        }
        else {
            $("#standardTax").hide(200);
        }
    });
</script>

<p>
    <br>
    <button type="submit" class="btn btn-primary" name="submit" value="yes">Weiter</button>
</p>

