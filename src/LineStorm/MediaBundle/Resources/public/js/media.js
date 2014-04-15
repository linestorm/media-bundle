
define(['jquery', 'bootstrap', 'dropzone', '/assets/bundles/linestormcms/js/api'], function ($, bs, Dropzone, api) {

    // setup dropzone
    Dropzone.autoDiscover = false;

    var $form;
    var $dropzone;
    var carrosselDropZone;

    $(document).ready(function(){
        $form = $('form[name="linestorm_cms_form_media"]');
        $dropzone = $('.dropzone');

        carrosselDropZone = new Dropzone($dropzone[0], {
            url: $dropzone.data('url'),
            maxFiles: 1,
            acceptedFiles: 'image/*',
            init: function(){
                this.on("success", function(file, response) {
                    if(file.xhr.status == 200){
                        alert('An identical file already exists.');
                    } else {
                        $form.find('.img-preview > img').attr('src', response.src);
                        $form.find('input[name*="[src]"]').val(response.src);
                        $form.find('input[name*="[hash]"]').val(response.hash);
                        $form.find('input[name*="[title]"]').val(response.title);
                        $('.media-form').slideDown();
                        $dropzone.hide();
                    }
                    this.removeFile(file);

                });
                this.on("error", function(file, response) {
                    this.removeFile(file);
                    alert("Cannot add file: "+response);
                });
            },
            previewTemplate: $dropzone.data('prototype')
        });

        $form.on('submit', function(e){
            e.preventDefault();
            e.stopPropagation();
            $('#FormErrors').slideUp(function(){ $(this).html(''); });
            window.lineStorm.api.saveForm($form, function(on, status, xhr){
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
    });

});