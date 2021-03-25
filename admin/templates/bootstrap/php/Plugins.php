<?php declare(strict_types=1);

namespace AdminTemplate;

use DateTime;
use JTL\Backend\Revision;
use JTL\Catalog\Currency;
use JTL\Shop;
use JTL\Update\Updater;
use Smarty_Internal_Template;

/**
 * Class Plugins
 * @package AdminTemplate
 */
class Plugins
{
    /**
     * @param array                    $params
     * @param Smarty_Internal_Template $smarty
     * @return string
     */
    public function getRevisions(array $params, $smarty): string
    {
        $secondary = $params['secondary'] ?? false;
        $data      = $params['data'] ?? null;
        $revision  = new Revision(Shop::Container()->getDB());

        return $smarty->assign('revisions', $revision->getRevisions($params['type'], $params['key']))
            ->assign('secondary', $secondary)
            ->assign('data', $data)
            ->assign('show', $params['show'])
            ->fetch('tpl_inc/revisions.tpl');
    }

    /**
     * @param array $params
     * @return string
     */
    public function getCurrencyConversionSmarty(array $params): string
    {
        $forceTax = !(isset($params['bSteuer']) && $params['bSteuer'] === false);
        if (!isset($params['fPreisBrutto'])) {
            $params['fPreisBrutto'] = 0;
        }
        if (!isset($params['fPreisNetto'])) {
            $params['fPreisNetto'] = 0;
        }
        if (!isset($params['cClass'])) {
            $params['cClass'] = '';
        }

        return Currency::getCurrencyConversion(
            $params['fPreisNetto'],
            $params['fPreisBrutto'],
            $params['cClass'],
            $forceTax
        );
    }

    /**
     * @param array                    $params
     * @param Smarty_Internal_Template $smarty
     * @return string
     */
    public function getCurrencyConversionTooltipButton(array $params, $smarty): string
    {
        $placement = $params['placement'] ?? 'left';

        if (!isset($params['inputId'])) {
            return '';
        }
        $inputId  = $params['inputId'];
        $button   = '<button type="button" class="btn btn-tooltip btn-link px-1" id="' .
            $inputId . 'Tooltip" data-html="true"';
        $button  .= ' data-toggle="tooltip" data-placement="' . $placement . '">';
        $button  .= '<i class="fa fa-eur"></i></button>';

        return $button;
    }

    /**
     * @param array                    $params
     * @param Smarty_Internal_Template $smarty
     */
    public function getCurrentPage($params, $smarty): void
    {
        $path = $_SERVER['SCRIPT_NAME'];
        $page = \basename($path, '.php');

        if (isset($params['assign'])) {
            $smarty->assign($params['assign'], $page);
        }
    }

    /**
     * @param array                    $params
     * @param Smarty_Internal_Template $smarty
     * @return string
     */
    public function getHelpDesc(array $params, $smarty): string
    {
        $placement    = $params['placement'] ?? 'left';
        $cID          = !empty($params['cID']) ? $params['cID'] : null;
        $iconQuestion = !empty($params['iconQuestion']);
        $description  = isset($params['cDesc'])
            ? \str_replace('"', '\'', $params['cDesc'])
            : null;

        return $smarty->assign('placement', $placement)
            ->assign('cID', $cID)
            ->assign('description', $description)
            ->assign('iconQuestion', $iconQuestion)
            ->fetch('tpl_inc/help_description.tpl');
    }

    /**
     * @param mixed $cRecht
     * @return bool
     */
    public function permission($cRecht): bool
    {
        $ok = false;
        if (!isset($_SESSION['AdminAccount'])) {
            return false;
        }
        if ((int)$_SESSION['AdminAccount']->oGroup->kAdminlogingruppe === \ADMINGROUP) {
            $ok = true;
        } else {
            $orExpressions = \explode('|', $cRecht);
            foreach ($orExpressions as $flag) {
                $ok = \in_array($flag, $_SESSION['AdminAccount']->oGroup->oPermission_arr, true);
                if ($ok) {
                    break;
                }
            }
        }

        return $ok;
    }

    /**
     * @param array                    $params
     * @param Smarty_Internal_Template $smarty
     * @return string
     */
    public function SmartyConvertDate(array $params, $smarty)
    {
        if (isset($params['date']) && \mb_strlen($params['date']) > 0) {
            $dateTime = new DateTime($params['date']);
            if (isset($params['format']) && \mb_strlen($params['format']) > 1) {
                $cDate = $dateTime->format($params['format']);
            } else {
                $cDate = $dateTime->format('d.m.Y H:i:s');
            }

            if (isset($params['assign'])) {
                $smarty->assign($params['assign'], $cDate);
            } else {
                return $cDate;
            }
        }

        return '';
    }

    /**
     * Map marketplace categoryId to localized category name
     *
     * @param array                    $params
     * @param Smarty_Internal_Template $smarty
     */
    public function getExtensionCategory(array $params, $smarty): void
    {
        if (!isset($params['cat'])) {
            return;
        }

        $catNames = [
            4  => 'Templates/Themes',
            5  => 'Sprachpakete',
            6  => 'Druckvorlagen',
            7  => 'Tools',
            8  => 'Marketing',
            9  => 'Zahlungsarten',
            10 => 'Import/Export',
            11 => 'SEO',
            12 => 'Auswertungen'
        ];

        $key = $catNames[$params['cat']] ?? null;
        $smarty->assign('catName', $key);
    }

    /**
     * @param array $params
     * @return string|null
     */
    public function formatVersion(array $params): ?string
    {
        if (!isset($params['value'])) {
            return null;
        }

        return \substr_replace((int)$params['value'], '.', 1, 0);
    }

    /**
     * @param array $params
     * @return string
     */
    public function getAvatar(array $params): string
    {
        $url = isset($params['account']->attributes['useAvatar']) &&
            $params['account']->attributes['useAvatar']->cAttribValue === 'U' ?
            $params['account']->attributes['useAvatarUpload']->cAttribValue
            : 'templates/bootstrap/gfx/avatar-default.svg';

        if (!(new Updater(Shop::Container()->getDB()))->hasPendingUpdates()) {
            \executeHook(\HOOK_BACKEND_FUNCTIONS_GRAVATAR, [
                'url'          => &$url,
                'AdminAccount' => &$_SESSION['AdminAccount']
            ]);
        }

        return $url;
    }

    /**
     * @param array                    $params
     * @param Smarty_Internal_Template $smarty
     * @return string
     */
    public function captchaMarkup(array $params, $smarty): string
    {
        if (isset($params['getBody']) && $params['getBody']) {
            return Shop::Container()->getCaptchaService()->getBodyMarkup($smarty);
        }

        return Shop::Container()->getCaptchaService()->getHeadMarkup($smarty);
    }
}
