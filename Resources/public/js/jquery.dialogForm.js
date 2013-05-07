/**
 * jQuery dialog forn plugin
 */

(function($){
    "use strict";

    $.fn.dialogForm = function(options) {
        var $dialog;
        var options = options || {};
        if (!options.trigger) {
            throw new Error('Please specify the trigger');
        }
        if (!options.url) {
            throw new Error('Please specify the url');
        }
        options.width = options.width || 400;

        $(document).delegate(options.trigger, 'click', function(e) {
            e.preventDefault();
            $.ajax({
                url: options.url,
                type: 'get',
                success: function(data) {
                    createDialog(data);
                }
            });
        });

        function createDialog(data) {
            destroyDialog();
            var $form = $(data);
            var formTitle = $form.data('title');
            var formId = '#' + $form.attr('id');

            var formButtons = {};
            var submitButton = $form.data('button-submit');
            var cancelButton = $form.data('button-cancel');
            if (submitButton) {
                formButtons[submitButton] = function() {
                    $.ajax({
                        url: options.url,
                        type: 'post',
                        data: $(formId).serialize(),
                        success: function (data) {
                            processResponse(data, $dialog);
                        }
                    });
                };
            }
            if (cancelButton) {
                formButtons[cancelButton] = function() {
                    destroyDialog();
                };
            }

            $form.find('.span4').each(function() {
                $(this).removeClass('span4').addClass('span10');
            });

            $dialog = $form.dialog({
                title: formTitle,
                modal: true,
                resizable: false,
                width: options.width,
                buttons: formButtons
            });

            $(formId + ' select').select2({ allowClear: true });
        }

        function destroyDialog() {
            if ($dialog && $dialog.length) {
                $dialog.remove();
            }
            $dialog = null;
        }

        function processResponse(data, $dialog) {
            if (isJSON(data)) {
                data = $.parseJSON(data);
                if (data.status == 1) {
                    window.location = data.url;
                }
            } else if ($(data).prop('tagName').toLowerCase() == 'form') {
                createDialog(data);
            }
        }

        function isJSON(str) {
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        }
    };

})(jQuery);
