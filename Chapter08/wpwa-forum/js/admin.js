jQuery(document).ready(function($){
	$("#wpwaf_topic_visibility").change(function(e){        
        if($(this).val() == 'role'){
            $("#wpwaf_topic_role_panel").show();
        }else{
            $("#wpwaf_topic_role_panel").hide();
        }
    });
});