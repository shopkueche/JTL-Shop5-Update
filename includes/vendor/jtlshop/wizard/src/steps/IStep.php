<?php
/**
 * @copyright JTL-Software-GmbH
 */

namespace jtl\Wizard\steps;

/**
 * Interface IStep
 */
interface IStep
{
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return array
     */
    public function getQuestions();

    /**
     * @param int   $questionId
     * @param mixed $value
     * @return mixed
     */
    public function answerQuestion($questionId, $value);

    /**
     * @param bool $jumpToNext
     * @return mixed
     */
    public function finishStep($jumpToNext);
}
