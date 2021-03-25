<?php declare(strict_types=1);

namespace JTL\Model;

/**
 * Class DataAttribute
 * @package JTL\Model
 */
class DataAttribute
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $dataType;

    /**
     * @var bool
     */
    public $nullable = true;

    /**
     * @var mixed
     */
    public $default;

    /**
     * @var bool
     */
    public $isPrimaryKey = false;

    /**
     * @var string|null
     */
    public $foreignKey;

    /**
     * @var string|null
     */
    public $foreignKeyChild;

    /**
     * @var bool
     */
    public $dynamic = false;

    /**
     * @var InputConfig
     */
    public $inputConfig;

    /**
     * DataAttribute constructor.
     */
    public function __construct()
    {
        $this->inputConfig = new InputConfig();
    }

    /**
     * @return InputConfig
     */
    public function getInputConfig(): InputConfig
    {
        return $this->inputConfig;
    }

    /**
     * @param InputConfig $inputConfig
     * @return DataAttribute
     */
    public function setInputConfig(InputConfig $inputConfig): DataAttribute
    {
        $this->inputConfig = $inputConfig;

        return $this;
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
     * @return DataAttribute
     */
    public function setName(string $name): DataAttribute
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * @param string $dataType
     * @return DataAttribute
     */
    public function setDataType(string $dataType): DataAttribute
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @param bool $nullable
     * @return DataAttribute
     */
    public function setNullable(bool $nullable): DataAttribute
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     * @return DataAttribute
     */
    public function setDefault($default): DataAttribute
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    /**
     * @param bool $isPrimaryKey
     * @return DataAttribute
     */
    public function setIsPrimaryKey(bool $isPrimaryKey): DataAttribute
    {
        $this->isPrimaryKey = $isPrimaryKey;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getForeignKey(): ?string
    {
        return $this->foreignKey;
    }

    /**
     * @param string|null $foreignKey
     * @return DataAttribute
     */
    public function setForeignKey(?string $foreignKey): DataAttribute
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getForeignKeyChild(): ?string
    {
        return $this->foreignKeyChild;
    }

    /**
     * @param string|null $foreignKeyChild
     * @return DataAttribute
     */
    public function setForeignKeyChild(?string $foreignKeyChild): DataAttribute
    {
        $this->foreignKeyChild = $foreignKeyChild;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDynamic(): bool
    {
        return $this->dynamic;
    }

    /**
     * @param bool $dynamic
     * @return DataAttribute
     */
    public function setDynamic(bool $dynamic): DataAttribute
    {
        $this->dynamic = $dynamic;

        return $this;
    }

    /**
     * Creates a new DataAttribute instance
     *
     * @param string      $name - name of the attribute
     * @param string      $dataType - type of the attribute
     * @param null|mixed  $default - default value of the attribute
     * @param bool        $nullable - true if the attribute is nullable, false otherwise
     * @param bool        $isPrimaryKey - true if the attribute is the primary key, false otherwise
     * @param string|null $foreignKey
     * @param string|null $foreignKeyChild
     * @param bool        $dynamic
     * @return self
     */
    public static function create(
        string $name,
        string $dataType,
        $default = null,
        bool $nullable = true,
        bool $isPrimaryKey = false,
        string $foreignKey = null,
        $foreignKeyChild = null,
        bool $dynamic = false
    ): self {
        $item = new self();
        $item->setName($name)
            ->setDataType($dataType)
            ->setDefault($default)
            ->setNullable($nullable)
            ->setIsPrimaryKey($isPrimaryKey)
            ->setForeignKey($foreignKey)
            ->setForeignKeyChild($foreignKeyChild)
            ->setDynamic($dynamic);

        return $item;
    }
}
