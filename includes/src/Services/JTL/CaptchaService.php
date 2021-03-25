<?php declare(strict_types=1);

namespace JTL\Services\JTL;

use JTL\Smarty\JTLSmarty;
use JTL\Smarty\JTLSmartyTemplateClass;

/**
 * Class CaptchaService
 * @package JTL\Services\JTL
 */
class CaptchaService implements CaptchaServiceInterface
{
    /**
     * @var CaptchaServiceInterface
     */
    private $fallbackCaptcha;

    /**
     * CaptchaService constructor.
     * @param CaptchaServiceInterface $fallbackCaptcha
     */
    public function __construct(CaptchaServiceInterface $fallbackCaptcha)
    {
        $this->fallbackCaptcha = $fallbackCaptcha;
    }

    /**
     * @inheritDoc
     */
    public function isConfigured(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $result = false;
        \executeHook(\HOOK_CAPTCHA_CONFIGURED, [
            'isConfigured' => &$result,
        ]);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return $this->fallbackCaptcha->isEnabled();
    }

    /**
     * @inheritDoc
     */
    public function getHeadMarkup($smarty): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if ($this->isConfigured()) {
            $result = '';
            \executeHook(\HOOK_CAPTCHA_MARKUP, [
                'getBody' => false,
                'markup'  => &$result,
            ]);
        } else {
            $result = $this->fallbackCaptcha->getHeadMarkup($smarty);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getBodyMarkup($smarty): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if ($this->isConfigured()) {
            $result = '';
            \executeHook(\HOOK_CAPTCHA_MARKUP, [
                'getBody' => true,
                'markup'  => &$result,
            ]);
        } else {
            $result = $this->fallbackCaptcha->getBodyMarkup($smarty);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function validate(array $requestData): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }

        if ($this->isConfigured()) {
            $result = false;
            \executeHook(\HOOK_CAPTCHA_VALIDATE, [
                'requestData' => $requestData,
                'isValid'     => &$result,
            ]);
        } else {
            $result = $this->fallbackCaptcha->validate($requestData);
        }

        return $result;
    }
}
