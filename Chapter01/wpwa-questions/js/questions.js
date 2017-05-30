jQuery(document).ready(function($) {
    
    $('body').on("click", ".answer-status" , function() { 

        // Get the button object and current status of the answer
        var answer_button = $(this);
        var answer_status  = $(this).attr("data-ques-status");

        // Get the ID of the clicked answer using hidden field
        var comment_id = $(this).parent().find(".hcomment").val();
        var data = {
            "comment_id":comment_id,
            "status": answer_status
        };

        // Create the AJAX request to save the status to database
        $.post( wpwaconf.ajaxURL, {
            action:"mark_answer_status",
            nonce:wpwaconf.ajaxNonce,
            data : data,
        }, function( data ) {
            if("success" == data.status){
                if("valid" == answer_status){
                    answer_button.val("Mark as Incorrect");
                    answer_button.attr("data-ques-status","invalid");
                }else{
                    answer_button.val("Mark as Correct");
                    answer_button.attr("data-ques-status","valid");
                }
            }
        }, "json");
    });
});


