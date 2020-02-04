var errorno = 1;
var dataForm = new VarienForm('review-form-popup');
Validation.addAllThese(
[
       ['validate-rating', LOCALE['Please select one of each of the ratings above'], function(v) {   
            var error = 0;
            jQuery('.ratings').each(function(){
                if(!jQuery(this).val()) {
                    error = 1;                                      
                }                           
            });
            if(error) return false;
            errorno = 0;
            return true;
        }]
]
);

jQuery('#review-form-popup').unbind().submit(function() {  
  jQuery('.required-entry').each(function(){
      if(!jQuery(this).val()) {
          errorno = 1;                                      
      }                           
  });
  if(errorno) return false;
  var popupId = jQuery(this).closest(".mbdialog").attr('data-popupid');
  var widgetId = jQuery(this).attr('data-widgetId');   
  var $this = this;
  var submitText = jQuery("#review-form-popup button").text();
  jQuery("#review-form-popup button").text(workingText);    
  jQuery("#review-form-popup button").attr('disabled','disabled');
  var cpnExp = '';
  if (typeof popupTimer[popupId].timer !== "undefined"){
    var cpnExp = popupTimer[popupId].timer;   
  }      
  jQuery.ajax({
    type: "POST",
    url: jQuery(this).attr('action'),
    data: jQuery(this).serialize()+"&widgetId="+widgetId+"&popupId="+popupId+"&cpnExpInherit="+cpnExp,
    dataType:'json',
    error: function(response){
      console.log(response)
    }, 
    success: function(response){       
			if(!response.exceptions) {  				
        mb_popup.gaTracking(mb_popups[popupId],'User popup review completed');
        mb_popups[popupId].completedAction = 1;
        mb_popup.setPopupIdsCookie('goalCompleted',mb_popups[popupId]);
        successMsg = successMsg.replace("{{var coupon_code}}",response.coupon);
        jQuery(".popupid"+popupId+" .dialogBody").html(successMsg);                
			} else {
        jQuery("#review-form-popup button").text(submitText);
        jQuery("#review-form-popup button").removeAttr('disabled');         
        var errorHtml = '';
				for(var i = 0; i < response.exceptions.length; i++) {
					errorHtml += '<p>'+response.exceptions[i]+'</p>';
				}          
        jQuery($this).closest(".mbdialog").find(".error").html('');
        jQuery($this).closest(".mbdialog").find(".error").append(errorHtml);
        jQuery($this).closest(".mbdialog").find(".error").fadeIn();
        setTimeout(function(){
          jQuery($this).closest(".mbdialog").find(".error").fadeOut();
        }, 3500);                    
			}             
    }             
  });  

});  

var numStars = 5;                
var defaultStar = '';
var ratingId = '';
jQuery('.popupRatings').each(function() {
  if(!defaultStar) defaultStar = 0;
  var starsObj           = this;
  var starsJqueryWrapper = jQuery(this);
  var starsCollection    = Array();
  starsObj.rating        = defaultStar;
  starsObj.ratingId = starsJqueryWrapper.attr('el-id');
  starsObj.ratingOptionIds = jQuery("#rating_"+starsObj.ratingId+"_val").val().split(',');
  for(var index = 0; index < starsObj.ratingOptionIds.length; index++) {
    var elStar    = document.createElement('div');
    var star      = jQuery(elStar);
    elStar.rating = index + 1;
    elStar.optionVal = starsObj.ratingOptionIds[index] 
    star.addClass('jquery-ratings-star');

    
    //add the star to the container
    starsJqueryWrapper.append(star);      
    starsCollection.push(star);
    
    //hook up the click event
    star.click(function() {
      var ratingText = '';
      switch(this.rating){
        case 1:
          ratingText = LOCALE['Poor'];
          break;
        case 2:
          ratingText = LOCALE['Fair'];
          break;
        case 3:
          ratingText = LOCALE['Average'];
          break;
        case 4:
          ratingText = LOCALE['Good'];
          break;
        case 5:
          ratingText = LOCALE['Excellent'];                                               
      }
      jQuery("input[name='ratings["+starsObj.ratingId+"]']").val(this.optionVal)   
      jQuery(this).siblings('span').text(ratingText)
      starsObj.rating = this.rating;
    });
    
    star.mouseenter(function() {
      //Highlight selected stars.
      for(var index = 0; index < this.rating; index++) {
        starsCollection[index].addClass('jquery-ratings-full');
      }
      //Unhighlight unselected stars.
      for(var index = this.rating; index < numStars; index++) {
        starsCollection[index].removeClass('jquery-ratings-full');
      }
    });
    
    starsJqueryWrapper.mouseleave(function() {
      //Highlight selected stars.
      for(var index = 0; index < starsObj.rating; index++) {
        starsCollection[index].addClass('jquery-ratings-full');
      }
      //Unhighlight unselected stars.
      for(var index = starsObj.rating; index < numStars ; index++) {
        starsCollection[index].removeClass('jquery-ratings-full');
      }
    });
  }
});