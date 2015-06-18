/* global screenReaderText */
( function( $ ) {
		// normal tabs
			$('.tabs-list a').click(function() {				
				var tab = $(this).data('tab'),
					tabs_data = $(this).closest('.tabs-list').siblings('.tabs-data'),
					parent = $(this).parent().parent();
				
				parent.find('.active').removeClass('active');
				$(this).parent().addClass('active');
				
				tabs_data.find('.tab-posts.active').hide();
				tabs_data.find('#recent-tab-' + tab).fadeIn().addClass('active');

				return false;
				
			});
            
    $('#btnToggle').click(function(){
    if ($(this).hasClass('on')) {
    $('#main .col-md-6').addClass('col-md-4').removeClass('col-md-6');
    $(this).removeClass('on');
    }
    else {
    $('#main .col-md-4').addClass('col-md-6').removeClass('col-md-4');
    $(this).addClass('on');
    }
    });

} )( jQuery );
