<?php declare(strict_types=1);

namespace JTL\Backend\Wizard;

use JTL\Backend\Wizard\Steps\ErrorCode;
use JTL\Helpers\Text;
use JTL\VerificationVAT\VATCheck;
use JTL\VerificationVAT\VATCheckInterface;

/**
 * Class QuestionValidation
 * @package JTL\Backend\Wizard
 */
class QuestionValidation
{
    /**
     * @var Question
     */
    private $question;

    /**
     * @var int
     */
    private $validationError;

    /**
     * QuestionValidation constructor.
     * @param QuestionInterface $question
     * @param bool              $defaultValidation
     */
    public function __construct(QuestionInterface $question, bool $defaultValidation = true)
    {
        $this->question = $question;

        if ($defaultValidation) {
            $this->defaultValidation();
        }
    }

    /**
     * @return bool
     */
    public function checkRequired(): bool
    {
        if ($this->question->isRequired() && $this->valueIsEmpty()) {
            $this->setValidationError(ErrorCode::ERROR_REQUIRED);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function checkEmail(): bool
    {
        if ($this->question->getType() === QuestionType::EMAIL
            && !empty($this->question->getValue())
            && Text::filterEmailAddress($this->question->getValue()) === false
        ) {
            $this->setValidationError(ErrorCode::INVALID_EMAIL);

            return false;
        }

        return true;
    }

    /**
     * @param bool $pluginMsg
     * @return bool
     */
    public function checkSSL(bool $pluginMsg = false): bool
    {
        if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') && !$this->valueIsEmpty()) {
            $pluginMsg
                ? $this->setValidationError(ErrorCode::ERROR_SSL_PLUGIN)
                : $this->setValidationError(ErrorCode::ERROR_SSL);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function checkVAT(): bool
    {
        if ($this->valueIsEmpty()) {
            return true;
        }
        $vatCheck       = new VATCheck(\trim($this->question->getValue()));
        $resultVatCheck = $vatCheck->doCheckID();
        //only check format
        if ($resultVatCheck['errortype'] === 'parse'
            && $resultVatCheck['errorcode'] !== VATCheckInterface::ERR_COUNTRY_NOT_FOUND
            && $resultVatCheck['success'] === false
        ) {
            $this->setValidationError(ErrorCode::ERROR_VAT);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function valueIsEmpty(): bool
    {
        return empty($this->question->getValue())
            || (\is_array($this->question->getValue()) && \count(\array_filter($this->question->getValue())) === 0);
    }

    /**
     *
     */
    private function defaultValidation(): void
    {
        if ($this->checkRequired()) {
            $this->checkEmail();
        }
    }

    /**
     * @return int
     */
    public function getValidationError(): int
    {
        return $this->validationError ?? ErrorCode::OK;
    }

    /**
     * @param int $validationError
     */
    private function setValidationError(int $validationError): void
    {
        $this->validationError = $validationError;
    }
}
