<?php declare(strict_types=1);

namespace JTL\Backend\Wizard;

use JsonSerializable;
use JTL\Backend\Wizard\Steps\ErrorCode;
use JTL\DB\DbInterface;
use JTL\Session\Backend;
use JTL\Update\MigrationTableTrait;
use JTL\Update\MigrationTrait;
use stdClass;

/**
 * Class AbstractQuestion
 * @package JTL\Backend\Wizard
 */
abstract class AbstractQuestion implements JsonSerializable, QuestionInterface
{
    use MigrationTrait,
        MigrationTableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $subheading;

    /**
     * @var string
     */
    protected $subheadingDescription;

    /**
     * @var string
     */
    protected $summaryText;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var int|null
     */
    protected $dependency;

    /**
     * @var callable
     */
    protected $onSave;

    /**
     * @var SelectOption[]
     */
    protected $options = [];

    /**
     * @var bool
     */
    protected $multiSelect = false;

    /**
     * @var bool
     */
    protected $required = true;

    /**
     * @var bool
     */
    protected $fullWidth = false;

    /**
     * @var callable
     */
    protected $validation;

    /**
     * @var string
     */
    protected $scope;

    /**
     * AbstractQuestion constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->setDB($db);
        $this->setValidation();
    }

    /**
     * @inheritDoc
     */
    public function answerFromPost(array $post)
    {
        $data = $post['question-' . $this->getID()] ?? null;
        if ($this->getType() === QuestionType::BOOL) {
            $value = $data === 'on';
        } else {
            $value = $data ?? '';
        }
        $this->setValue($value, false);

        return $value;
    }

    /**
     * @param string $configName
     * @param mixed  $value
     * @return int
     */
    public function updateConfig(string $configName, $value): int
    {
        return $this->db->update('teinstellungen', 'cName', $configName, (object)['cWert' => $value]);
    }

    /**
     * @inheritDoc
     */
    public function save(): int
    {
        if (($validationError = $this->validate()) !== ErrorCode::OK) {
            return $validationError;
        }
        $cb = $this->getOnSave();
        if (\is_callable($cb)) {
            $cb($this);
        }

        return ErrorCode::OK;
    }

    /**
     * @inheritDoc
     */
    public function loadAnswer(array $data): void
    {
        $value = $data[$this->getID()] ?? null;
        if ($value !== null) {
            $this->setValue($value);
        }
    }

    /**
     * @inheritDoc
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @inheritDoc
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @inheritDoc
     */
    public function getSubheading(): ?string
    {
        return $this->subheading;
    }

    /**
     * @inheritDoc
     */
    public function setSubheading(string $subheading): void
    {
        $this->subheading = $subheading;
    }

    /**
     * @inheritDoc
     */
    public function getSubheadingDescription(): ?string
    {
        return $this->subheadingDescription;
    }

    /**
     * @inheritDoc
     */
    public function setSubheadingDescription(string $subheadingDescription): void
    {
        $this->subheadingDescription = $subheadingDescription;
    }

    /**
     * @inheritDoc
     */
    public function getSummaryText(): ?string
    {
        return $this->summaryText;
    }

    /**
     * @inheritDoc
     */
    public function setSummaryText(string $summaryText): void
    {
        $this->summaryText = $summaryText;
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @inheritDoc
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @inheritDoc
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function setValue($value, bool $sessionFirst = true): void
    {
        $wizard = Backend::get('wizard');
        $idx    = 'question-' . $this->getID();
        if ($sessionFirst && isset($wizard[$idx])) {
            $this->value = $wizard[$idx];
        } else {
            $this->value = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function getDependency(): ?int
    {
        return $this->dependency;
    }

    /**
     * @inheritDoc
     */
    public function setDependency(int $dependency): void
    {
        $this->dependency = $dependency;
    }

    /**
     * @inheritDoc
     */
    public function getOnSave(): ?callable
    {
        return $this->onSave;
    }

    /**
     * @inheritDoc
     */
    public function setOnSave(callable $onSave): void
    {
        $this->onSave = $onSave;
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function addOption(SelectOption $option): void
    {
        $this->options[] = $option;
    }

    /**
     * @inheritDoc
     */
    public function isMultiSelect(): bool
    {
        return $this->multiSelect;
    }

    /**
     * @inheritDoc
     */
    public function setIsMultiSelect(bool $multi): void
    {
        $this->multiSelect = $multi;
    }

    /**
     * @inheritDoc
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @inheritDoc
     */
    public function setIsRequired(bool $required): void
    {
        $this->required = $required;
    }

    /**
     * @inheritDoc
     */
    public function isFullWidth(): bool
    {
        return $this->fullWidth;
    }

    /**
     * @inheritDoc
     */
    public function setIsFullWidth(bool $fullWidth): void
    {
        $this->fullWidth = $fullWidth;
    }

    /**
     * @inheritDoc
     */
    public function setValidation(?callable $validation = null): void
    {
        $this->validation = $validation ?? function (QuestionInterface $question) {
                return (new QuestionValidation($question))->getValidationError();
        };
    }

    /**
     * @inheritDoc
     */
    public function getValidation(): callable
    {
        return $this->validation;
    }

    /**
     * @inheritDoc
     */
    public function validate(): int
    {
        $cb = $this->getValidation();
        if (\is_callable($cb)) {
            return $cb($this);
        }

        return ErrorCode::OK;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): stdClass
    {
        $data = new stdClass();
        foreach (\get_object_vars($this) as $k => $v) {
            $data->$k = $v;
        }

        return $data;
    }

    /**
     * @return null|string
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     */
    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }
}
