<?php declare(strict_types=1);

namespace JTL\Mail\Admin;

use InvalidArgumentException;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Mail\Template\Model;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Template\TemplateInterface;
use PHPMailer\PHPMailer\Exception;
use stdClass;

/**
 * Class Controller
 * @package JTL\Mail\Admin
 */
final class Controller
{
    public const OK = 0;

    public const ERROR_NO_TEMPLATE = 1;

    public const ERROR_UPLOAD_FILE_NAME = 3;

    public const ERROR_UPLOAD_FILE_NAME_MISSING = 4;

    public const ERROR_UPLOAD_FILE_SAVE = 5;

    public const ERROR_UPLOAD_FILE_SIZE = 6;

    public const ERROR_DELETE = 7;

    public const ERROR_CANNOT_SEND = 8;

    private const UPLOAD_DIR = \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_EMAILPDFS;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var TemplateFactory
     */
    private $factory;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var array
     */
    private $errorMessages = [];

    /**
     * @var Model|null
     */
    private $model;

    /**
     * Controller constructor.
     * @param DbInterface     $db
     * @param Mailer          $mailer
     * @param TemplateFactory $factory
     */
    public function __construct(DbInterface $db, Mailer $mailer, TemplateFactory $factory)
    {
        $this->db      = $db;
        $this->mailer  = $mailer;
        $this->factory = $factory;
    }

    /**
     * @return Model|null
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * @param Model $model
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }

    /**
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * @param array $errorMessages
     */
    public function setErrorMessages(array $errorMessages): void
    {
        $this->errorMessages = $errorMessages;
    }

    /**
     * @param string $errorMsg
     */
    public function addErrorMessage(string $errorMsg): void
    {
        $this->errorMessages[] = $errorMsg;
    }

    /**
     * @param int $templateID
     * @param int $languageID
     * @return int
     */
    public function deleteAttachments(int $templateID, int $languageID): int
    {
        $model = $this->getTemplateByID($templateID);
        if ($model === null) {
            throw new InvalidArgumentException('Cannot find model with ID ' . $templateID);
        }
        $res = self::OK;
        foreach ($model->getAttachments($languageID) as $attachment) {
            if (!(\file_exists(self::UPLOAD_DIR . $attachment) && \unlink(self::UPLOAD_DIR . $attachment))) {
                $res = self::ERROR_DELETE;
            }
        }
        $model->removeAttachments($languageID);
        $model->setAttachmentNames(null, $languageID);
        $model->save();

        return $res;
    }

