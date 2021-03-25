<script type="text/javascript" src="{$templateBaseURL}js/jquery.jclock.js"></script>
<script type="text/javascript">
    $(function ($) {ldelim}
        $('#clock_time').jclock({ldelim}
            format: '%H:%M:%S',
        {rdelim});
    {rdelim});
    {literal}
        $(document).ready(function(){
            var dateLong = new Date();
            var dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            $('#clock_date').html(dateLong.toLocaleDateString('{/literal}{$language}{literal}', dateOptions));
        });
    {/literal}
</script>
<div class="widget-custom-data nospacing">
    <div class="clock">
        <p id="clock_time"></p>
        <p id="clock_date"></p>
    </div>
</div>
