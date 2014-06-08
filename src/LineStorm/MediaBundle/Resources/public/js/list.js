
define(['jquery', 'bootstrap', 'cms_api', 'cms_media_treebrowser'], function ($, bs, api, mTree) {
    $(document).ready(function(){

        var $tree = $('.media-browser');
        mTree.mediaTree($tree);

        $('.media-edit').on('click', function(){
            var selected = $('.media-tree').jstree('get_selected', true);
            for(var i=0 ; i<selected.length ; ++i){
                var item = selected[i];
                switch(item.type){
                    case "default":
                    case "file":
                        window.location = item.original.node.url;
                        break;
                }
            }
        });
    });
});
