<script type="module">
    {literal}
        $('.selectpicker').selectpicker({
            noneSelectedText: {/literal}'{__('selectPickerNoneSelectedText')}'{literal},
            noneResultsText: {/literal}'{__('selectPickerNoneResultsText')}'{literal},
            countSelectedText: {/literal}'{__('selectPickerCountSelectedText')}'{literal},
            maxOptionsText: () => [
                {/literal}'{__('selectPickerLimitReached')}'{literal},
                {/literal}'{__('selectPickerGroupLimitReached')}'{literal},
            ],
            selectAllText: {/literal}'{__('selectPickerSelectAllText')}'{literal},
            deselectAllText: {/literal}'{__('selectPickerDeselectAllText')}'{literal},
            doneButtonText: {/literal}'{__('close')}'{literal},
            style: ''
        });
    {/literal}
</script>
