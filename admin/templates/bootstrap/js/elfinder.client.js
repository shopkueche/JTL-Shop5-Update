/**
 * elFinder client options and main script for RequireJS
 **/
(function(){
    "use strict";

    var lang = (function() {
        var locq = window.location.search,
            fullLang, locm, lang;

        if (locq && (locm = locq.match(/lang=([a-zA-Z_-]+)/))) {
            // detection by url query (?lang=xx)
            fullLang = locm[1];
        } else {
            // detection by browser language
            fullLang = navigator.language;
        }

        lang = fullLang.substr(0, 2);

        if (lang === 'pt')
            lang = 'pt_BR';
        else if (lang === 'ug')
            lang = 'ug_CN';
        else if (lang === 'zh')
            lang = (fullLang.substr(0,5).toLowerCase() === 'zh-tw') ? 'zh_TW' : 'zh_CN';

        return lang;
    })();

    // config of RequireJS (REQUIRED)
    require.config({
        baseUrl: '..',
        paths: {
            'jquery':    'admin/templates/bootstrap/js/jquery-3.5.1.min',
            'jquery-ui': 'admin/templates/bootstrap/js/jquery-ui.min',
            'elfinder':  'includes/vendor/studio-42/elfinder/js/elfinder.full',
        },
    });

    require(['elfinder', 'elFinderConfig'],
        function(elFinder, config) {
            $(function() {
                config.options.lang = lang;
                $('#' + config.elementId).elfinder(config.options);
            });
        },
        function(error) {
            throw error;
        }
    );
})();
