jQuery(document).ready(function(e){wp.codeEditor.initialize(e("#feedback-custom-css")),e("#feedback-post-type-title").on("change",function(){e("#feedback-title-"+e(this).val()).addClass("active").siblings().removeClass("active")}),e("#feedback-thumbs-up").wpColorPicker(),e("#feedback-thumbs-down").wpColorPicker(),jQuery("#feedback-thumbs-up").iris({change:function(e,c){jQuery(".dashicons-thumbs-up").css("color",c.color.toString())}}),jQuery(" #feedback-thumbs-down").iris({change:function(e,c){jQuery(".dashicons-thumbs-down").css("color",c.color.toString())}})});