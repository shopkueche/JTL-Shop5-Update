{function test_result}
    {if $test->getResult() === Systemcheck\Tests\AbstractTest::RESULT_OK}
        <h4 class="label-wrap"><span class="label label-success">
            {$state = $test->getCurrentState()}
            {if $state !== null && $state|strlen > 0}
                {$state}
            {else}
                <i class="fal fa-check text-success" aria-hidden="true"></i>
            {/if}
        </span></h4>
    {elseif $test->getResult() === Systemcheck\Tests\AbstractTest::RESULT_FAILED}
        {if $test->getIsOptional()}
            {if $test->getIsRecommended()}
                {$state = $test->getCurrentState()}
                <h4 class="label-wrap">
                    <span class="label label-warning">
                        {if $state !== null && $state|strlen > 0}
                            {$state}
                        {else}
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                        {/if}
                    </span>
                </h4>
            {else}
                {$state = $test->getCurrentState()}
                <h4 class="label-wrap">
                    <span class="label label-primary">
                        {if $state !== null && $state|strlen > 0}
                            {$state}
                        {else}
                            <i class="fal fa-times" aria-hidden="true"></i>
                        {/if}
                    </span>
                </h4>
            {/if}
        {else}
            {$state = $test->getCurrentState()}
            <h4 class="label-wrap">
                <span class="label label-danger">
                    {if $state !== null && $state|strlen > 0}
                        {$state}
                    {else}
                        <i class="fal fa-times" aria-hidden="true"></i>
                    {/if}
                </span>
            </h4>
        {/if}
    {/if}
{/function}
