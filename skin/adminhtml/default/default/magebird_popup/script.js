(function(){ 
  jQuery(document).ready(function() {
    activateTooltip();
    if(jQuery('.adminhtml-magebird-popup-duplicate').length==0 && jQuery('.adminhtml-magebird-popup-edit').length==0) return;    
    var clearcacheMsg = jQuery('.clearcache').attr('title');
    jQuery(".clearcache").after('<span style="font-weight:bold" class="popupTooltip" title="'+clearcacheMsg+'">(?)</span>');
    if ( jQuery.isFunction(jQuery.fn.on) ) {      
        jQuery('body').on('change', '#page_id', function(){                      
          showIdsField();
        });
        jQuery('body').on('change', '#horizontal_position', function(){                      
          showHorizontalPosField();
        });  
        jQuery('body').on('change', '#vertical_position', function(){                      
          showVerticalFields();
        });                     
    }else{
      jQuery("#page_id").change(function() {
        showIdsField();        
      });
      jQuery("#horizontal_position").change(function() {
        showHorizontalPosField();        
      });   
        jQuery('body').on('change', '#vertical_position', function(){                      
          showVerticalFields();
        });                    
    }                          
    showIdsField();
    showHorizontalPosField();
    showVerticalFields();
    
    if ( jQuery.isFunction(jQuery.fn.on) ) {
      jQuery('body').on('change', '#width_unit', function(){                      
        widthUnitListener();
      });     
    }else{
      jQuery("#width_unit").change(function() {
        widthUnitListener();        
      });    
    }
    
    widthUnitListener();
  });
  
  function widthUnitListener(){
      var widthUnit = jQuery('#width_unit').val();
      if(widthUnit==1){
        jQuery("#horizontal_position option[value='4']").prop("disabled", false);
        jQuery("#horizontal_position option[value='5']").prop("disabled", false);
      }else{
        jQuery("#horizontal_position option[value='4']").prop("disabled", true);
        jQuery("#horizontal_position option[value='5']").prop("disabled", true);      
        if(!jQuery("#horizontal_position").val()){
          jQuery("#horizontal_position").val(1)
        }
        //jQuery("#horizontal_position").prop("disabled", true);
        
        //jQuery("#horizontal_position").val(1);
      }    
  }
  
  function showIdsField(){
      var showAt = jQuery('#page_id').val();
      if(jQuery.inArray('2', showAt)==-1){
        jQuery('#product_ids').parent().parent().hide();
      }else{
        jQuery('#product_ids').parent().parent().show();
      }
      
      if(jQuery.inArray('3', showAt)==-1){
        jQuery('#category_ids').parent().parent().hide();  
      }else{
        jQuery('#category_ids').parent().parent().show();
      }  
      
      if(jQuery.inArray('6', showAt)==-1){
        jQuery('#specified_url').parent().parent().hide();  
      }else{
        jQuery('#specified_url').parent().parent().show();
      }                                    
  }
  
  function showHorizontalPosField(){
      var verticalPos = jQuery('#horizontal_position').val();
      if(verticalPos==1){
        jQuery('#horizontal_position_px').parent().parent().hide();
      }else{
        jQuery('#horizontal_position_px').parent().parent().show();
      }                                  
  }  
  
  function showVerticalFields(){
      var verticalPos = jQuery('#vertical_position').val();
      if(verticalPos==4){
        jQuery('#vertical_position_px').parent().parent().hide();
        //jQuery('#element_id_position').parent().parent().hide();
      }else{
        jQuery('#vertical_position_px').parent().parent().show();
        //jQuery('#element_id_position').parent().parent().show();
      }                                 
  }   
  
  function activateTooltip(){
    jQuery('.popupTooltip').hover(function(e){ // Hover event
    var titleText = jQuery(this).attr('title');
    jQuery(this).data('tiptext', titleText).removeAttr('title');
    jQuery('<p class="tooltip"></p>')
      .html(titleText)
      .appendTo('body')
      .css('top', (e.pageY -50) + 'px')
      .css('left', (e.pageX - 340) + 'px')
      .fadeIn('fast');
    }, function(){ // Hover off event
      jQuery(this).attr('title', jQuery(this).data('tiptext'));
      jQuery('.tooltip').remove();
    });
  }  
})();

function setIssetCookie(cname) {
    var d = new Date();
    d.setTime(d.getTime() + (900*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname + "=1; " + expires + "; path=/";        
}

function getIssetCookie(key) {
    var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? keyValue[2] : null;
}

function disableInputs(selector){
    var errorMsg = '';
    var widgetButtonListen = setInterval(function(){
      if(jQuery('.error-msg.mb_widget').length>0){
        clearInterval(widgetButtonListen)
        errorMsg = jQuery('.error-msg.mb_widget').text();
        jQuery("#insert_button").attr("onclick",'return false;'); 
        jQuery("#insert_button").attr('id','insertPopupWidget');
        jQuery(selector + ' input,'+selector + ' textarea,' + selector + ' button,' + selector + ' select').attr("disabled",true); 
      }
    },10)   
        
    if ( jQuery.isFunction(jQuery.fn.on) ) {
      jQuery('body').on('click', '#insertPopupWidget', function(){                      
        alert(errorMsg)
        return false;
      });     
    }else{
      jQuery("#insertPopupWidget").click(function() {
        alert(errorMsg)
        return false;       
      });    
    }          
} 