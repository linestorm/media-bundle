(function(){

    //Main module definition.
    define(['jquery', 'jqueryui', 'dropzone'], function($, ui, Dropzone){

        // setup dropzone
        Dropzone.autoDiscover = false;

        return {
            load: function (name, req, onload, config) {
                //req has the same API as require().
                req([name], function (value) {
                    onload(value);
                });
            },

            dropzone: function ($dropzone, options) {

                // load defaults
                options = $.extend(true, {
                    maxFiles: null
                }, options);

                var formCount = parseInt($dropzone.data('count'));
                var dz = new Dropzone($dropzone[0], {
                    url: $dropzone.data('url'),
                    maxFiles: options.maxFiles,
                    acceptedFiles: 'image/*',
                    thumbnailWidth: null,
                    thumbnailHeight: null,
                    init: function(){
                        this.on("success", function(file, response) {
                            var $form = $(file.previewElement);

                            var $container = $form.find('.media-form-container');
                            $container.html($container.html().replace(/__name__/g, formCount));
                            ++formCount;

                            $container.find('input[name*="[alt]"]').val(response.alt);
                            $container.find('input[name*="[hash]"]').val(response.hash);
                            $container.find('input[name*="[credits]"]').val(response.credits);
                            $container.find('textarea[name*="[title]"]').val(response.title);

                            $container.find('input[name*="[src]"]').val(response.src);
                            $container.find('input[name*="[path]"]').val(response.path);
                            $container.find('input[name*="[name]"]').val(response.name);
                            $container.find('input[name*="[nameOriginal]"]').val(response.name_original);
                        });
                        this.on("error", function(file, response) {
                            this.removeFile(file);
                            alert("Cannot add file: "+response);
                        });
                    },
                    previewTemplate: $dropzone.data('prototype')
                });

                // dropzone doesn't bind the delete buttons if they were present before init (e.g. on edit pages)
                $dropzone.find('.media-remove').on('click', function(){
                    $(this).closest('.media-tile').remove();
                });

                // set up the sortable content
                $dropzone.sortable({
                    items: '> .media-tile',
                    create: function( event, ui ) {
                    },
                    start: function(e, ui){

                    },
                    stop:function(e,ui){

                    }
                });

                return dz;
            }
        }
    });

})();
