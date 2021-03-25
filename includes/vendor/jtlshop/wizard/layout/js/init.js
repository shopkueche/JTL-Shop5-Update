(function($) {
    $(function() {
        $('.expandable').expander({
            'expandText' : 'weiterlesen',
            'userCollapse' : false,
            'expandEffect': 'show',
            'expandSpeed': 0,
            'collapseEffect': 'hide',
            'collapseSpeed': 0,
            'collapseEffect' : 'hide',
            'afterExpand' : function() {
                $('.details', this).css('display', 'inline');
            }
        });
    });
})(jQuery);