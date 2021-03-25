<?php declare(strict_types=1);

namespace JTL\Services\JTL;

use JTL\Smarty\JTLSmarty;
use JTL\Smarty\JTLSmartyTemplateClass;
use Smarty_Internal_Template;

/**
 * Interface CaptchaService
 * @package JTL\Services\JTL
 */
interface CaptchaServiceInterface
{
    /**
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @param JTLSmarty|JTLSmartyTemplateClass|Smarty_Internal_Template $smarty
     * @return string
     */
    public function getHeadMarkup($smarty): string;

    /**
     * @param JTLSmarty|JTLSmartyTemplateClass|Smarty_Internal_Template $smarty
     * @return string
     */
    public function getBodyMarkup($smarty): string;

    /**
     * @param  array $requestData
     * @return bool
     */
    public function validate(array $requestData): bool;
}
