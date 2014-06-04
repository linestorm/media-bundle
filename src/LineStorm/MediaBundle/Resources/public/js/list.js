
define(['jquery', 'bootstrap', 'cms_api'], function ($, bs, api) {
    $(document).ready(function(){
        $('.media-edit').on('click', function(){
            var selected = $('#media-tree').jstree('get_selected', true);
            for(var i=0 ; i<selected.length ; ++i){
                var item = selected[i];
                if(item.type == "default"){
                    window.location = item.original.url;
                }
            }
        });
    });
});
