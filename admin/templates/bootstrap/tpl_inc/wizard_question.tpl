<div class="{if $question->isFullWidth()}col-12{else}col-xl-6{/if}">
    {if $question->getType() === JTL\Backend\Wizard\QuestionType::TEXT || $question->getType() === JTL\Backend\Wizard\QuestionType::EMAIL}
        <div class="form-group-lg mb-4">
            <span class="form-title">
                {$question->getText()}:
                {if $question->getDescription() !== null}
                    <span class="fal fa-info-circle text-muted ml-3" data-toggle="tooltip" title="{$question->getDescription()}"></span>
                {/if}
            </span>
            <div>
                <input type="{if $question->getType() === JTL\Backend\Wizard\QuestionType::EMAIL}email{else}text{/if}"
                       class="form-control rounded-pill"
                       id="question-{$question->getID()}"
                       placeholder=""
                       data-setup-summary-id="question-{$question->getID()}"
                       name="question-{$question->getID()}"
                       value="{if $question->getValue() !== null}{$question->getValue()}{/if}"
                       {if $question->isRequired()}required{/if}
                >
            </div>
        </div>
    {elseif $question->getType() === JTL\Backend\Wizard\QuestionType::NUMBER}
        <div class="form-group-lg mb-4">
            <span class="form-title">
                {$question->getText()}:
                {if $question->getDescription() !== null}
                    <span class="fal fa-info-circle text-muted ml-3" data-toggle="tooltip" title="{$question->getDescription()}"></span>
                {/if}
            </span>
            <div class="input-group form-counter min-w-sm">
                <div class="input-group-prepend">
                    <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                        <span class="fas fa-minus"></span>
                    </button>
                </div>
                <input type="number"
                       class="form-control rounded-pill"
                       id="question-{$question->getID()}"
                       placeholder=""
                       data-setup-summary-id="question-{$question->getID()}"
                       name="question-{$question->getID()}"
                       value="{if $question->getValue() !== null}{$question->getValue()}{/if}"
                       {if $question->isRequired()}required{/if}
                >
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                        <span class="fas fa-plus"></span>
                    </button>
                </div>
            </div>
        </div>
    {elseif $question->getType() === JTL\Backend\Wizard\QuestionType::BOOL}
        {if $question->getText() !== null}
            <span class="form-title">
                {$question->getText()}:
                {if $question->getDescription() !== null}
                    <span class="fal fa-info-circle text-muted ml-3" data-toggle="tooltip" title="{$question->getDescription()}"></span>
                {/if}
            </span>
        {/if}
        <div class="custom-control custom-checkbox">
            <input type="checkbox"
                   class="custom-control-input"
                   id="question-{$question->getID()}"
                   name="question-{$question->getID()}"
                   data-setup-summary-id="question-{$question->getID()}"
                   data-setup-summary-text="{if $question->getText() !== null}{$question->getText()}{else}{$question->getSummaryText()}{/if}"
                    {if !empty($question->getValue())} checked{/if}
                    {if $question->isRequired()}required{/if}
            >
            <label class="custom-control-label" for="question-{$question->getID()}">
                {$question->getLabel()}
                {if $question->getText() === null && $question->getDescription() !== null}
                    <span class="fal fa-info-circle text-muted ml-3" data-toggle="tooltip" title="{$question->getDescription()}"></span>
                {/if}
            </label>
        </div>
    {elseif $question->getType() === JTL\Backend\Wizard\QuestionType::MULTI_BOOL}
        <div>
            <div id="question-{$question->getID()}"></div>
        </div>
        <div class="form-group-lg mb-4">
            <span class="form-title">
                {$question->getText()}:
                {if $question->getDescription() !== null}
                    <span class="fal fa-info-circle text-muted ml-3" data-toggle="tooltip" title="{$question->getDescription()}"></span>
                {/if}
            </span>
            {foreach $question->getOptions() as $option}
                <div class="custom-control custom-checkbox">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="question-{$question->getID()}-{$option@index}"
                           name="question-{$question->getID()}[]"
                           data-setup-summary-id="question-{$question->getID()}"
                           data-setup-summary-text="{$option->getName()}"
                           value="{$option->getValue()}"
                            {if $option->isSelected($question->getValue())} checked{/if}
                            {if $question->isRequired()}required{/if}
                    >
                    <label class="custom-control-label" for="question-{$question->getID()}-{$option@index}">
                        {$option->getName()}
                    </label>
                </div>
            {/foreach}
        </div>
    {elseif $question->getType() === JTL\Backend\Wizard\QuestionType::PLUGIN}
        <div>
            <div id="question-{$question->getID()}"></div>
        </div>
        <div class="form-group-list">
            {foreach $question->getOptions() as $option}
                <div class="form-group-list-item py-2">
                    <div class="form-row">
                        <div class="col-xl-3">
                            <div class="custom-control custom-checkbox custom-checkbox-centered">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="question-{$question->getID()}-{$option@index}"
                                       name="question-{$question->getID()}[]"
                                       data-setup-summary-id="question-{$question->getID()}"
                                       data-setup-summary-text="{$option->getName()}"
                                       value="{$option->getValue()}"
                                        {if $option->isSelected($question->getValue())} checked{/if}
                                        {if $question->isRequired()}required{/if}
                                >
                                <label class="custom-control-label" for="question-{$question->getID()}-{$option@index}">
                                    <img src="{$option->getLogoPath()}" width="80" height="80" loading="lazy" alt="{$option->getName()}">
                                </label>
                            </div>
                        </div>
                        <div class="col-xl">
                            {$option->getDescription()}
                            <a href="premiumplugin.php?scope={$question->getScope()}&id={$option->getValue()}&fromWizard=true" target="_blank">
                                {__('getToKnowMore')}
                                <span class="fal fa-long-arrow-right ml-1"></span>
                            </a>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    {/if}
</div>
