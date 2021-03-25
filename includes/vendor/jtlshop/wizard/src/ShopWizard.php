<?php
/**
 * @copyright JTL-Software-GmbH
 */

namespace jtl\Wizard;

use jtl\Wizard\steps\AdditionalLinks;
use jtl\Wizard\steps\CustomerFormStep;
use jtl\Wizard\steps\GlobalSettingsStep;
use jtl\Wizard\steps\Step;
use jtl\Wizard\steps\SyncStatusStep;

/**
 * Class ShopWizard
 * @package jtl\Wizard
 */
class ShopWizard
{
    /**
     * @var SyncStatusStep
     */
    private $step;

    /**
     * @var int
     */
    private $stepId;

    /**
     * Shop4Wizard constructor.
     * @param int $stepId
     */
    public function __construct($stepId = 0)
    {
        $this->stepId = (int)$stepId;
        switch ($this->stepId) {
            case 3:
                $this->step = new AdditionalLinks($this);
                break;
            case 2:
                $this->step = new CustomerFormStep($this);
                break;
            case 1:
                $this->step = new GlobalSettingsStep($this);
                break;
            case 0:
            default:
                $this->step = new SyncStatusStep($this);
                break;
        }
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->step->getTitle();
    }

    /**
     * @return Question[]
     */
    public function getQuestions()
    {
        return $this->step->getQuestions();
    }

    /**
     * @return Question[]
     */
    public function getFilteredQuestions()
    {
        return $this->step->getFilteredQuestions();

        $questions = $this->step->getFilteredQuestions();
        foreach ($questions as $i => $question) {
            $question->setID($i);
        }

        return $questions;
    }

    /**
     * @return Question[]
     */
    public function getAvailableQuestions()
    {
        return $this->step->getAvailableQuestions();
    }

    /**
     * @return int
     */
    public function getStepId()
    {
        return $this->stepId;
    }

    /**
     * @param int   $questionId
     * @param mixed $value
     */
    public function answerQuestion($questionId, $value)
    {
        $this->step->answerQuestion($questionId, $value);
    }

    /**
     * @param int   $questionID
     * @param mixed $value
     * @return $this
     */
    public function answerQuestionByID($questionID, $value)
    {
        $this->step->answerQuestionByID($questionID, $value);

        return $this;
    }

    /**
     * @return SyncStatusStep
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param null|Step $step
     */
    public function setStep($step)
    {
        $this->step = $step;
        ++$this->stepId;
    }
}
