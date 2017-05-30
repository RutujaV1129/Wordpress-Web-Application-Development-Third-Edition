jQuery(document).ready(function($){
	$("#wpwaf_forum_join").click(function(){
		var join = $(this);
		$(this).html(wpwafFront.processing);
		var forum_id = $(this).attr('data-forum-id');
		jQuery.post(
            wpwafFront.ajaxUrl,
            {
                'action': 'wpwaf_forum_join',
                'forum_id':  forum_id
            },
            function(response){
                if(response.status == 'success'){
                	join.attr('id','wpwaf_forum_member');
                	join.html(wpwafFront.member);
                }		    	
            },"json");
	});
});