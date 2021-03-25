<?php declare(strict_types=1);

namespace JTL\Model;

use DateInterval;
use DateTime;
use Exception;

/**
 * Class ModelHelper
 * @package App\Models
 */
final class ModelHelper
{
    /**
     * @param string|DateTime $value
     * @param string          $format
     * @return string|null
     */
    private static function formatDateTime($value, $format = 'Y-m-d H:i:s'): ?string
    {
        if (\is_a($value, DateTime::class)) {
            return $value->format($format);
        }
        if (\is_string($value)) {
            return self::fromStrToDateTime($value)->format($format);
        }

        return null;
    }

    /**
     * @param string|DateTime $value
     *
     * @return string|null
     */
    public static function fromDateTimeToStr($value): ?string
    {
        return self::formatDateTime($value);
    }

    /**
     * @param string|DateTime      $value
     * @param string|DateTime|null $default
     * @return DateTime|null
     */
    public static function fromStrToDateTime($value, $default = null): ?DateTime
    {
        if (($value === null && $default === null) || \is_a($value, DateTime::class)) {
            return $value;
        }
        if (\is_string($value)) {
            try {
                return new DateTime(\str_replace('now()', 'now', $value));
            } catch (Exception $e) {
                return self::fromStrToDateTime($default);
            }
        }

        return self::fromStrToDateTime($default);
    }

    /**
     * @param string|DateInterval $value
     * @return string|null
     */
    public static function fromTimeToStr($value): ?string
    {
        if (\is_a($value, DateInterval::class)) {
            return $value->format('%H:%I:%S');
        }
        if (\is_string($value)) {
            return self::fromStrToTime($value)->format('%H:%I:%S');
        }

        return null;
    }

    /**
     * @param string|DateInterval      $value
     * @param string|DateInterval|null $default
     *
     * @return DateInterval|null
     */
    public static function fromStrToTime($value, $default = null): ?DateInterval
    {
        if (!isset($value) && !isset($default)) {
            return null;
        }
        if (\is_a($value, DateInterval::class)) {
            return $value;
        }
        if (\is_string($value)) {
            try {
                $splits = \explode(':', $value, 3);

                switch (\count($splits)) {
                    case 0:
                        $result = DateInterval::createFromDateString($value);
                        break;
                    case 1:
                        $result = new DateInterval('PT' . (int)$splits[0] . 'H');
                        break;
                    case 2:
                        $result = new DateInterval('PT' . (int)$splits[0] . 'H' . (int)$splits[1] . 'M');
                        break;
                    case 3:
                        $result = new DateInterval(
                            'PT' . (int)$splits[0] . 'H' . (int)$splits[1] . 'M' . (int)$splits[2] . 'S'
                        );
                        break;
                    default:
                        $result = self::fromStrToTime($default);
                }

                return $result;
            } catch (Exception $e) {
                return self::fromStrToTime($default);
            }
        }

        return self::fromStrToTime($default);
    }

    /**
     * @param string|DateTime $value
     * @return string|null
     */
    public static function fromDateToStr($value): ?string
    {
        return self::formatDateTime($value, 'Y-m-d');
    }

    /**
     * @param string|DateTime      $value
     * @param string|DateTime|null $default
     * @return DateTime|null
     */
    public static function fromStrToDate($value, $default = null): ?DateTime
    {
        $dateTime = self::fromStrToDateTime($value, $default);

        if (isset($dateTime)) {
            $dateTime->setTime(0, 0);
        }

        return $dateTime;
    }

    /**
     * @param string|DateTime $value
     * @return string|null
     */
    public static function fromTimestampToStr($value): ?string
    {
        return self::formatDateTime($value, 'Y-m-d H:i:s.u');
    }

    /**
     * @param string|DateTime      $value
     * @param string|DateTime|null $default
     * @return DateTime|null
     */
    public static function fromStrToTimestamp($value, $default = null): ?DateTime
    {
        return self::fromStrToDateTime($value, $default);
    }

    /**
     * @param string    $value
     * @param bool|null $default
     *
     * @return bool
     */
    public static function fromCharToBool($value, $default = null): ?bool
    {
        if (\is_string($value)) {
            return \in_array(\strtoupper($value), ['Y', 'J', 'TRUE']);
        }

        return $default;
    }

    /**
     * @param bool $value
     * @return string
     */
    public static function fromBoolToChar(bool $value): string
    {
        return $value ? 'Y' : 'N';
    }

    /**
     * @param int       $value
     * @param bool|null $default
     * @return bool
     */
    public static function fromIntToBool($value, $default = null): ?bool
    {
        if (\is_numeric($value)) {
            return $value > 0;
        }

        return $default;
    }

    /**
     * @param bool $value
     * @return int
     */
    public static function fromBoolToInt(bool $value): int
    {
        return $value ? 1 : 0;
    }
}
