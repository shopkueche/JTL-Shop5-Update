<?php
/**
 * @copyright JTL-Software-GmbH
 */

namespace jtl\Wizard\steps;

use jtl\Wizard\Question;

/**
 * Class Step
 * @package jtl\Wizard\steps
 */
abstract class Step
{
    /**
     * @var Question[]
     */
    protected $questions = [];

    /**
     * @return string
     */
    abstract public function getTitle();

    /**
     * @return array
     */
    public function getAvailableQuestions()
    {
        return $this->getQuestions();
    }

    /**
     * @return Question[]
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @param int   $questionId
     * @param mixed $value
     */
    public function answerQuestion($questionId, $value)
    {
        $this->questions[$questionId]->setValue($value);
    }

    /**
     * @param $questionID
     * @param $value
     * @return $this
     */
    public function answerQuestionByID($questionID, $value)
    {
        foreach ($this->questions as $question) {
            if ($question->getID() === (int)$questionID) {
                $question->setValue($value);

                return $this;
            }
        }

        return $this;
    }

    /**
     * @return array|Question[]
     */
    public function getFilteredQuestions()
    {
        return \array_filter($this->questions, function ($question) {
            $test = $question->getDependency();
            if ($test === null) {
                return true;
            }
            foreach ($this->questions as $q) {
                if ($q->getID() === $test) {
                    return !empty($q->getValue());
                }
            }

            return false;
        });
    }

    /**
     * @param bool $jumpToNext
     * @return mixed
     */
    abstract public function finishStep($jumpToNext = true);
}
