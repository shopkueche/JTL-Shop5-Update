<?php declare(strict_types=1);

namespace JTL\Debug;

use DebugBar\JavascriptRenderer;

/**
 * Class DummyRenderer
 * @package JTL\Debug
 */
class DummyRenderer extends JavascriptRenderer
{
    /**
     * @inheritdoc
     */
    public function renderHead()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function render($initialize = true, $renderStackedData = true)
    {
        return '';
    }
}