    /**
     * @param Model           $model
     * @param LanguageModel[] $availableLanguages
     * @param array           $post
     * @param array           $files
     * @return int
     */
    private function updateUploads(Model $model, array $availableLanguages, array $post, array $files): int
    {
        $filenames = [];
        $pdfFiles  = [];
        foreach ($availableLanguages as $lang) {
            $langID             = $lang->getId();
            $filenames[$langID] = [];
            $pdfFiles[$langID]  = [];
            $i                  = 0;
            foreach ($model->getAttachments($langID) as $tmpPFDs) {
                $pdfFiles[$langID][] = $tmpPFDs;
                $postIndex           = $post['cPDFNames_' . $langID][$i];
                if (\mb_strlen($postIndex) > 0) {
                    $regs = [];
                    \preg_match(
                        '/[A-Za-z0-9_-]+/',
                        $postIndex,
                        $regs
                    );
                    if (\mb_strlen($regs[0]) === \mb_strlen($postIndex)) {
                        $filenames[$langID][] = $postIndex;
                        unset($postIndex);
                    } else {
                        $this->addErrorMessage(\sprintf(__('errorFileName'), $postIndex));
                        return self::ERROR_UPLOAD_FILE_NAME;
                    }
                } else {
                    $filenames[$langID][] = $model->getAttachmentNames($langID)[$i];
                }
                ++$i;
            }
            for ($i = 0; $i < 3; $i++) {
                if (isset($files['cPDFS_' . $langID]['name'][$i])
                    && \mb_strlen($files['cPDFS_' . $langID]['name'][$i]) > 0
                    && \mb_strlen($post['cPDFNames_' . $langID][$i]) > 0
                ) {
                    if ($files['cPDFS_' . $langID]['size'][$i] <= 2097152) {
                        if (!\mb_strrpos($files['cPDFS_' . $langID]['name'][$i], ';')
                            && !\mb_strrpos($post['cPDFNames_' . $langID][$i], ';')
                        ) {
                            $cPlugin = $model->getPluginID() > 0 ? '_' . $model->getPluginID() : '';
                            $target  = self::UPLOAD_DIR . $model->getID() .
                                '_' . $langID . '_' . ($i + 1) . $cPlugin . '.pdf';
                            if (!\move_uploaded_file($files['cPDFS_' . $langID]['tmp_name'][$i], $target)) {
                                $this->addErrorMessage(__('errorFileSave'));

                                return self::ERROR_UPLOAD_FILE_SAVE;
                            }
                            $filenames[$langID][] = $post['cPDFNames_' . $langID][$i];
                            $pdfFiles[$langID][]  = $model->getID()
                                . '_' . $langID
                                . '_' . ($i + 1) . $cPlugin . '.pdf';
                        } else {
                            $this->addErrorMessage(__('errorFileNameMissing'));

                            return self::ERROR_UPLOAD_FILE_NAME_MISSING;
                        }
                    } else {
                        $this->addErrorMessage(__('errorFileSizeType'));
                        return self::ERROR_UPLOAD_FILE_SIZE;
                    }
                } elseif (isset($files['cPDFS_' . $langID]['name'][$i], $post['cPDFNames_' . $langID][$i])
                    && \mb_strlen($files['cPDFS_' . $langID]['name'][$i]) > 0
                    && \mb_strlen($post['cPDFNames_' . $langID][$i]) === 0
                ) {
                    $attachmentErrors[$langID][$i] = 1;
                    $this->addErrorMessage(__('errorFileNameMissing'));
                    return self::ERROR_UPLOAD_FILE_SIZE;
                }
            }
        }
        $model->setAllAttachmentNames($filenames);
        $model->setAllAttachments($pdfFiles);

        return self::OK;
    }

    /**
     * @param int   $templateID
     * @param array $post
     * @param array $files
     * @return int
     */
    public function updateTemplate(int $templateID, array $post, array $files): int
    {
        $this->model = $this->getTemplateByID($templateID);
        if ($this->model === null) {
            throw new InvalidArgumentException('Cannot find model with ID ' . $templateID);
        }
        $languages = LanguageHelper::getAllLanguages();
        foreach ($languages as $lang) {
            $langID = $lang->getId();
            foreach ($this->model->getMapping() as $field => $method) {
                $method         = 'set' . $method;
                $localizedIndex = $field . '_' . $langID;
                if (isset($post[$field])) {
                    $this->model->$method($post[$field]);
                } elseif (isset($post[$localizedIndex])) {
                    $this->model->$method($post[$localizedIndex], $langID);
                }
            }
        }
        $res = $this->updateUploads($this->model, $languages, $post, $files);
        if ($res !== self::OK) {
            return $res;
        }
        $this->model->setHasError(false);
        $this->model->setSyntaxCheck(Model::SYNTAX_NOT_CHECKED);
        $this->model->save();

        return self::OK;
    }

    /**
     * @param int $templateID
     * @return int
     * @throws Exception
     * @throws \SmartyException
     */
    public function sendPreviewMails(int $templateID): int
    {
        $mailTpl = $this->getTemplateByID($templateID);
        if ($mailTpl === null) {
            $this->addErrorMessage(__('errorTemplateMissing') . $templateID);

            return self::ERROR_NO_TEMPLATE;
        }
        $moduleID = $mailTpl->getModuleID();
        if ($mailTpl->getPluginID() > 0) {
            $moduleID = 'kPlugin_' . $mailTpl->getPluginID() . '_' . $moduleID;
        }
        $template = $this->factory->getTemplate($moduleID);
        if ($template === null) {
            $this->addErrorMessage(__('errorTemplateMissing') . $moduleID);

            return self::ERROR_NO_TEMPLATE;
        }
        $res  = true;
        $mail = new Mail();
        foreach (LanguageHelper::getAllLanguages() as $lang) {
            try {
                $mail = $mail->createFromTemplate($template, null, $lang);
            } catch (InvalidArgumentException $e) {
                $this->addErrorMessage(__('errorTemplateMissing') . $lang->getLocalizedName());
                $res = self::ERROR_NO_TEMPLATE;
                continue;
            }
            $mail->setToMail($this->mailer->getConfig()['emails']['email_master_absender']);
            $mail->setToName($this->mailer->getConfig()['emails']['email_master_absender_name']);
            $res = ($sent = $this->mailer->send($mail)) && $res;
            if ($sent !== true) {
                $this->addErrorMessage($mail->getError());
            }
        }

        return $res === true ? self::OK : self::ERROR_CANNOT_SEND;
    }

