(function( $ ){
	
	var methods = {
		init : function() {
			var container = this;
			this.find('a.tab').click(function(){
				if (!$(this).hasClass('disabled'))
					container.tabs('toggleTab', this);
				return false;
			})
			
			var active = this.find('a.tab.active');
			if (active.length > 0)
				this.tabs('toggleTab', active[0]);
			return this;
		},
		
		toggleTab : function( tab ) {
			this.find('a.tab').removeClass('active');
			$(tab).addClass('active');
			
			this.find('.tab-content').slideUp();
			
			var name = $(tab).attr('name');
			$('#' + name + "_content").slideDown();
			return this;
		},
		
		enableAll : function() {
			this.find('a.tab').removeClass('disabled');
			return this;
		}
	};
	
	$.fn.tabs = function( method ) {
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		}
		else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		}
		else {
			$.error( 'Method ' + method + ' does not exist on jQuery.map' );
			return null;
		}
	}
	
})( jQuery );