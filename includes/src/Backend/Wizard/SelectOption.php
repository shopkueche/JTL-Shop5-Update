<?php declare(strict_types=1);

namespace JTL\Backend\Wizard;

/**
 * Class SelectOption
 * @package JTL\Backend\Wizard
 */
final class SelectOption
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $logoPath;

    /**
     * @var string
     */
    private $link;

    /**
     * @var string
     */
    private $description;

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    /**
     * @param string $logoPath
     */
    public function setLogoPath(string $logoPath): void
    {
        $this->logoPath = $logoPath;
    }

    /**
     * @return null|string
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param $val
     * @return bool
     */
    public function isSelected($val): bool
    {
        return $val === $this->getValue() || (\is_array($val) && \in_array($this->getValue(), $val, true));
    }
}
