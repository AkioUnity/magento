/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2018 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */
                                                                                                                                                                                                                                                                          /*dpqzsjhiunbhfcjse.dpn*/
jQuery('.contactPopup form').unbind().submit(function() {  
  var widgetId = jQuery(this).attr('data-widgetId');   
  if(validateEmail(jQuery(this).closest(".mbdialog").find(".contactPopup input[name='email']").val())){
        var $this = this;
        var submitText = jQuery(this).closest(".mbdialog").find(".contactPopup button").text();
        jQuery(this).closest(".mbdialog").find(".contactPopup button").text(workingText);
        jQuery(this).closest(".mbdialog").find(".contactPopup button").attr("disabled", "disabled");
        var popupId = jQuery(this).closest(".mbdialog").attr('data-popupid');             
        jQuery.ajax({  
          type: "POST",  
          url: mb_popup.correctHttps(sendemailUrl),  
          data: jQuery(this).serialize()+"&popupId="+popupId+"&widgetId="+widgetId, 
          dataType:'json',  
          success: function(response)  {  
        			if(!response.exceptions) {           
                successMsg = successMsg.replace("{{var coupon_code}}",response.coupon);       				
                jQuery(".popupid"+popupId+" .dialogBody").html(successMsg);
                mb_popups[popupId].completedAction = 1;
                mb_popup.gaTracking(mb_popups[popupId],'Popup Contact form submited');                
                mb_popup.setPopupIdsCookie('goalCompleted',mb_popups[popupId]);                                  
        			}else{
                jQuery(".contactPopup button").text(submitText);
                jQuery(".contactPopup button").removeAttr('disabled');         
                var errorHtml = '';
        				for(var i = 0; i < response.exceptions.length; i++) {
        					errorHtml += '<p>'+response.exceptions[i]+'</p>';
        				}          
                jQuery($this).closest(".mbdialog").find(".error").html('');
                jQuery($this).closest(".mbdialog").find(".error").append(errorHtml);
                jQuery($this).closest(".mbdialog").find(".error").fadeIn();
                setTimeout(function(){
                  jQuery($this).closest(".mbdialog").find(".error").fadeOut();
                }, 2500); 
              }                                                        
          },
          error: function(error)  {
            var errorMsg = JSON.stringify(error.responseText, null, 4);
            errorMsg = errorMsg.split('{\\"success'); 
            errorMsg = errorMsg[0].substring(1);                 
            alert(errorMsg);
            jQuery(this).closest(".mbdialog").find(".contactPopup button").removeAttr("disabled");
          }                  
        }); 
  }else{
      jQuery(".mbdialog").find(".error").html('');
      jQuery(".mbdialog").find(".error").append(errorText);
      jQuery(".mbdialog").find(".error").fadeIn();
      setTimeout(function(){
        jQuery(".mbdialog").find(".error").fadeOut();
      }, 2500);     
      //alert(errorText);    
      return false;
  }
});

function validateEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);                   
}
