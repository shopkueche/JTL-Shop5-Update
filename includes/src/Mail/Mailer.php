<?php declare(strict_types=1);

namespace JTL\Mail;

use JTL\Emailhistory;
use JTL\Mail\Hydrator\HydratorInterface;
use JTL\Mail\Mail\MailInterface;
use JTL\Mail\Renderer\RendererInterface;
use JTL\Mail\Validator\ValidatorInterface;
use JTL\Shop;
use JTL\Shopsetting;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use stdClass;

/**
 * Class Mailer
 * @package JTL\Mail
 */
class Mailer
{
    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var array
     */
    private $config;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Mailer constructor.
     * @param HydratorInterface  $hydrator
     * @param RendererInterface  $renderer
     * @param Shopsetting        $settings
     * @param ValidatorInterface $validator
     */
    public function __construct(
        HydratorInterface $hydrator,
        RendererInterface $renderer,
        Shopsetting $settings,
        ValidatorInterface $validator
    ) {
        $this->hydrator  = $hydrator;
        $this->renderer  = $renderer;
        $this->config    = $settings->getAll();
        $this->validator = $validator;
    }

    /**
     * @return RendererInterface
     */
    public function getRenderer(): RendererInterface
    {
        return $this->renderer;
    }

    /**
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer): void
    {
        $this->renderer = $renderer;
    }

    /**
     * @return HydratorInterface
     */
    public function getHydrator(): HydratorInterface
    {
        return $this->hydrator;
    }

    /**
     * @param HydratorInterface $hydrator
     */
    public function setHydrator(HydratorInterface $hydrator): void
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @param ValidatorInterface $validator
     */
    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * @return stdClass
     */
    private function getMethod(): stdClass
    {
        $method                = new stdClass();
        $method->methode       = $this->config['emails']['email_methode'];
        $method->sendmail_pfad = $this->config['emails']['email_sendmail_pfad'];
        $method->smtp_hostname = $this->config['emails']['email_smtp_hostname'];
        $method->smtp_port     = $this->config['emails']['email_smtp_port'];
        $method->smtp_auth     = $this->config['emails']['email_smtp_auth'];
        $method->smtp_user     = $this->config['emails']['email_smtp_user'];
        $method->smtp_pass     = $this->config['emails']['email_smtp_pass'];
        $method->SMTPSecure    = $this->config['emails']['email_smtp_verschluesselung'];
        $method->SMTPAutoTLS   = !empty($method->SMTPSecure);

        return $method;
    }

    /**
     * @param PHPMailer $phpmailer
     * @return Mailer
     */
    private function initMethod(PHPMailer $phpmailer): self
    {
        $method = $this->getMethod();
        switch ($method->methode) {
            case 'mail':
                $phpmailer->isMail();
                break;
            case 'sendmail':
                $phpmailer->isSendmail();
                $phpmailer->Sendmail = $method->sendmail_pfad;
                break;
            case 'qmail':
                $phpmailer->isQmail();
                break;
            case 'smtp':
                $phpmailer->isSMTP();
                $phpmailer->Host          = $method->smtp_hostname;
                $phpmailer->Port          = $method->smtp_port;
                $phpmailer->SMTPKeepAlive = true;
                $phpmailer->SMTPAuth      = $method->smtp_auth;
                $phpmailer->Username      = $method->smtp_user;
                $phpmailer->Password      = $method->smtp_pass;
                $phpmailer->SMTPSecure    = $method->SMTPSecure;
                $phpmailer->SMTPAutoTLS   = $method->SMTPAutoTLS;
                break;
        }

        return $this;
    }

    /**
     * @param PHPMailer     $phpmailer
     * @param MailInterface $mail
     * @return Mailer
     * @throws Exception
     */
    private function addAttachments(PHPMailer $phpmailer, MailInterface $mail): self
    {
        foreach ($mail->getPdfAttachments() as $pdf) {
            $phpmailer->addAttachment(
                $pdf->getFullPath(),
                $pdf->getName() . '.pdf',
                $pdf->getEncoding(),
                $pdf->getMime()
            );
        }
        foreach ($mail->getAttachments() as $attachment) {
            $phpmailer->addAttachment(
                $attachment->getFullPath(),
                $attachment->getName(),
                $attachment->getEncoding(),
                $attachment->getMime()
            );
        }

        return $this;
    }

