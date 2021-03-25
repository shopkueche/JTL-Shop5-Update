<?php

use JTL\Helpers\Form;

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibFehlendeEingabenKontaktformular()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Form::getMissingContactFormData();
}

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function baueKontaktFormularVorgaben()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Form::baueKontaktFormularVorgaben();
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeBetreffVorhanden()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Form::checkSubject();
}

/**
 * @return int|bool
 * @deprecated since 5.0.0
 */
function bearbeiteNachricht()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Form::editMessage();
}

/**
 * @param int $min
 * @return bool
 * @deprecated since 5.0.0
 */
function floodSchutz($min)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Form::checkFloodProtection($min);
}

if (!function_exists('baueFormularVorgaben')) {
    /**
     * @return stdClass
     * @deprecated since 5.0.0
     */
    function baueFormularVorgaben()
    {
        trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
        return Form::baueKontaktFormularVorgaben();
    }
}
