
define(['jquery', 'jqueryui', 'bootstrap', 'dropzone', 'typeahead', 'cms_api'], function ($, jui, bs, Dropzone, typeahead, api) {

    // setup dropzone
    Dropzone.autoDiscover = false;

    var $form;
    var $dropzone;
    var carrosselDropZone;

    $(document).ready(function(){
        $form = $('form[name="linestorm_cms_form_media"],form[name="linestorm_cms_form_media_category"]');
        $dropzone = $('.dropzone');

        var formCount = parseInt($form.data('count')) || 0;

        if($dropzone.length){
            carrosselDropZone = new Dropzone($dropzone[0], {
                url: $dropzone.data('url'),
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

            $('form[name="linestorm_cms_form_media_multiple"]').on('submit', function(e){
                e.preventDefault();
                e.stopPropagation();

                var $form = $(this),
                    $mediaItems = $form.find('.upload-tile');
                window.lineStorm.api.saveForm($form, function(on, status, xhr){
                    $mediaItems.remove();
                }, function(e, status, ex){
                    if(e.status === 400){
                        if(e.responseJSON){
                        } else {
                            alert(status);
                        }
                    }
                });

                return false;
            });

            // dropzone doesn't bind the delete buttons if they were present before init (e.g. on edit pages)
            $dropzone.find('.upload-remove').on('click', function(){
                $(this).closest('.upload-tile').remove();
            })

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
        }

        $('form.api-save').on('submit', function(e){
            e.preventDefault();
            e.stopPropagation();

            window.lineStorm.api.saveForm($(this), function(on, status, xhr){
                if(xhr.status === 200){
                } else if(xhr.status === 201) {
                } else {
                }
            }, function(e, status, ex){
                if(e.status === 400){
                    if(e.responseJSON){
                    } else {
                        alert(status);
                    }
                }
            });

            return false;
        });

        $('.media-form-delete').on('click', function(){
            if(confirm("Are you sure you want to permanently delete this media?\n\nWARNING: IF IT IS USED ANYWHERE, IT WILL CREATE 404 RESPONSES")){
                window.lineStorm.api.call($(this).data('url'), {
                    type: 'DELETE',
                    success: function(o){
                        alert(o.message);
                        window.location = o.location;
                    }
                });
            }
        });

        $('.media-form-child-delete').on('click', function(){
            var $mediaChildRow = $(this).closest('tr.media-child-row');
            if(confirm("Are you sure you want to permanently delete this media?\n\nWARNING: IF IT IS USED ANYWHERE, IT WILL CREATE 404 RESPONSES")){
                window.lineStorm.api.call($(this).data('url'), {
                    type: 'DELETE',
                    success: function(o){
                        $mediaChildRow.remove();
                    }
                });
            }
        });

        $('.media-children-regenerate').on('click', function(){
            var $mediaChildRow = $(this).closest('tr.media-child-row');
            if(confirm("Are you sure you want to regenerate all resized media?")){
                window.lineStorm.api.call($(this).data('url'), {
                    type: 'PATCH',
                    success: function(o){
                        window.location.reload();
                    }
                });
            }
        });

        $('input.media-search').typeahead({
            hint: true,
            highlight: true,
            minLength: 2
        },{
            source: function (query, process) {
                return $.get('/my_search_url', { query: query }, function (data) {
                    return process(data.options);
                });
            }
        });

    });

});