    /**
     * @param MailInterface $mail
     * @throws \Exception
     */
    private function log(MailInterface $mail): void
    {
        $id       = 0;
        $template = $mail->getTemplate();
        if ($template !== null) {
            $model = $template->getModel();
            $id    = $model === null ? 0 : $model->getID();
        }
        $history = new Emailhistory();
        $history->setEmailvorlage($id)
            ->setSubject($mail->getSubject())
            ->setFromName($mail->getFromName())
            ->setFromEmail($mail->getFromMail())
            ->setToName($mail->getToName() ?? '')
            ->setToEmail($mail->getToMail())
            ->setSent('NOW()')
            ->save();
    }

    /**
     * @param MailInterface $mail
     */
    private function hydrate(MailInterface $mail): void
    {
        $this->hydrator->hydrate($mail->getData(), $mail->getLanguage());
        $this->hydrator->add('absender_name', $mail->getFromName());
        $this->hydrator->add('absender_mail', $mail->getFromMail());
    }

    /**
     * @param MailInterface $mail
     * @return MailInterface
     * @throws \SmartyException
     */
    private function renderTemplate(MailInterface $mail): MailInterface
    {
        $template = $mail->getTemplate();
        if ($template !== null) {
            $template->setConfig($this->config);
            $template->preRender($this->renderer->getSmarty(), $mail->getData());
            $template->render($this->renderer, $mail->getLanguage()->getId(), $mail->getCustomerGroupID());
            $mail->setBodyHTML($template->getHTML());
            $mail->setBodyText($template->getText());
            $mail->setSubject($template->getSubject());
        } else {
            $this->renderer->renderMail($mail);
        }

        return $mail;
    }

    /**
     * @param MailInterface $mail
     * @return bool
     * @throws Exception
     */
    private function sendViaPHPMailer(MailInterface $mail): bool
    {
        $phpmailer             = new PHPMailer();
        $phpmailer->AllowEmpty = true;
        $phpmailer->CharSet    = \JTL_CHARSET;
        $phpmailer->Timeout    = \SOCKET_TIMEOUT;
        $phpmailer->setLanguage($mail->getLanguage()->getIso639());
        $phpmailer->setFrom($mail->getFromMail(), $mail->getFromName());
        $phpmailer->addAddress($mail->getToMail(), $mail->getToName());
        $phpmailer->addReplyTo($mail->getReplyToMail(), $mail->getReplyToName());
        $phpmailer->Subject = $mail->getSubject();
        foreach ($mail->getCopyRecipients() as $recipient) {
            $phpmailer->addBCC($recipient);
        }
        $this->initMethod($phpmailer);
        if ($mail->getBodyHTML()) {
            $phpmailer->isHTML();
            $phpmailer->Body    = $mail->getBodyHTML();
            $phpmailer->AltBody = $mail->getBodyText();
        } else {
            $phpmailer->isHTML(false);
            $phpmailer->Body = $mail->getBodyText();
        }
        if (\mb_strlen($phpmailer->Body) === 0) {
            Shop::Container()->getLogService()->warning('Empty body for mail ' . $phpmailer->Subject);
        }
        $this->addAttachments($phpmailer, $mail);
        $sent = $phpmailer->send();
        $mail->setError($phpmailer->ErrorInfo);

        return $sent;
    }

    /**
     * @param MailInterface $mail
     * @return bool
     * @throws Exception
     * @throws \SmartyException
     */
    public function send(MailInterface $mail): bool
    {
        \executeHook(\HOOK_MAIL_PRERENDER, [
            'mailer' => $this,
            'mail'   => $mail,
        ]);
        $this->hydrate($mail);
        $mail = $this->renderTemplate($mail);
        if (!$this->validator->validate($mail)) {
            $mail->setError('Mail failed validation');

            return false;
        }
        \executeHook(\HOOK_MAILTOOLS_SENDEMAIL_ENDE, [
            'mailsmarty'    => $this->renderer->getSmarty(),
            'mail'          => $mail,
            'kEmailvorlage' => 0,
            'kSprache'      => $mail->getLanguage()->getId(),
            'cPluginBody'   => '',
            'Emailvorlage'  => null,
            'template'      => $mail->getTemplate()
        ]);
        $sent = $this->sendViaPHPMailer($mail);
        if ($sent) {
            $this->log($mail);
        }
        \executeHook(\HOOK_MAILTOOLS_VERSCHICKEMAIL_GESENDET);

        return $sent;
    }
}
