<?php declare(strict_types=1);

namespace JTL\Mail\Renderer;

use JTL\Mail\Mail\MailInterface;
use JTL\Mail\Template\Plugin;
use JTL\Mail\Template\TemplateInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Smarty\MailSmarty;

/**
 * Class SmartyRenderer
 * @package JTL\Mail\Renderer
 */
class SmartyRenderer implements RendererInterface
{
    /**
     * @var MailSmarty|JTLSmarty
     */
    private $smarty;

    /**
     * SmartyRenderer constructor.
     * @param MailSmarty|JTLSmarty $smarty
     */
    public function __construct($smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * @return MailSmarty|JTLSmarty
     */
    public function getSmarty()
    {
        return $this->smarty;
    }

    /**
     * @param MailSmarty|JTLSmarty $smarty
     */
    public function setSmarty($smarty): void
    {
        $this->smarty = $smarty;
    }

    /**
     * @inheritdoc
     */
    public function renderTemplate(TemplateInterface $template, int $languageID): void
    {
        if ($template === null) {
            return;
        }
        $model = $template->getModel();
        if ($model === null) {
            return;
        }
        $tplID = $model->getID() . '_' . $languageID . ($template instanceof Plugin ? '_' . $model->getPluginID() : '');
        $type  = $model->getType();
        \executeHook(\HOOK_MAILTOOLS_INC_SWITCH, [
            'mailsmarty'    => $this->getSmarty(),
            'renderer'      => $this,
            'mail'          => null,
            'kEmailvorlage' => $model->getID(),
            'kSprache'      => $languageID,
            'cPluginBody'   => '',
            'template'      => $template,
            'model'         => $model,
            'Emailvorlage'  => $model
        ]);
        $html = $type === 'text/html' || $type === 'html' ? $this->renderHTML($tplID) : '';
        $text = $this->renderText($tplID);
        $html = $this->renderLegalDataHTML($template, $languageID, $html);
        $text = $this->renderLegalDataText($template, $languageID, $text);

        $template->setHTML($html);
        $template->setText($text);
        $template->setSubject($this->parseSubject($model->getSubject($languageID)));
    }

    /**
     * @param TemplateInterface $template
     * @param int               $languageID
     * @param string            $html
     * @return string
     * @throws \SmartyException
     */
    private function renderLegalDataHTML(TemplateInterface $template, int $languageID, string $html): string
    {
        $legalData = $template->getLegalData();
        $model     = $template->getModel();
        $akz       = '';
        $legal     = '';
        if ($model === null || \mb_strlen($html) === 0) {
            return $html;
        }
        if ($model->getShowAKZ()) {
            $rendered = $this->renderHTML('core_jtl_anbieterkennzeichnung_' . $languageID);
            if (\mb_strlen($rendered) > 0) {
                $akz .= '<br /><br />' . $rendered;
            }
        }
        if ($model->getShowWRB()) {
            $legal .= $this->addLineBreakText($legalData['wrb']->cContentHtml, Shop::Lang()->get('wrb'));
        }
        if ($model->getShowWRBForm()) {
            $legal .= $this->addLineBreakText($legalData['wrbform']->cContentHtml, Shop::Lang()->get('wrbform'));
        }
        if ($model->getShowAGB()) {
            $legal .= $this->addLineBreakText($legalData['agb']->cContentHtml, Shop::Lang()->get('agb'));
        }
        if ($model->getShowDSE()) {
            $legal .= $this->addLineBreakText($legalData['dse']->cContentHtml, Shop::Lang()->get('dse'));
        }

        return \str_replace(['[AKZ]', '[LEGAL_DATA]'], [$akz, $legal], $html);
    }

    /**
     * @param TemplateInterface $template
     * @param int               $languageID
     * @param string            $text
     * @return string
     * @throws \SmartyException
     */
    private function renderLegalDataText(TemplateInterface $template, int $languageID, string $text): string
    {
        $legalData = $template->getLegalData();
        $model     = $template->getModel();
        $akz       = '';
        $legal     = '';
        if ($model === null) {
            return $text;
        }
        if ($model->getShowAKZ()) {
            $rendered = $this->renderText('core_jtl_anbieterkennzeichnung_' . $languageID);
            if (\mb_strlen($rendered) > 0) {
                $akz .= "\n\n" . $rendered;
            }
        }
        if ($model->getShowWRB()) {
            $legal .= $this->addLineBreakText($legalData['wrb']->cContentText, Shop::Lang()->get('wrb'), false);
        }
        if ($model->getShowWRBForm()) {
            $legal .= $this->addLineBreakText($legalData['wrbform']->cContentText, Shop::Lang()->get('wrbform'), false);
        }
        if ($model->getShowAGB()) {
            $legal .= $this->addLineBreakText($legalData['agb']->cContentText, Shop::Lang()->get('agb'), false);
        }
        if ($model->getShowDSE()) {
            $legal .= $this->addLineBreakText($legalData['dse']->cContentText, Shop::Lang()->get('dse'), false);
        }

        return \str_replace(['[AKZ]', '[LEGAL_DATA]'], [$akz, $legal], $text);
    }

    /**
     * @param string $text
     * @param string $heading
     * @param bool   $asHtml
     * @return string
     */
    private function addLineBreakText(string $text, string $heading, bool $asHtml = true): string
    {
        $breaks  = $asHtml ? '<br /><br />' : "\n\n";
        $heading = $asHtml ? '<h3>' . $heading . '</h3>' : $heading;

        return \mb_strlen($text) > 0
            ? $breaks . $heading . $breaks . $text
            : '';
    }

    /**
     * @inheritDoc
     */
    public function renderHTML(string $id): string
    {
        return $this->smarty->fetch('db:html_' . $id);
    }

    /**
     * @inheritDoc
     */
    public function renderText(string $id): string
    {
        return $this->smarty->fetch('db:text_' . $id);
    }

    /**
     * @inheritdoc
     */
    public function renderMail(MailInterface $mail): void
    {
        $model    = null;
        $template = $mail->getTemplate();
        if ($template !== null) {
            $model = $template->getModel();
        }
        if ($model === null) {
            $mail->setBodyText($this->smarty->fetch('string:' . $mail->getBodyText()));
            $mail->setBodyHTML($this->smarty->fetch('string:' . $mail->getBodyHTML()));
            $mail->setSubject($this->smarty->fetch('string:' . $mail->getSubject()));
        } else {
            $this->renderTemplate($template, $mail->getLanguage()->getId());
        }
    }

    /**
     * mail template subjects support a special syntax like "#smartyobject.value#"
     * this only works for #var# or #var.value# - not for deeper hierarchies
     *
     * @param string $subject
     * @return string|null
     */
    private function parseSubject(string $subject): ?string
    {
        if (\preg_match_all('/#(.*?)#/', $subject, $hits) === 0) {
            return $subject;
        }
        $search  = [];
        $replace = [];
        foreach ($hits[0] as $i => $match) {
            $parts = \explode('.', $hits[1][$i]);
            $count = \count($parts);
            if ($count === 0 || $count > 2) {
                continue;
            }
            $value = $this->getAssignedVar($parts[0]);
            if ($value === null) {
                continue;
            }
            if (\is_object($value) && isset($parts[1])) {
                $value = $this->getAssignedValue($value, $parts[1]);
            }
            if ($value !== null) {
                $search[]  = $match;
                $replace[] = $value;
            }
        }

        return \str_replace($search, $replace, $subject);
    }

    /**
     * @param object $object
     * @param string $name
     * @return mixed
     */
    private function getAssignedValue(object $object, string $name)
    {
        foreach (\get_object_vars($object) as $var => $value) {
            if ($var === $name) {
                return $value;
            }
            if (\mb_convert_case(\mb_substr($var, 1), \MB_CASE_LOWER) === $name) {
                return $value;
            }
        }
        $getter = 'get' . \ucfirst($name);
        if (\method_exists($object, $getter)) {
            return $object->$getter();
        }

        return null;
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function getAssignedVar(string $name)
    {
        return $this->smarty->getTemplateVars($name) ?? $this->smarty->getTemplateVars(\ucfirst($name));
    }
}
