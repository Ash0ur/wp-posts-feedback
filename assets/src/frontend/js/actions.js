
( function($){

	$(document).ready(function(){

		var feedbackStatus = 0;

		$('.wpf-up').on('click', function(){
			$('.feedback-choice-wrapper').remove();
			$('.feedback-form-container').remove();
			$('.feedback-after-submit').show();
			addPostThumb( 'up' );
		});

		$('.wpf-down').on('click', function(){
			$('.feedback-choice-wrapper').remove();
			$('.feedback-container .feedback-form-container').show();
			addPostThumb( 'down' );
		});

		if ( WPF_ajax_data.recaptcha ) {
			$('.feedback-submit').prop('disabled', true );
		}

		$('#wpf-feedback-name, #wpf-feedback-email, #wpf-feedback-content').on('input', function() {
			if ( $(this).is(':invalid') ) {
				$(this).addClass('error');
			} else {
				$(this).removeClass('error');
			}
		})


		$('.feedback-submit' ).on( 'click', function(e) {
			e.preventDefault();

			if ( ! $('.feedback-form-container form')[0].checkValidity() ) {
				if ( $('#wpf-feedback-content').is(':invalid') ) {
					$('#wpf-feedback-content').addClass('error');
				}

				if ( WPF_ajax_data.userDetails == 1 ) {

					if ( $('#wpf-feedback-name').is(':invalid') ) {
						$('#wpf-feedback-name').addClass('error')
					}

					if ( $('#wpf-feedback-email').is(':invalid') ) {
						$('#wpf-feedback-email').addClass('error');
					}
				}
				return;
			}

			var feedbackContent = $('#wpf-feedback-content').val();


			var details = {
				'content' : feedbackContent,
				'postID'  : WPF_ajax_data.postID
			}

			if ( WPF_ajax_data.recaptcha == 1 ) {
				if ( grecaptcha !== undefined ) {
					details['g-recaptcha-response'] = grecaptcha.getResponse();
				}
			}

			if ( WPF_ajax_data.userDetails == 1 ) {
				var feedbackName    = $('input[name="wpf-feedback-name"]').val();
				var feedbackEmail   = $('input[name="wpf-feedback-email"]').val();

				details['name'] = feedbackName;
				details['mail'] = feedbackEmail;
			}

			var data = {
				'action'   : 'WPF_save_feedback',
				'security' : WPF_ajax_data.nonce,
				'data'     : details
			}

			$('.feedback-form-container').remove();
			$('.feedback-after-submit').show();

			$.post(
				WPF_ajax_data.ajaxUrl,
				data,
				function( response ) {
				}
			);
		})


		function addPostThumb( thumbType ) {
			var data = {
				'action': 'WPF_post_thumb',
				'security': WPF_ajax_data.nonce,
				'data': {
					'thumb' : thumbType,
					'postID': WPF_ajax_data.postID,
				}
			}
			$.post(
				WPF_ajax_data.ajaxUrl,
				data,
				function( response ) {
				}
			)
		}
	});


	window.wpfRecaptchaCallback = function() {
		jQuery('.feedback-submit').prop('disabled', false );
	}

})(jQuery);
