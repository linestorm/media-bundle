define(['jquery', 'bootstrap', 'jstree'], function ($, bs, jstree) {
    var $tree = $('#media-tree');
    var $input = $('#' + $tree.data('input'));

    var processNode = function (o) {
        for (var i in o) {
            var n = o[i];
            o[i].text = n.name;
            if ('children' in o[i]) {
                processNode(o[i].children);
            } else {
                o[i].children = true;
            }
        }
    };

    $tree.jstree({
        "core": {
            "animation": 0,
            "check_callback": false,
            "themes": { "stripes": true },
            "multiple": false,
            "data": {
                'url': $tree.data('root'),
                'data': function (node) {
                    if (node.id == '#') {
                        var initId = $tree.data('init');
                        if (initId) {
                            return {to: initId};
                        }
                    }

                    return { 'id': node.id };
                },
                'success': function (o) {
                    processNode(o);
                }
            }
        },
        "types": {
            "#": {
                "valid_children": ["root"]
            },
            "root": {
                "icon": "/static/3.0.1/assets/images/tree_icon.png",
                "valid_children": ["default"]
            },
            "default": {
                "icon": "fa-folder-open",
                "valid_children": ["default"]
            }
        },
        "plugins": [
            "wholerow"
        ]
    })
        .on('select_node.jstree', function (n, s, e) {
            $input.val(s.node.id);
        })
        .on('loaded.jstree', function (n, s, e) {
            $tree.jstree('select_node', $tree.data('init'));
        });

});