    /**
     * @param int $templateID
     * @return bool
     */
    public function resetTemplate(int $templateID): bool
    {
        $this->db->queryPrepared(
            'DELETE FROM temailvorlagesprache
                WHERE kEmailvorlage = :tid',
            ['tid' => $templateID],
            ReturnType::DEFAULT
        );
        $this->db->queryPrepared(
            'INSERT INTO temailvorlagesprache
                SELECT *
                FROM temailvorlagespracheoriginal
                WHERE temailvorlagespracheoriginal.kEmailvorlage = :tid',
            ['tid' => $templateID],
            ReturnType::DEFAULT
        );
        $data = $this->db->select(
            'temailvorlage',
            'kEmailvorlage',
            $templateID
        );
        if (isset($data->cDateiname) && \mb_strlen($data->cDateiname) > 0) {
            $this->resetFromFile($templateID, $data);
        }

        return true;
    }

    /**
     * @param int      $templateID
     * @param stdClass $data
     * @return int
     */
    private function resetFromFile(int $templateID, stdClass $data): int
    {
        $affected = 0;
        foreach (LanguageHelper::getAllLanguages() as $lang) {
            $base      = \PFAD_ROOT . \PFAD_EMAILVORLAGEN . $lang->getIso() . '/' . $data->cDateiname;
            $fileHtml  = $base . '_html.tpl';
            $filePlain = $base . '_plain.tpl';
            if (!\file_exists($fileHtml) || !\file_exists($filePlain)) {
                continue;
            }
            $upd                = new stdClass();
            $upd->cContentHtml  = \file_get_contents($fileHtml);
            $upd->cContentText  = \file_get_contents($filePlain);
            $upd->kEmailvorlage = $templateID;
            $upd->kSprache      = $lang->getId();
            $convertHTML        = \mb_detect_encoding($upd->cContentHtml, ['UTF-8'], true) !== 'UTF-8';
            $convertText        = \mb_detect_encoding($upd->cContentText, ['UTF-8'], true) !== 'UTF-8';
            $upd->cContentHtml  = $convertHTML === true ? Text::convertUTF8($upd->cContentHtml) : $upd->cContentHtml;
            $upd->cContentText  = $convertText === true ? Text::convertUTF8($upd->cContentText) : $upd->cContentText;
            $updCount           = $this->db->upsert(
                'temailvorlagesprache',
                $upd
            );
            $affected          += $updCount > 0 ? $updCount : 0;
        }

        return $affected;
    }


    /**
     * @param int      $templateID
     * @return Model|null
     */
    public function getTemplateByID(int $templateID): ?Model
    {
        $mailTpl = $this->factory->getTemplateByID($templateID);
        if ($mailTpl !== null) {
            $mailTpl->load(1, 1);

            return $mailTpl->getModel();
        }

        return null;
    }

    /**
     * @return TemplateInterface[]
     */
    public function getAllTemplates(): array
    {
        $templates   = [];
        $templateIDs = $this->db->selectAll('temailvorlage', [], [], 'cModulId, kPlugin');
        $langID      = LanguageHelper::getDefaultLanguage()->kSprache;
        $cgroupID    = CustomerGroup::getDefaultGroupID();
        foreach ($templateIDs as $templateID) {
            $module = $templateID->cModulId;
            if ($templateID->kPlugin > 0) {
                $module = 'kPlugin_' . $templateID->kPlugin . '_' . $templateID->cModulId;
            }
            if (($template = $this->factory->getTemplate($module)) !== null) {
                $template->load($langID, $cgroupID);
                $templates[] = $template->getModel();
            }
        }

        return \array_filter($templates);
    }
}
