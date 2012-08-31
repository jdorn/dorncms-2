//Chrome style tabs
jQuery(
	function () {
		// Select Tab List
		$('.tab-list').on('click','a.link',function() {
			$('.tab-list li.current').removeClass('current');
			$(this).closest('li').addClass('current');
			return false;
		}).on('click','a.close',function() {
			$(this).closest('li').remove();
		});
	}
);
