<?php
/**
 * @global JTLSmarty $smarty
 */
require_once __DIR__ . '/../../../../admin/includes/admininclude.php';
require_once __DIR__ . '/../../autoload.php';

$stepId = isset($_REQUEST['stepId']) ? (int)$_REQUEST['stepId'] : 0;
$wizard = new \jtl\Wizard\ShopWizard($stepId);
\Shop::dbg($wizard->getQuestions());

if (isset($_POST['submit'])) {
    foreach ($wizard->getQuestions() as $questionId => $question) {
        $questionIndex = 'question-' . $questionId;
        if ($question->getType() === \jtl\Wizard\Question::TYPE_BOOL) {
            $wizard->answerQuestion($questionId, isset($_POST[$questionIndex]));
        } else {
            $wizard->answerQuestion($questionId, $_POST['question-' . $questionId]);
        }
    }
    $wizard->getStep()->finishStep();
}

$smarty->setTemplateDir(__DIR__ . '/src/templates')
       ->setCompileDir(__DIR__ . '/templates_c')
       ->assign('wizard', $wizard)
       ->display('gui.tpl');
