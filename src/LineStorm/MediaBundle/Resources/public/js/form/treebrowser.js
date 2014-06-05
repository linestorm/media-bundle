define(['jquery', 'bootstrap', 'jstree'], function ($, bs, jstree) {
    var $tree = $('#media-tree');
    var exclude = ($tree.data('exclude') || "").toString().split(',');

    var processNode = function (o) {
        for (var i in o) {
            var n = o[i];
            o[i].text = n.name;
            o[i].type = "default";
            if ('children' in o[i]) {
                processNode(o[i].children);
            }

            if ('media' in o[i]) {
                for (var j in o[i].media) {
                    o[i].children = o[i].children || [];
                    o[i].children.push({
                        id: 'm-' + o[i].media[j].id,
                        text: o[i].media[j].title,
                        type: "file"
                    })
                }
            }

            if ("undefined" == typeof o[i].children) {
                o[i].children = true;
            }
        }
    };

    $tree.jstree({
        core: {
            animation: 0,
            check_callback: false,
            themes: { stripes: true },
            multiple: false,
            data: {
                url: $tree.data('root'),
                data: function (node) {
                    var data = {};
                    data['exclude'] = exclude;

                    if (node.id == '#') {
                        var initId = $tree.data('init');
                        if (initId) {
                            data['to'] = initId;
                            return data;
                        }
                    }

                    data['id'] = node.id;
                    return data;
                },
                success: function (o) {
                    processNode(o);
                }
            }
        },
        types: {
            "#": {
                valid_children: ["root"]
            },
            root: {
                icon: "/static/3.0.1/assets/images/tree_icon.png",
                valid_children: ["default", "file"]
            },
            default: {
                icon: "fa fa-folder-open",
                valid_children: ["default", "file"]
            },
            file: {
                icon: "fa fa-picture-o",
                "valid_children": []
            }
        },
        plugins: [
            "wholerow", "types"
        ]
    })
        .on('select_node.jstree', function (n, s, e) {
            // this must be done dynamically!
            $($tree.data('input')).val(s.node.id);
        })
        .on('loaded.jstree', function (n, s, e) {
            $tree.jstree('select_node', $tree.data('init'));
        });

});
