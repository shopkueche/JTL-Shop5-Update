<?php

use JTL\Helpers\Form;
use JTL\Helpers\Request;

/**
 * If the "Export CSV" button was clicked with the id $exporterId, offer a CSV download and stop execution of current
 * script. Call this function as soon as you can provide data to be exported but before any page output has been done!
 * Call this function for each CSV exporter on a page with its unique $exporterId!
 *
 * @param string $exporterId
 * @param string $csvFilename
 * @param array|callable $source - array of objects to be exported as CSV or function that gives that array back on
 *      demand. The function may also return an Iterator object
 * @param array $fields - array of property/column names to be included or empty array for all columns (taken from
 *      first item of $source)
 * @param array $excluded - array of property/column names to be excluded
 * @param string $delim
 * @param bool   $head
 * @return bool - false = failure or exporter-id-mismatch
 */
function handleCsvExportAction(
    $exporterId,
    $csvFilename,
    $source,
    $fields = [],
    $excluded = [],
    $delim = ',',
    $head = true
) {
    if (Form::validateToken() && Request::verifyGPDataString('exportcsv') === $exporterId) {
        if (is_callable($source)) {
            $arr = $source();
        } elseif (is_array($source)) {
            $arr = $source;
        } else {
            return false;
        }

        if (count($fields) === 0) {
            if ($arr instanceof Iterator) {
                /** @var Iterator $arr **/
                $first = $arr->current();
            } else {
                $first = $arr[0];
            }
            $assoc  = get_object_vars($first);
            $fields = array_keys($assoc);
            $fields = array_diff($fields, $excluded);
            $fields = array_filter($fields, '\is_string');
        }

        header('Content-Disposition: attachment; filename=' . $csvFilename);
        header('Content-Type: text/csv');
        $fs = fopen('php://output', 'wb');

        if ($head) {
            fputcsv($fs, $fields);
        }

        foreach ($arr as $elem) {
            $csvRow = [];
            foreach ($fields as $field) {
                $csvRow[] = (string)($elem->$field ?? '');
            }

            fputcsv($fs, $csvRow, $delim);
        }
        exit();
    }

    return false;
}
