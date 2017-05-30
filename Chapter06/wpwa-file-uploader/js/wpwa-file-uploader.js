jQuery(document).ready(function($){
    $(".wpwaf_files").each(function(){

        var fieldId = $(this).attr("id");

        $(this).after("<div id='wpwaf_upload_panel_"+ fieldId +"' ></div>");
        $("#wpwaf_upload_panel_"+ fieldId).html("<input type='button' value='"+WPWAUpload.addFileText+"' class='wpwaf_upload_btn' id='"+ fieldId +"' />");
        $("#wpwaf_upload_panel_"+ fieldId).append("<div style='margin:20px 0' class='wpwaf_preview_box' id='"+ fieldId +"_panel' ></div>");
        $(this).remove();
    });


	
    $(".wpwaf_upload_btn").click(function(){       
        var uploadObject = $(this);
        var sendAttachmentMeta = wp.media.editor.send.attachment;

        wp.media.editor.send.attachment = function(props, attachment) {

            $(uploadObject).parent().find(".wpwaf_preview_box").append("<img class='wpwaf_img_prev' style='float:left;with:75px;height:75px' src='"+ WPWAUpload.imagePath +"document.png' />");
            $(uploadObject).parent().find(".wpwaf_preview_box").append("<div class='wpwaf_prev_file_name' style='margin:25px 0;float:left'>"+ attachment.filename +"</div>");
            $(uploadObject).parent().find(".wpwaf_preview_box").append("<input class='wpwaf_img_prev_hidden' type='hidden' name='h_"+ $(uploadObject).attr("id")
                +"[]' value='"+ attachment.url +"' />");
            $(uploadObject).parent().find(".wpwaf_preview_box").append("<div style='clear:both' >&nbsp;</div>");

            wp.media.editor.send.attachment = sendAttachmentMeta;
        }

        wp.media.editor.open();
        return false;   
    });
    
    
    $("body").on("dblclick", ".wpwaf_img_prev" , function() {        
        $(this).next(".wpwaf_img_prev_hidden").remove();
        $(this).next(".wpwaf_prev_file_name").remove();
        $(this).remove();
    });


});
