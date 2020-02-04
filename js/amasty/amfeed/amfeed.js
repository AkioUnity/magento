/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Feed
*/
function am_feed() 
{
	this.url = "";
        this.stopUrl = "";
	this.delay = 1000;
	this.profileId = 0;
	this.logElementId = 'am_feed_popup_log';
	this.stopped = false;
	this.completed = false;
	
	this.showPopup = function()
	{
		$('am_feed_popup').show();
		$('am_feed_overlay').show();
	}
	
	this.close = function(url)
	{
		if (!this.completed) {
			if (!confirm('Closing this window you will stop the process.\nAre you sure?')) {
				return;
			} else {
                this.stopped = true;
            }
		} else {
            this.hidePopup();
            if (url)
            {
                window.location.href = url;
            } else {
                window.location.reload();
            }
        }
	}
	
	this.stop = function(isError)
	{
        new Ajax.Request(this.stopUrl, 
    	{
    		parameters: {
    			profileId : this.profileId,
                error : isError
    		},
            onSuccess: function(response) {

	        }
    	});
	}
	
	this.hidePopup = function()
	{
		$('am_feed_popup').hide();
		$('am_feed_overlay').hide();
	}
	
    this.request = function(profileId)
    {
    	var _this = this;

    	if (profileId) {
    		$(_this.logElementId).update("");
    		this.profileId = profileId;
    		this.completed = false;
    	}
    	this.showPopup();
    	new Ajax.Request(this.url, 
    	{
    		parameters: {
    			profileId : this.profileId
    		},
            onSuccess: function(response) { 
                var data = response.responseText;
                if (!data || !data.isJSON()) {
                    _this.stop(true);
                    alert('System error: ' + data);
                    window.location.reload();
                }
                data = data.evalJSON(); 
                _this.completed = true;
                if (!data.isCompleted && !_this.stopped) {
                    _this.completed = false;
                    setTimeout(function() { _this.request(); }, _this.delay);
                }
                if (_this.stopped) {
                    _this.stop(false);
                    data.log = 'Generation stopped!';
                }
                _this.updateLog(data);
	        }
    	});
    }
    
    this.updateLog = function(json)
    {
        $('am_feed_popup_progress').writeAttribute('style', 'width:' + json.progress + '%');
        $(this.logElementId).insert('<li>' + json.log + '</li>', {position: 'content'});
    	if (this.completed && json.filepath) {
    		$('am_feed_popup_result').update('<a href="' + json.filepath + '">' + json.filename + '</a>');
    	}
    }  
}

var amFeedImport = Class.create({
    url: null,
    initialize: function(options) {
        this.url = options.url
    },
    load: function(id){
        if (id){
            var url = [this.url , this.url.indexOf('?') === -1 ? '?' : '&', 'id=', id].join('');
            document.location.href = url;
        }    
    }
})
