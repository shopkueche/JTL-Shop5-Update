{$result = $test->getResult()}
{if $result === 0}
<span class="hidden-xs">
	<button type="button" class="btn btn-test-result btn-success btn-xs">
		<i class="glyphicon glyphicon-ok"></i>
	</button>
	{$test->getCurrentState()}
	</span>
	<span class="visible-xs">
		<button type="button" class="btn btn-test-result btn-success btn-xs">
			<i class="glyphicon glyphicon-ok"></i>
		</button>
	</span>
{elseif $result === 1}
	{if $test->getIsOptional()}
		<span class="hidden-xs">
			{if $test->getIsRecommended()}
				<button type="button" class="btn btn-test-result btn-warning btn-xs">
					<i class="glyphicon glyphicon-exclamation-sign"></i>
				</button>
			{else}
				<button type="button" class="btn btn-test-result btn-primary btn-xs">
					<i class="glyphicon glyphicon-remove"></i>
				</button>
			{/if}
			{$test->getCurrentState()}
		</span>
		<span class="visible-xs">
			{if $test->getIsRecommended()}
				<button type="button" class="btn btn-test-result btn-warning btn-xs">
					<i class="glyphicon glyphicon-exclamation-sign"></i>
				</button>
			{else}
				<button type="button" class="btn btn-test-result btn-primary btn-xs">
					<i class="glyphicon glyphicon-remove"></i>
				</button>
			{/if}
		</span>
	{else}
		<span class="hidden-xs">
			<button type="button" class="btn btn-test-result btn-danger btn-xs">
				<i class="glyphicon glyphicon-remove"></i>
			</button>
			{$test->getCurrentState()}
		</span>
		<span class="visible-xs">
			<button type="button" class="btn btn-test-result btn-danger btn-xs">
				<i class="glyphicon glyphicon-remove"></i>
			</button>
		</span>
	{/if}
{elseif $result === 2}
{/if}
