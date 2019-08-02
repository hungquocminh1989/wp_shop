<?php defined( 'ABSPATH' ) || exit; ?>

<button type="button" class="button-secondary" id="my-ajax-trigger" data-action="myajaxaction">Generate Token</button>

<script type="text/javascript">
	var $ = jQuery;
	(function($){
	    $(document).ready(function(){
	        $('#my-ajax-trigger').click(function(){
	        	System.message_confirm('Thực xử lý token ?',function(){
	        		var ajax = new System();
					ajax.done_func = function(json) {
						if(json.success == true){
							System.message_success('Lấy token thành công!',function(){
								$('#acf-field_5d3343300d412').val(json.data);
							});
						}
						else{
							System.message_error('Có lỗi xảy ra!',function(){
								$('#acf-field_5d3343300d412').val('');
							});
						}
						
			    	};
			    	ajax.connect("POST",{
			            action: "get_token", //Tên action
			            fb_user : $("#acf-field_5d3340cef3dab").val(),//Biến truyền vào xử lý. $_POST['website']
			            fb_pass : $("#acf-field_5d334124f3dac").val(),//Biến truyền vào xử lý. $_POST['website']
				    });
				});
		    })
    	});
    })($);
</script>