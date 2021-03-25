<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Plugin\InstallCode;

/**
 * Class WidgetsExtension
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class WidgetsExtension extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        $dir  = $this->getDir();
        if (!GeneralObject::isCountable('AdminWidget', $node)) {
            return InstallCode::OK;
        }
        $node = $node['AdminWidget'][0]['Widget'] ?? null;
        $base = $dir . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_WIDGET;
        if (!GeneralObject::hasCount($node)) {
            return InstallCode::MISSING_WIDGETS;
        }
        foreach ($node as $i => $widget) {
            if (!\is_array($widget)) {
                continue;
            }
            $i      = (string)$i;
            $widget = $this->sanitizeWidget($widget);
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            \preg_match(
                '/[\w\/\-() ]+/u',
                $widget['Title'],
                $hits1
            );
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($widget['Title'])) {
                return InstallCode::INVALID_WIDGET_TITLE;
            }
            \preg_match('/[a-zA-Z0-9\/_\-.]+/', $widget['Class'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($widget['Class'])) {
                return InstallCode::INVALID_WIDGET_CLASS;
            }
            if (!\file_exists($base . $widget['Class'] . '.php')) {
                return InstallCode::MISSING_WIDGET_CLASS_FILE;
            }
            if (!\in_array($widget['Container'], ['center', 'left', 'right'], true)) {
                return InstallCode::INVALID_WIDGET_CONTAINER;
            }
            \preg_match('/[0-9]+/', $widget['Pos'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($widget['Pos'])) {
                return InstallCode::INVALID_WIDGET_POS;
            }
            \preg_match('/[0-1]{1}/', $widget['Expanded'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($widget['Expanded'])) {
                return InstallCode::INVALID_WIDGET_EXPANDED;
            }
            \preg_match('/[0-1]{1}/', $widget['Active'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($widget['Active'])) {
                return InstallCode::INVALID_WIDGET_ACTIVE;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $widget
     * @return array
     */
    private function sanitizeWidget(array $widget): array
    {
        $widget['Title']     = $widget['Title'] ?? '';
        $widget['Class']     = $widget['Class'] ?? '';
        $widget['Container'] = $widget['Container'] ?? '';
        $widget['Pos']       = $widget['Pos'] ?? '';
        $widget['Expanded']  = $widget['Expanded'] ?? '';
        $widget['Active']    = $widget['Active'] ?? '';

        return $widget;
    }
}
