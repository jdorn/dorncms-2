var editorTabs = function(tabList,editorArea,options) {
	this.tabList = tabList;
	this.editorArea = editorArea;

	options = options || {};
	this.options = $.extend({
		maxTabs: 3,
		onOpen: null,
		onFocus: null,
		onClose: null,
		onMaxTabs: null,
		iframeHtml: '<iframe class="editor" scrolling="no"></iframe>',
		tabHtml: "<span class='tab'><a href='#' class='open'>%name%</a><a href='#' class='close'>x</a></span>"
	},options);
	
	this.init();
};
editorTabs.prototype = {
	init: function() {
		this.tabList.html('');
		this.editorArea.html('');
		
		this.open_tabs = {};
	},
	open: function(url,name) {
		name = name || url;
		
		//if the url is already open, just bring it into focus
		if(this.open_tabs[name]) {
			this.focus(name);
			return true;
		}
		
		//if the max number of tabs is reached
		if(this.options.maxTabs > 0 && this.options.maxTabs <= this.open_tabs.length) {
			//call the callback if defined
			if(this.options.onMaxTabs) {
				this.options.onMaxTabs(url, name);
			}
			
			return false;
		}
		
		//create tab and iframe elements
		var editor = $(this.options.iframeHtml).attr('src',url).data('name',name);
		var tab = $('<li></li>').html(this.options.tabHtml.replace('%name%',name)).data('name',name);
		
		//add close and open event to tab
		var self = this;
		tab.on('click','.close',function() {
			self.close(name);
			return false;
		});
		tab.on('click','.open',function() {
			self.focus(name);
			return false;
		});
		
		//add the elements to the dom
		this.tabList.append(tab);
		this.editorArea.append(editor);
		
		//add to the open_tabs list
		this.open_tabs[name] = {
			tab: tab,
			editor: editor,
			url: url
		};
		
		//if callback is defined
		if(this.options.onOpen) {
			this.options.onOpen(url, name);
		}
		
		//focus the tab
		this.focus(name);
		
		return true;
	},
	focus: function(name) {
		if(!this.open_tabs[name]) throw "Tried to focus tab '"+name+"' that isn't open";
		
		//loop through tabs and add classes/styles
		for(var i in this.open_tabs) {
			if(i===name) {
				this.open_tabs[i].editor.show();
				this.open_tabs[i].tab.addClass('current');
			}
			else {
				this.open_tabs[i].editor.hide();
				this.open_tabs[i].tab.removeClass('current');
			}
		}
		
		//if callback is defined
		if(this.options.onFocus) {
			this.options.onFocus(this.open_tabs[name].url, name);
		}
	},
	close: function(name) {
		if(!this.open_tabs[name]) throw "Tried to close tab '"+name+"' that isn't open";
		
		//save the url for the callback function
		var url = this.open_tabs[name].url;
		
		//if the tab currently has focus, we need to focus another tab at the end
		var refocus;
		if(this.open_tabs[name].tab.hasClass('current')) {
			refocus = true;
		}
		else {
			refocus = false;
		}
		
		this.open_tabs[name].tab.remove();
		this.open_tabs[name].editor.remove();
		delete this.open_tabs[name];
		
		//if callback is defined
		if(this.options.onClose) {
			this.options.onClose(url, name);
		}
		
		if(refocus) {
			//focus the first remaining tab
			for (var i in this.open_tabs) {
				this.focus(i);
				break;
			}
		}
		
		return true;
	}
};
