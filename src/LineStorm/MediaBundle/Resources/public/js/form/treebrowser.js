define(['jquery', 'jstree'], function ($, jstree) {

    return {

        mediaTree: function ($browser, options) {

            // load defaults
            options = $.extend(true, {
                handles: {
                    tree: '.media-tree',
                    refresh: '.media-refresh-tree'
                },
                multiple: false
            }, options);

            var $tree = $browser.find(options.handles.tree);

            var exclude = ($tree.data('exclude') || "").toString().split(',');

            var processNode = function (o) {

                for (var i in o) {
                    var n = $.extend(true, {}, o[i]);
                    var d = {
                        id: 'dir-' + n.id,
                        text: n.name,
                        type: 'default',
                        children: n.children || [],
                        node: n
                    };

                    if (d.children.length) {
                        processNode(d.children);
                    }

                    if ('media' in n) {
                        for (var j in n.media) {
                            d.children = d.children || [];
                            var node = {
                                id: 'node-' + n.media[j].id,
                                text: n.media[j].title,
                                type: 'file',
                                url: n.media[j].url,
                                node: n.media[j]
                            };
                            d.children.push(node);
                        }
                    }

                    if ("undefined" == typeof o[i].children) {
                        d.children = true;
                    }

                    o[i] = d;
                }
            };

            var plugins = [
                "wholerow", "types"
            ];

            $tree.jstree({
                core: {
                    animation: 0,
                    check_callback: false,
                    themes: { stripes: true },
                    multiple: options.multiple,
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
                            } else {
                                data['id'] = node.original.node.id;
                            }

                            return data;
                        },
                        success: function (o) {
                            processNode(o);
                            return o;
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
                plugins: plugins
            });

            $tree
                .on('select_node.jstree', function (n, s, e) {
                    // this must be done dynamically!
                    $($tree.data('input')).val(s.node.original.node.id);
                })
                .on('loaded.jstree', function (n, s, e) {
                    $tree.jstree('select_node', $tree.data('init'));
                });


            $browser.find(options.handles.refresh).on('click', function () {
                $tree.jstree('refresh');
                return false;
            });

            return {
                tree: $tree
            }
        }
    }
});