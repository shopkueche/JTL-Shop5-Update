<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\Plugin\InstallCode;
use stdClass;

/**
 * Class Widgets
 * @package JTL\Plugin\Admin\Installation\Items
 */
class Widgets extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['AdminWidget'][0]['Widget'])
        && \is_array($this->baseNode['Install'][0]['AdminWidget'][0]['Widget'])
            ? $this->baseNode['Install'][0]['AdminWidget'][0]['Widget']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $oldWidgets = $this->oldPlugin !== null
            ? $this->oldPlugin->getWidgets()->getWidgets()->keyBy('cClass')->toArray()
            : [];
        foreach ($this->getNode() as $i => $widgetData) {
            $i = (string)$i;
            \preg_match('/[0-9]+/', $i, $hits);
            if (\mb_strlen($hits[0]) !== \mb_strlen($i)) {
                continue;
            }
            $widget               = new stdClass();
            $widget->kPlugin      = $this->plugin->kPlugin;
            $widget->cTitle       = $widgetData['Title'];
            $widget->cClass       = $this->plugin->bExtension === 1 // @todo
                ? $widgetData['Class']
                : $widgetData['Class'] . '_' . $this->plugin->cPluginID;
            $widget->cDescription = $widgetData['Description'];
            if (\is_array($widget->cDescription)) {
                //@todo: when description is empty, this becomes an array with indices [0] => '' and [0 attr] => ''
                $widget->cDescription = $widget->cDescription[0];
            }
            if (isset($oldWidgets[$widget->cClass])) {
                $oldWidget          = $oldWidgets[$widget->cClass];
                $widget->eContainer = $oldWidget->eContainer;
                $widget->nPos       = $oldWidget->nPos;
                $widget->bExpanded  = $oldWidget->bExpanded;
                $widget->bActive    = $oldWidget->bActive;
            } else {
                $widget->eContainer = $widgetData['Container'];
                $widget->nPos       = $widgetData['Pos'];
                $widget->bExpanded  = $widgetData['Expanded'];
                $widget->bActive    = $widgetData['Active'];
            }
            if (!$this->db->insert('tadminwidgets', $widget)) {
                return InstallCode::SQL_CANNOT_SAVE_WIDGET;
            }
        }

        return InstallCode::OK;
    }
}
