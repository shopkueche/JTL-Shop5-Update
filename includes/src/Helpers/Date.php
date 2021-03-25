<?php

namespace JTL\Helpers;

use DateInterval;
use DateTime;
use Exception;
use JTL\Shop;

/**
 * Class Date
 * @package JTL\Helpers
 * @since 5.0.0
 */
class Date
{
    /**
     * @param DateTime|string|int $date
     * @param int                 $weekdays
     * @return DateTime
     * @since 5.0.0
     */
    public static function dateAddWeekday($date, $weekdays): DateTime
    {
        try {
            if (\is_string($date)) {
                $resDate = new DateTime($date);
            } elseif (\is_numeric($date)) {
                $resDate = new DateTime();
                $resDate->setTimestamp($date);
            } elseif (\is_object($date) && \is_a($date, DateTime::class)) {
                $resDate = new DateTime($date->format(DateTime::ATOM));
            } else {
                $resDate = new DateTime();
            }
        } catch (Exception $e) {
            Shop::Container()->getLogService()->error($e->getMessage());
            $resDate = new DateTime();
        }

        if ((int)$resDate->format('w') === 0) {
            // Add one weekday if startdate is on sunday
            $resDate->add(DateInterval::createFromDateString('1 weekday'));
        }

        // Add $weekdays as normal days
        $resDate->add(DateInterval::createFromDateString($weekdays . ' day'));

        if ((int)$resDate->format('w') === 0) {
            // Add one weekday if enddate is on sunday
            $resDate->add(DateInterval::createFromDateString('1 weekday'));
        }

        return $resDate;
    }

    /**
     * YYYY-MM-DD HH:MM:SS, YYYY-MM-DD, now oder now()
     *
     * @param string $dateString
     * @return array
     * @former gibDatumTeile()
     * @since 5.0.0
     */
    public static function getDateParts(string $dateString): array
    {
        $parts = [];
        if (\mb_strlen($dateString) > 0) {
            if (\mb_convert_case($dateString, \MB_CASE_LOWER) === 'now()') {
                $dateString = 'now';
            }
            try {
                $date              = new DateTime($dateString);
                $parts['cDatum']   = $date->format('Y-m-d');
                $parts['cZeit']    = $date->format('H:m:s');
                $parts['cJahr']    = $date->format('Y');
                $parts['cMonat']   = $date->format('m');
                $parts['cTag']     = $date->format('d');
                $parts['cStunde']  = $date->format('H');
                $parts['cMinute']  = $date->format('i');
                $parts['cSekunde'] = $date->format('s');
            } catch (Exception $e) {
            }
        }

        return $parts;
    }

    /**
     * localize datetime to DE
     *
     * @param string $input
     * @param bool   $dateOnly
     * @return string
     */
    public static function localize(string $input, bool $dateOnly = false): string
    {
        $date = new DateTime($input);

        return $date->format($dateOnly ? 'd.m.Y' : 'd.m.Y H:i');
    }

    /**
     * @param string|null $date
     * @return string
     */
    public static function convertDateToMysqlStandard(?string $date): string
    {
        if ($date === null) {
            $convertedDate = '_DBNULL_';
        } elseif (\preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $date)) {
            $convertedDate = DateTime::createFromFormat('d.m.Y', $date)->format('Y-m-d');
        } elseif (\preg_match('/^\d{4}\-\d{2}\-(\d{2})$/', $date)) {
            $convertedDate = $date;
        } else {
            $convertedDate = '_DBNULL_';
        }

        return $convertedDate;
    }
}
