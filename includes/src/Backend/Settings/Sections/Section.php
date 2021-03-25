<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: mo
 * Date: 2019-01-25
 * Time: 14:40
 */

namespace JTL\Backend\Settings\Sections;

use JTL\DB\DbInterface;
use JTL\Smarty\JTLSmarty;

/**
 * Interface Section
 * @package Backend\Settings\Sections
 */
interface Section
{
    /**
     * SettingSection constructor.
     * @param DbInterface $db
     * @param JTLSmarty   $smarty
     */
    public function __construct(DbInterface $db, JTLSmarty $smarty);

    /**
     * @param object $conf
     * @param object $confValue
     * @return bool
     */
    public function validate($conf, &$confValue): bool;

    /**
     * @param object $conf
     * @param mixed  $value
     */
    public function setValue(&$conf, $value): void;

    /**
     * @return string
     */
    public function getSectionMarkup(): string;

    /**
     * @param object $conf
     * @return string
     */
    public function getValueMarkup($conf): string;
}
