
define(['jquery', 'bootstrap', 'dropzone', 'typeahead', 'cms_api'], function ($, bs, Dropzone, typeahead, api) {

    // setup dropzone
    Dropzone.autoDiscover = false;

    var $form;
    var $dropzone;
    var carrosselDropZone;

    $(document).ready(function(){
        $form = $('form[name="linestorm_cms_form_media"]');
        $dropzone = $('.dropzone');

        if($dropzone.length){
            carrosselDropZone = new Dropzone($dropzone[0], {
                url: $dropzone.data('url'),
                acceptedFiles: 'image/*',
                thumbnailWidth: null,
                thumbnailHeight: null,
                init: function(){
                    this.on("success", function(file, response) {
                        var $form = $(file.previewElement);
                        $form.find('input[name*="[alt]"]').val(response.alt);
                        $form.find('input[name*="[src]"]').val(response.src);
                        $form.find('input[name*="[hash]"]').val(response.hash);
                        $form.find('input[name*="[credits]"]').val(response.credits);
                        $form.find('textarea[name*="[title]"]').val(response.title);
                    });
                    this.on("error", function(file, response) {
                        this.removeFile(file);
                        alert("Cannot add file: "+response);
                    });
                },
                previewTemplate: $dropzone.data('prototype')
            });

            $('.media-api-save').on('click', function(e){
                e.preventDefault();
                e.stopPropagation();

                var forms = $('form[name="linestorm_cms_form_media"]');
                window.lineStorm.api.saveForm(forms, function(on, status, xhr){
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
