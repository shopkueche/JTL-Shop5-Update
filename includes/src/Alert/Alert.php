<?php

namespace JTL\Alert;

use JTL\Shop;

/**
 * Class Alert
 * @package JTL\Alert
 */
class Alert
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $key;

    /**
     * @var bool
     */
    private $dismissable = false;

    /**
     * @var int
     */
    private $fadeOut = self::FADE_NEVER;

    /**
     * @var bool
     */
    private $showInAlertListTemplate = true;

    /**
     * @var bool
     */
    private $saveInSession = false;

    /**
     * @var string
     */
    private $linkHref;

    /**
     * @var string
     */
    private $linkText;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var array|null
     */
    private $options;

    /**
     * @var string
     */
    private $id;

    public const TYPE_PRIMARY   = 'primary';
    public const TYPE_SECONDARY = 'secondary';
    public const TYPE_SUCCESS   = 'success';
    public const TYPE_DANGER    = 'danger';
    public const TYPE_WARNING   = 'warning';
    public const TYPE_INFO      = 'info';
    public const TYPE_LIGHT     = 'light';
    public const TYPE_DARK      = 'dark';

    //used for former cFehler / cHinhweis
    public const TYPE_ERROR = 'error';
    public const TYPE_NOTE  = 'note';

    public const FADE_FAST   = 3000;
    public const FADE_SLOW   = 8000;
    public const FADE_MEDIUM = 5000;
    public const FADE_NEVER  = 0;

    public const ICON_WARNING = 'warning';
    public const ICON_INFO    = 'info-circle';
    public const ICON_CHECK   = 'check-circle';

    /**
     * @return array
     */
    public function __sleep(): array
    {
        $propertiesToSave = ['type', 'message', 'key'];
        if ($this->getOptions() !== null) {
            $propertiesToSave[] = 'options';
        }

        return $propertiesToSave;
    }

    /**
     *
     */
    public function __wakeup()
    {
        $this->initAlert();
    }

    /**
     * @param string     $message
     * @param string     $type
     * @param string     $key
     * @param array|null $options
     * constructor
     */
    public function __construct(string $type, string $message, string $key, array $options = null)
    {
        $this->setType($type)
             ->setMessage($message)
             ->setKey($key)
             ->setOptions($options);

        $this->initAlert();
    }

    /**
     * @return void
     */
    private function initAlert(): void
    {
        switch ($this->getType()) {
            case self::TYPE_DANGER:
            case self::TYPE_ERROR:
            case self::TYPE_WARNING:
                $this->setDismissable(true)
                     ->setIcon(self::ICON_WARNING);
                break;
            case self::TYPE_INFO:
            case self::TYPE_NOTE:
                $this->setIcon(self::ICON_INFO);
                break;
            case self::TYPE_SUCCESS:
                $this->setFadeOut(self::FADE_SLOW)
                     ->setIcon(self::ICON_CHECK);
                break;
            default:
                break;
        }

        if ($this->getOptions() !== null) {
            foreach ($this->getOptions() as $optionKey => $optionValue) {
                $methodName = 'set' . \ucfirst($optionKey);
                if (\is_callable([$this, $methodName])) {
                    $this->$methodName($optionValue);
                }
            }
        }
        if ($this->getSaveInSession()) {
            $this->addToSession();
        }
    }

    /**
     * @return void
     */
    public function display(): void
    {
        echo Shop::Smarty()->assign('alert', $this)
            ->fetch('snippets/alert.tpl');

        if ($this->getSaveInSession()) {
            $this->removeFromSession();
        }
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return $this
     */
    private function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    private function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this
     */
    private function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDismissable(): bool
    {
        return $this->dismissable;
    }

    /**
     * @param bool $dismissable
     * @return $this
     */
    private function setDismissable(bool $dismissable): self
    {
        $this->dismissable = $dismissable;

        return $this;
    }

    /**
     * @return int
     */
    public function getFadeOut(): int
    {
        return $this->fadeOut;
    }

    /**
     * @param int $fadeOut
     * @return $this
     */
    private function setFadeOut(int $fadeOut): self
    {
        $this->fadeOut = $fadeOut;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSaveInSession(): bool
    {
        return $this->saveInSession;
    }

    /**
     * @param bool $saveInSession
     * @return $this
     */
    private function setSaveInSession(bool $saveInSession): self
    {
        $this->saveInSession = $saveInSession;

        return $this;
    }

    /**
     * @return bool
     */
    public function getShowInAlertListTemplate(): bool
    {
        return $this->showInAlertListTemplate;
    }

    /**
     * @param bool $showInAlertListTemplate
     * @return $this
     */
    private function setShowInAlertListTemplate(bool $showInAlertListTemplate): self
    {
        $this->showInAlertListTemplate = $showInAlertListTemplate;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLinkHref(): ?string
    {
        return $this->linkHref;
    }

    /**
     * @param string $linkHref
     * @return $this
     */
    private function setLinkHref(string $linkHref): self
    {
        $this->linkHref = $linkHref;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLinkText(): ?string
    {
        return $this->linkText;
    }

    /**
     * @param string $linkText
     * @return $this
     */
    private function setLinkText(string $linkText): self
    {
        $this->linkText = $linkText;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     * @return $this
     */
    private function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return array|null
     */
    private function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @param array|null $options
     * @return $this
     */
    private function setOptions(?array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    private function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * save Alert in Session
     */
    private function addToSession(): void
    {
        if (!isset($_SESSION['alerts'])) {
            $_SESSION['alerts'] = [];
        }
        $_SESSION['alerts'][$this->getKey()] = \serialize($this);
    }

    /**
     * remove Alert from Session
     */
    private function removeFromSession(): void
    {
        if (isset($_SESSION['alerts'][$this->getKey()])) {
            unset($_SESSION['alerts'][$this->getKey()]);
        }
    }

    /**
     * @return string
     */
    public function getCssType(): string
    {
        switch ($this->getType()) {
            case self::TYPE_ERROR:
                return self::TYPE_DANGER;
            case self::TYPE_NOTE:
                return self::TYPE_INFO;
            default:
                return $this->getType();
        }
    }
}
