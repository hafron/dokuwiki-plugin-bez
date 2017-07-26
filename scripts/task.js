bez.ctl.task = function() {
    var $task_form = jQuery('.bez_task_form');
    
    if ($task_form.length > 0) {
        //date picker
        jQuery("input[name=plan_date]").datepicker({
            dateFormat: "yy-mm-dd"
            });
        if (jQuery("input[name=all_day_event]").is(":checked")) {
            jQuery('#task_datapair').hide();
        }
        jQuery("input[name=all_day_event]").on('change', function() {
            if (jQuery(this).is(":checked")) {
                jQuery('#task_datapair').hide();
            } else {
                jQuery('#task_datapair').show();
            }
        });

        //time picker
        jQuery('#task_datapair .time').timepicker({
                'showDuration': true,
                'timeFormat': 'H:i'
            });
        var timeDatepair = new Datepair(jQuery('#task_datapair').get(0));
        bez.rich_text_editor($task_form.find('textarea'), $task_form.find('.bez_toolbar'));
    }
    
    if (jQuery('#reason').length > 0) {
        jQuery('#no_evaluation').on('change', function() {
            if (jQuery(this).prop('checked') === true) {
                jQuery('#reason').prop('disabled', true).hide();
                jQuery('.bez_reason_toolbar').hide();
            } else {
                jQuery('#reason').prop('disabled', false).show();
                jQuery('.bez_reason_toolbar').show();
            }
            
        });
		bez.rich_text_editor(jQuery('#reason'), jQuery('.bez_reason_toolbar'));
	}

    jQuery('#bez_hidden_issue').hide();
    jQuery('#bez_show_issue').on('click', function (e) {
        e.preventDefault();
        jQuery('#bez_hidden_issue').slideDown();
        jQuery(this).hide();
    });
    
    if (jQuery('.bez_task_metaform').length > 0) {
        var tooltips = jQuery('.bez_task_metaform').find("input, select").tooltip({
                position: {
                    my: "left top",
                    at: "right+5 top-5",
                    collision: "none"
                }
            });
        jQuery.validate({
            form: '.bez_task_metaform',
            inlineErrorMessageCallback:  function($input, errorMessage, config) {
                if ($input.tooltip("instance") === undefined) {
                    return false;
                }
    
                if (errorMessage) {
                    //customDisplayInlineErrorMessage($input, errorMessage);
                    $input.attr('title', errorMessage);
                    $input.tooltip("open");
                } else {
                    //customRemoveInlineError($input);
                    $input.tooltip("disable");
                }
                return false; // prevent default behaviour
            }
        });
        
        jQuery("input[name=date], input[name=close_date]").datepicker({
			dateFormat: "yy-mm-dd"
        });
    }
};
