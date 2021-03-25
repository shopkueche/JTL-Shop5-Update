<?php declare(strict_types=1);

namespace JTL\Backend\Wizard\Steps;

use Illuminate\Support\Collection;
use JTL\Backend\Wizard\ExtensionInstaller;
use JTL\Backend\Wizard\Question;
use JTL\Backend\Wizard\QuestionInterface;
use JTL\Backend\Wizard\QuestionType;
use JTL\Backend\Wizard\QuestionValidation;
use JTL\Backend\Wizard\SelectOption;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Recommendation\Manager;
use JTL\Recommendation\Recommendation;
use JTL\Services\JTL\AlertServiceInterface;
use function Functional\map;

/**
 * Class PaymentPlugins
 * @package JTL\Backend\Wizard\Steps
 */
final class PaymentPlugins extends AbstractStep
{
    /**
     * PaymentPlugins constructor.
     * @param DbInterface           $db
     * @param AlertServiceInterface $alertService
     */
    public function __construct(DbInterface $db, AlertServiceInterface $alertService)
    {
        parent::__construct($db, $alertService);
        $collection = new Collection();
        $this->setTitle(__('stepFour'));

        $paymentMethods = map($db->query(
            "SELECT cModulId FROM tzahlungsart WHERE nNutzbar = 1 AND cModulId LIKE 'za_%'",
            ReturnType::ARRAY_OF_OBJECTS
        ), static function ($e) {
            return __($e->cModulId);
        });

        $this->setDescription(\sprintf(__('stepFourDesc'), \implode(', ', $paymentMethods)));
        $this->setID(4);

        $scope           = Manager::SCOPE_WIZARD_PAYMENT_PROVIDER;
        $recommendations = new Manager($this->alertService, $scope);

        $question = new Question($db);
        $question->setID(10);
        $question->setSubheading(__('weRecommend') . ':');
        $question->setSubheadingDescription(__('weRecommendPaymentDesc'));
        $question->setSummaryText(__('paymentTypes'));
        $question->setType(QuestionType::PLUGIN);
        $question->setIsFullWidth(true);
        $question->setIsRequired(false);
        $question->setValue(false);
        $question->setScope($scope);
        $question->setValidation(static function (QuestionInterface $question) {
            $questionValidation = new QuestionValidation($question);
            $questionValidation->checkSSL(true);

            return $questionValidation->getValidationError();
        });

        $recommendations->getRecommendations()->each(
            static function (Recommendation $recommendation) use ($question, $collection) {
                $option = new SelectOption();
                $option->setName($recommendation->getTitle());
                $option->setValue($recommendation->getId());
                $option->setLogoPath($recommendation->getPreviewImage());
                $option->setDescription($recommendation->getTeaser());
                $option->setLink($recommendation->getUrl());
                $question->addOption($option);
                $collection->push($recommendation);
            }
        );

        $question->setOnSave(function (QuestionInterface $question) use ($collection) {
            $requested = $question->getValue();
            if (!\is_array($requested) || \count($requested) === 0) {
                return;
            }
            $installer = new ExtensionInstaller($this->db);
            $installer->setRecommendations($collection);
            $errorMsg = $installer->onSaveStep($requested);
            if ($errorMsg !== '') {
                $error = new Error($this->getID(), $question->getID(), ErrorCode::ERROR_PLUGIN);
                $error->setMessage($errorMsg);
                $this->addError($error);
            }
        });
        $this->addQuestion($question);
    }
}
