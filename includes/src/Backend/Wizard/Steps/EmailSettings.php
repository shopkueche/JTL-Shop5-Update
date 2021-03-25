<?php declare(strict_types=1);

namespace JTL\Backend\Wizard\Steps;

use JTL\Backend\AdminAccount;
use JTL\Backend\Wizard\Question;
use JTL\Backend\Wizard\QuestionInterface;
use JTL\Backend\Wizard\QuestionType;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Mail\Template\TemplateFactory;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;

/**
 * Class GlobalSettings
 * @package JTL\Backend\Wizard\Steps
 */
final class EmailSettings extends AbstractStep
{
    /**
     * EmailSettings constructor.
     * @param DbInterface           $db
     * @param AlertServiceInterface $alertService
     * @param AdminAccount          $adminAccount
     */
    public function __construct(DbInterface $db, AlertServiceInterface $alertService, AdminAccount $adminAccount)
    {
        parent::__construct($db, $alertService);
        $this->setTitle(__('stepTwo'));
        $this->setDescription(__('stepTwoDesc'));
        $this->setID(2);

        $question = new Question($db);
        $question->setID(11);
        $question->setText(__('email_master_absender_name'));
        $question->setDescription(__('email_master_absender_desc'));
        $question->setType(QuestionType::EMAIL);
        $question->setValue(Shop::getSettingValue(\CONF_EMAILS, 'email_master_absender'));
        $question->setOnSave(function (QuestionInterface $question) {
            $question->updateConfig('email_master_absender', $question->getValue());
        });
        $this->addQuestion($question);

        $question = new Question($db);
        $question->setID(12);
        $question->setText(__('email_master_absender_name_name'));
        $question->setDescription(__('email_master_absender_name_desc'));
        $question->setType(QuestionType::TEXT);
        $question->setValue(Shop::getSettingValue(\CONF_EMAILS, 'email_master_absender_name'));
        $question->setOnSave(function (QuestionInterface $question) {
            $question->updateConfig('email_master_absender_name', $question->getValue());
        });
        $this->addQuestion($question);

        $factory  = new TemplateFactory($db);
        $template = $factory->getTemplate(\MAILTEMPLATE_BESTELLBESTAETIGUNG);
        $template->load(1, 1);

        $question = new Question($db);
        $question->setID(13);
        $question->setText(__('orderConfirmationBCC'));
        $question->setDescription(__('orderConfirmationBCCDesc'));
        $question->setType(QuestionType::TEXT);
        $question->setValue(\implode(';', $template->getCopyTo()));
        $question->setIsFullWidth(true);
        $question->setIsRequired(false);
        $question->setOnSave(function (QuestionInterface $question) use ($template, $db) {
            //TODO use Mail classes ( saveEmailSetting() )
            $emailTemplateID = $db->select(
                'temailvorlage',
                'cModulId',
                \MAILTEMPLATE_BESTELLBESTAETIGUNG,
                null,
                null,
                null,
                null,
                false,
                'kEmailvorlage'
            )->kEmailvorlage;
            if (empty($template->getCopyTo())) {
                $db->queryPrepared(
                    "INSERT INTO temailvorlageeinstellungen VALUES (:emailTemplateID, 'cEmailCopyTo', :emailBCC)",
                    [
                        'emailTemplateID' => $emailTemplateID,
                        'emailBCC'        => $question->getValue()
                    ],
                    ReturnType::DEFAULT
                );
            } else {
                $db->queryPrepared(
                    "UPDATE temailvorlageeinstellungen
                      SET cValue = :emailBCC
                      WHERE kEmailvorlage = :emailTemplateID
                        AND cKey = 'cEmailCopyTo'",
                    [
                        'emailTemplateID' => $emailTemplateID,
                        'emailBCC'        => $question->getValue()
                    ],
                    ReturnType::DEFAULT
                );
            }
        });
        $this->addQuestion($question);

        $question = new Question($db);
        $question->setID(14);
        $question->setText(__('adminUserEmail'));
        $question->setDescription(__('adminUserEmailDesc'));
        $question->setType(QuestionType::EMAIL);
        $question->setIsFullWidth(true);
        $question->setValue($db->select(
            'tadminlogin',
            'kAdminlogin',
            $adminAccount->getID(),
            null,
            null,
            null,
            null,
            false,
            'cMail'
        )->cMail ?? '');
        $question->setOnSave(function (QuestionInterface $question) use ($adminAccount, $db) {
            $db->update(
                'tadminlogin',
                'kAdminlogin',
                $adminAccount->getID(),
                (object)['cMail' => $question->getValue()]
            );
        });
        $this->addQuestion($question);
    }
}
