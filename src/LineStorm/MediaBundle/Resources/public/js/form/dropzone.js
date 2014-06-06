(function(){

    //Main module definition.
    define(['jquery', 'jqueryui', 'dropzone'], function($, ui, Dropzone){

        return {
            load: function (name, req, onload, config) {
                //req has the same API as require().
                req([name], function (value) {
                    onload(value);
                });
            },

            dropzone: function ($dropzones, options) {

                // load defaults
                options = $.extend(true, {
                    maxFiles: null
                }, options);

                $dropzones.each(function(){

                    var $dropzone = $(this);
                    var formCount = 0;
                    var dz = new Dropzone(this, {
                        url: $dropzone.data('url'),
                        maxFiles: options.maxFiles,
                        acceptedFiles: 'image/*',
                        thumbnailWidth: null,
                        thumbnailHeight: null,
                        init: function(){
                            this.on("success", function(file, response) {
                                var $form = $(file.previewElement);

                                var $container = $form.find('.upload-form-container');
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
                    $dropzone.find('.upload-remove').on('click', function(){
                        $(this).closest('.upload-tile').remove();
                    });

                    // set up the sortable content
                    $dropzone.sortable({
                        items: '> .upload-tile',
                        create: function( event, ui ) {
                        },
                        start: function(e, ui){

                        },
                        stop:function(e,ui){

                        }
                    });
                    $dropzone.disableSelection();
                });

                return $dropzones;
            }
        }
    });

})();
