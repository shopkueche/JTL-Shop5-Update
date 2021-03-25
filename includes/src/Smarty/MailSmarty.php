<?php declare(strict_types=1);

namespace JTL\Smarty;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;

/**
 * Class MailSmarty
 * @package JTL\Smarty
 */
class MailSmarty extends JTLSmarty
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * MailSmarty constructor.
     * @param DbInterface $db
     * @param string      $context
     * @throws \SmartyException
     */
    public function __construct(DbInterface $db, string $context = ContextType::MAIL)
    {
        $this->db = $db;
        parent::__construct(true, $context);
        $this->registerResource('db', new SmartyResourceNiceDB($db, $context))
             ->registerPlugin(\Smarty::PLUGIN_FUNCTION, 'includeMailTemplate', [$this, 'includeMailTemplate'])
             ->setCompileDir(\PFAD_ROOT . \PFAD_COMPILEDIR)
             ->setTemplateDir(\PFAD_ROOT . \PFAD_EMAILTEMPLATES)
             ->setDebugging(0)
             ->setCaching(0);
        if ($context === ContextType::MAIL && \MAILTEMPLATE_USE_SECURITY) {
            $this->activateBackendSecurityMode();
        } elseif ($context === ContextType::NEWSLETTER && \NEWSLETTER_USE_SECURITY) {
            $this->activateBackendSecurityMode();
        }
    }

    /**
     * @param array     $params
     * @param JTLSmarty $smarty
     * @return string
     */
    public function includeMailTemplate($params, $smarty): string
    {
        if (!isset($params['template'], $params['type']) || $smarty->getTemplateVars('int_lang') === null) {
            return '';
        }
        $res  = null;
        $lang = null;
        $tpl  = $this->db->select(
            'temailvorlage',
            'cDateiname',
            $params['template']
        );
        if (isset($tpl->kEmailvorlage) && $tpl->kEmailvorlage > 0) {
            $lang = $smarty->getTemplateVars('int_lang');
            $row  = $params['type'] === 'html' ? 'cContentHtml' : 'cContentText';
            $res  = $this->db->query(
                'SELECT ' . $row . ' AS content
                    FROM temailvorlagesprache
                    WHERE kSprache = ' . (int)$lang->kSprache .
                ' AND kEmailvorlage = ' . (int)$tpl->kEmailvorlage,
                ReturnType::SINGLE_OBJECT
            );
            if (isset($res->content)) {
                return $smarty->fetch('db:' . $params['type'] . '_' . $tpl->kEmailvorlage . '_' . $lang->kSprache);
            }
        }

        return '';
    }
}
