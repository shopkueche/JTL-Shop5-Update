{$htag = 'h'|cat:$instance->getProperty('level')}

<{$htag} style="{$instance->getStyleString()} text-align:{$instance->getProperty('align')}"
         class="{$instance->getAnimationClass()} {$instance->getStyleClasses()}"
         {$instance->getAnimationDataAttributeString()}>
    {$instance->getProperty('text')|escape:'html'}
</{$htag}>