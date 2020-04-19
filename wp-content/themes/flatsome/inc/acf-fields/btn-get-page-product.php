<?php defined( 'ABSPATH' ) || exit; ?>

<button type="button" class="button-secondary" id="btn_get_products">Create Products From Page</button>

<script type="text/javascript">
	var $ = jQuery;
	(function($){
	    $(document).ready(function(){
	        $('#btn_get_products').click(function(){
	        	System.message_confirm('Thực hiện xử lý đồng bộ từ bài viết trên page ?',function(){
	        		var ajax = new System();
					ajax.done_func = function(json) {
						if(json.success == true){
							System.message_success('Đồng bộ thành công!',function(){
								
							});
						}
						else{
							System.message_error('Có lỗi xảy ra!',function(){
								
							});
						}
						
			    	};
			    	ajax.connect("POST",{
			            action: "get_products", //Tên action
			            token : $("#acf-field_5e9bd68b79305").val(),//Biến truyền vào xử lý. $_POST['website']
				    });
				});
		    })
    	});
    })($);
</script>