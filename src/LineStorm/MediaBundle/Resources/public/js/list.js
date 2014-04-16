
define(['jquery', 'bootstrap', 'cms_api'], function ($, bs, api) {
    var page = 1,
        $container;

    function requestPage(){
        $.ajax({
            url: $container.data('url'),
            data: {
                page: page,
                limit: 50
            },
            type: 'GET',
            dataType: 'json',
            success: function(o){
                $container.empty();
                var html = '';
                for(var i in o){
                    var media = o[i];
                    var prototype = $container.data('prototype');
                    for(var p in media){
                        var prop = media[p];
                        var rgx = new RegExp("__"+p+"__", "gim");
                        prototype = prototype.replace(rgx, prop);
                    }
                    html += prototype;
                }

                $container.append(html);
            }
        });
    }

    $(document).ready(function(){
        $container = $('.media-container');

        $('a.media-prev').on('click', function(){
            if(page <= 0)
                return;
            --page;
            requestPage();
        });
        $('a.media-next').on('click', function(){
            ++page;
            requestPage();
        });
        requestPage();
    });
});
