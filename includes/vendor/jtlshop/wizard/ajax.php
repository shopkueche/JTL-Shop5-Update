<?php
/**
 * @copyright JTL-Software-GmbH
 */

use jtl\Wizard\Question;
use jtl\Wizard\ShopWizard;

require_once __DIR__ . '/../../autoload.php';

$questions = $_POST['questions'];
$stepId    = isset($_REQUEST['stepId']) ? $_REQUEST['stepId'] : 0;
$wizard    = new ShopWizard($stepId);

foreach ($wizard->getQuestions() as $questionId => $question) {
    if ($question->getType() === Question::TYPE_BOOL) {
        $wizard->answerQuestion(
            $questionId, $questions[$questionId] === 'true'
        );
    } else {
        $wizard->answerQuestion($questionId, $questions[$questionId]);
    }
}

echo json_encode($wizard->getAvailableQuestions());
