<?php declare(strict_types=1);

namespace JTL\Mail\Renderer;

use JTL\Mail\Mail\MailInterface;
use JTL\Mail\Template\TemplateInterface;
use JTL\Smarty\JTLSmarty;
use JTL\Smarty\MailSmarty;

/**
 * Interface RendererInterface
 * @package JTL\Mail\Renderer
 */
interface RendererInterface
{
    /**
     * @return JTLSmarty|MailSmarty
     */
    public function getSmarty();

    /**
     * @param JTLSmarty|MailSmarty $smarty
     */
    public function setSmarty($smarty): void;

    /**
     * @param TemplateInterface $template
     * @param int               $languageID
     * @throws \SmartyException
     */
    public function renderTemplate(TemplateInterface $template, int $languageID): void;

    /**
     * @param string $id
     * @return string
     * @throws \SmartyException
     */
    public function renderHTML(string $id): string;

    /**
     * @param string $id
     * @return string
     * @throws \SmartyException
     */
    public function renderText(string $id): string;

    /**
     * @param MailInterface $mail
     * @throws \SmartyException
     */
    public function renderMail(MailInterface $mail): void;
}
