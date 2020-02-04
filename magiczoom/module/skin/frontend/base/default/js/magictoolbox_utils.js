//var magicToolboxLinks = [];//defined in header.phtml
var optionLabels = {};
var optionTitles = {};
var optionProductIDs = {};
var choosedOptions = {};
//var magicToolboxProductId = 0;//defined in header.phtml
//var magicToolboxOptionTitles = '';//defined in header.phtml
//var magicToolboxSwitchMetod = 'click';//defined in header.phtml
//var magicToolboxMouseoverDelay = 0;//defined in header.phtml

var allowMagicToolboxChange = true;

function mtGetProductId() {
    var productId;
    if(typeof optionsPrice.productId != 'undefined') {
        productId = optionsPrice.productId;
    } else {
        var inputs = document.getElementsByName('product');
        if(inputs.length) {
            productId = inputs[0].value;
        } else {
            productId = magicToolboxProductId;
        }
    }
    return productId;
}

function magicToolboxPrepareOptions() {
    var productId = mtGetProductId();

    var container = document.getElementById('MagicToolboxSelectors'+productId);
    if(container) {
        var aTagsArray = Array.prototype.slice.call(container.getElementsByTagName('a'));
        for(var i = 0; i < aTagsArray.length; i++) {
            if(aTagsArray[i].getElementsByTagName('img').length) {
                magicToolboxLinks.push(aTagsArray[i]);
            }
        }
    }

    //NOTE: for products with options
    for(var optionID in optionLabels) {
        var elements = document.getElementsByName('options['+optionID+']');
        if(elements) {
            for(var i = 0, l = elements.length; i < l; i++) {
                var eventType = (elements[i].type == 'radio') ? 'click' : 'change';
                $mjs(elements[i]).jAddEvent(eventType, function(e) {
                    var objThis = e.target || e.srcElement;
                    var optionID = objThis.name.replace('options[', '').replace(']', '');
                    magicToolboxOnChangeOption(objThis, optionTitles[optionID]);
                });
            }
        }
    }

    //NOTE: for configurable products
    if(typeof spConfig != 'undefined' && typeof spConfig.config.attributes != 'undefined') {
        for(var attributeID in spConfig.config.attributes) {
            optionLabels[attributeID] = {};
            optionProductIDs[attributeID] = {};
            optionTitles[attributeID] = spConfig.config.attributes[attributeID].label.toLowerCase();
            var options = spConfig.config.attributes[attributeID].options;
            for(var k = 0; k < options.length; k++) {
                var option = options[k];
                if(typeof option == 'object') {
                    optionLabels[attributeID][option.id] = option.label.replace(/(^\s+)|(\s+$)/g, "")/*.replace(/"/g, "'")*/.toLowerCase();
                    optionProductIDs[attributeID][option.id] = {};
                    for(var i = 0, productsLength = option.products.length; i < productsLength; i++) {
                        optionProductIDs[attributeID][option.id][i] = option.products[i];
                    }
                }

                //NOTE: this fix for 'option-associated-with-images' option to work when Mage_ConfigurableSwatches module enabled
                if(typeof option.id != 'undefined') {
                    var swatchElement = document.getElementById('swatch'+option.id);
                    if(swatchElement) {
                        Element.writeAttribute(swatchElement, 'data-attribute-id', attributeID);
                        Event.observe(swatchElement, 'click', function(e) {
                            var attrID = this.getAttribute('data-attribute-id');
                            var objThis = document.getElementById('attribute'+attrID);
                            //NOTE: need delay for IE 8 because the select value is changed too late
                            setTimeout(function(){
                                magicToolboxOnChangeOptionConfigurable(objThis, optionTitles[attrID]);
                            }, 1);
                        });
                    }
                }

            }
            //NOTE: for select in configurable.phtml
            var selectEl = document.getElementById('attribute'+attributeID);
            if(selectEl) {
                $mjs(selectEl).jAddEvent('change', function(e) {
                    var objThis = e.target || e.srcElement;
                    var attrID = objThis.id.replace('attribute', '');
                    magicToolboxOnChangeOptionConfigurable(objThis, optionTitles[attrID]);
                });
            }
        }
    }
    //if(typeof opConfig != 'undefined') opConfig.reloadPrice();

    //NOTE: to swicth between 360, zoom and video
    var isMagicZoom = (magicToolboxTool == 'magiczoom' || magicToolboxTool == 'magiczoomplus'),
        loadVimeoJSFramework = function() {
            //NOTE: to avoid multiple loading
            if(typeof(arguments.callee.loadedVimeoJSFramework) !== 'undefined') {
                return;
            }
            arguments.callee.loadedVimeoJSFramework = true;

            //NOTE: load vimeo js framework
            if(typeof(window.$f) == 'undefined') {
                var firstScriptTag = document.getElementsByTagName('script')[0],
                    newScriptTag = document.createElement('script');
                newScriptTag.async = true;
                newScriptTag.src = 'https://secure-a.vimeocdn.com/js/froogaloop2.min.js';
                firstScriptTag.parentNode.insertBefore(newScriptTag, firstScriptTag);
            }
        },
        loadYoutubeApi = function() {
            //NOTE: to avoid multiple loading
            if(typeof(arguments.callee.loadedYoutubeApi) !== 'undefined') {
                return;
            }
            arguments.callee.loadedYoutubeApi = true;

            //NOTE: load youtube api
            if(typeof(window.YT) == 'undefined' || typeof(window.YT.Player) == 'undefined') {
                var firstScriptTag = document.getElementsByTagName('script')[0],
                    newScriptTag = document.createElement('script');
                newScriptTag.async = true;
                newScriptTag.src = 'https://www.youtube.com/iframe_api';
                firstScriptTag.parentNode.insertBefore(newScriptTag, firstScriptTag);
            }
        },
        pauseYoutubePlayer = function(iframe) {
            if(typeof(arguments.callee.youtubePlayers) === 'undefined') {
                arguments.callee.youtubePlayers = {};
            }
            var id = iframe.getAttribute('id');
            if(id && typeof(arguments.callee.youtubePlayers[id]) != 'undefined') {
                arguments.callee.youtubePlayers[id].pauseVideo();
                return;
            }
            var player = new window.YT.Player(iframe, {
                events: {
                    'onReady': function(event) {
                        event.target.pauseVideo();
                    }
                }
            });
            id = iframe.getAttribute('id');
            arguments.callee.youtubePlayers[id] = player;
            return;
        },
        switchFunction = function(event) {

            event = event || window.event;

            var element = event.target || event.srcElement,
                currentContainer = document.querySelector('.mt-active'),
                currentSlideId = null,
                newSlideId = null,
                newContainer = null,
                switchContainer = false;

            if(!currentContainer) {
                return false;
            }

            if(element.tagName.toLowerCase() != 'a') {
                element = element.parentNode;
                if(element.tagName.toLowerCase() != 'a') {
                    return false;
                }
            }

            currentSlideId = currentContainer.getAttribute('data-magic-slide');
            newSlideId = element.getAttribute('data-magic-slide-id');

            if(currentSlideId == newSlideId/* && currentSlideId == 'zoom'*/) {
                if(isMagicZoom) {
                    allowHighlightActiveSelectorOnUpdate = false;
                }
                magicToolboxHighlightActiveSelector(element);
                return false;
            }

            //NOTE: spike for native video support
            if(magicToolboxTool == 'magicthumb' && newSlideId.match(/^video\-\d+$/)) {
                newSlideId = 'zoom';
            }

            //NOTE: check when one image + 360 selector
            newContainer = document.querySelector('div[data-magic-slide="'+newSlideId+'"]');

            if(!newContainer) {
                return false;
            }

            if(newSlideId == 'zoom' && isMagicZoom) {
                //NOTE: in order to magiczoom(plus) was not switching selector
                event.stopQueue && event.stopQueue();
            }

            //NOTE: switch slide container
            currentContainer.className = currentContainer.className.replace(/(\s|^)mt-active(\s|$)/, ' ');
            newContainer.className += ' mt-active';

            if(newSlideId == 'zoom') {
                if(isMagicZoom) {
                    //NOTE: hide image to skip magiczoom(plus) switching effect
                    if(!$mjs(element).jHasClass('mz-thumb-selected')) {
                        document.querySelector('#'+magicToolboxToolMainId+' .mz-figure > img').style.visibility = 'hidden';
                    }
                    //NOTE: switch image
                    MagicZoom.switchTo(magicToolboxToolMainId, element);
                }
                allowHighlightActiveSelectorOnUpdate = false;
                magicToolboxHighlightActiveSelector(element);
            }

            var videoType = null;

            //NOTE: stop previous video slide
            if(currentSlideId.match(/^video\-\d+$/)) {
                //NOTE: need to stop current video
                var iframe = currentContainer.querySelector('iframe');
                if(iframe) {
                    videoType = iframe.getAttribute('data-video-type');
                    if(videoType == 'vimeo') {
                        var vimeoPlayer = window.$f(iframe);
                        if(vimeoPlayer) {
                            vimeoPlayer.api('pause');
                        }
                    } else if(videoType == 'youtube') {
                        pauseYoutubePlayer(iframe);
                    }
                }
            }

            //NOTE: load api for video if need it
            if(newSlideId.match(/^video\-\d+$/)) {
                videoType = element.getAttribute('data-video-type');
                if(videoType == 'vimeo') {
                    loadVimeoJSFramework();
                } else if(videoType == 'youtube') {
                    loadYoutubeApi();
                }
                magicToolboxHighlightActiveSelector(element);
            }

            if(newSlideId == '360') {
                magicToolboxHighlightActiveSelector(element);
            }

            event.preventDefault ? event.preventDefault() : (event.returnValue = false);

            return false;
        },
        switchEvent;

    if(isMagicZoom || magicToolboxTool == 'magicthumb') {

        var activeSlide, slideId, query, thumbnail;

        if(isMagicZoom) {
            switchEvent = (magicToolboxSwitchMetod == 'click' ? 'btnclick' : magicToolboxSwitchMetod);
            query = '.mz-thumb.mz-thumb-selected';
        } else {
            switchEvent = magicToolboxSwitchMetod;
            query = '.mgt-selector.mgt-active';
        }

        //NOTE: mark thumbnail
        activeSlide = document.querySelector('.magic-slide.mt-active');
        if(activeSlide) {
            slideId = activeSlide.getAttribute('data-magic-slide');
            if(slideId != 'zoom') {
                query = '[data-magic-slide-id="'+slideId+'"]';
            }
            thumbnail = document.querySelector(query);
            if(thumbnail) {
                thumbnail.className += ' active-selector';
            }
        }

        //NOTE: a[data-magic-slide-id]
        for(var j = 0, linksLength = magicToolboxLinks.length; j < linksLength; j++) {
            $mjs(magicToolboxLinks[j]).jAddEvent(switchEvent+' tap', switchFunction, 1);
        }

        //NOTE: start magicscroll if need it
        if((typeof(window['MagicScroll']) != 'undefined') && container && container.className.match(/(?:\s|^)MagicScroll(?:\s|$)/)) {
            if(isMagicZoom) {
                MagicScroll.start('MagicToolboxSelectors'+productId);
            } else {
                window.checkForThumbIsReadyIntervalID = setInterval(function() {
                    if (MagicThumb && MagicThumb.isReady('MagicThumbImage'+productId)) {
                        MagicScroll.start('MagicToolboxSelectors'+productId);
                        clearInterval(window.checkForThumbIsReadyIntervalID);
                        window.checkForThumbIsReadyIntervalID = null;
                    }
                }, 100);
            }
        }
    }
}

function magicToolboxHighlightActiveSelector(selectedElement) {
    //NOTE: to highlight selector when switching thumbnails
    for(var i = 0; i < magicToolboxLinks.length; i++) {
        magicToolboxLinks[i].className = magicToolboxLinks[i].className.replace(/(\s|^)active\-selector(\s|$)/, ' ');
    }
    if(selectedElement) {
        selectedElement.className += ' active-selector';
    }
}

function magicToolboxClickElement(element, eventType, eventName) {
    var event;
    if(document.createEvent) {
        event = document.createEvent(eventType);
        event.initEvent(eventName, true, true);
        element.dispatchEvent(event);
    } else {
        event = document.createEventObject();
        event.eventType = eventType;
        element.fireEvent('on' + eventName, event);
    }
    return event;
}

function magicToolboxOnChangeOption(element, optionTitle) {
    if(!allowMagicToolboxChange) {
        allowMagicToolboxChange = true;
        return;
    }

    if(magicToolboxInArray(optionTitle, magicToolboxOptionTitles)) {

        var label = mtGetOptionLabel(element);
        if(!label) return;

        var selector = mtGetMatchedSelector(label);
        if(!selector) return;

        mtSwitchToSelector(selector);
    }
}

function mtGetOptionLabel(element) {
    var id = '';
    if(element.type == 'radio' && element.checked) {
        id = element.name.replace('options[', '').replace(']', '');
    } else if(element.type == 'select-one') {
        id = element.id.replace('select_', '').replace('attribute', '');
    } else {
        return false;
    }
    if(element.value == '' || (typeof optionLabels[id][element.value] == 'undefined')) {
        return false;
    }
    return optionLabels[id][element.value];
}

function mtGetMatchedSelector(label) {
    var selector = null;
    for(var i = 0, l = magicToolboxLinks.length, img = null, alt = null; i < l; i++) {
        img = magicToolboxLinks[i].querySelector('img');
        if(!img) continue;
        alt = img.getAttribute('alt');
        if(!alt) continue;
        if(alt.replace(/(^\s+)|(\s+$)/g, "")/*.replace(/"/g, "'")*/.toLowerCase() == label) {
            selector = magicToolboxLinks[i];
            break;
        }
    }
    return selector;
}

function mtSwitchToSelector(selector) {
    if(magicToolboxTool == 'magiczoom' || magicToolboxTool == 'magiczoomplus') {
        if(magicToolboxSwitchMetod == 'click') {
            $mjs(selector).jCallEvent('btnclick', {target: selector});
        } else {
            allowMagicToolboxChange = false;
            magicToolboxClickElement(selector, 'MouseEvents', magicToolboxSwitchMetod);
        }
    } else {
        //NOTE: for MagicThumb
        allowMagicToolboxChange = false;
        magicToolboxClickElement(selector, 'MouseEvents', magicToolboxSwitchMetod);
    }
}

function magicToolboxOnChangeSelector(a) {
    if(!allowMagicToolboxChange) {
        allowMagicToolboxChange = true;
        return;
    }
    var label = a.firstChild.getAttribute('alt').replace(/(^\s+)|(\s+$)/g, "").toLowerCase();
    var reloadPrice = false;
    for(var optionID in optionLabels) {
        for(var optionValue in optionLabels[optionID]) {
            if(optionLabels[optionID][optionValue] == label && magicToolboxInArray(optionTitles[optionID], magicToolboxOptionTitles)) {

                //NOTE: this fix for 'option-associated-with-images' option to work when Mage_ConfigurableSwatches module enabled
                var swatchElement = document.getElementById('swatch'+optionValue);
                if(swatchElement) {
                    setTimeout(function(element) {
                        return function() {
                            allowMagicToolboxChange = false;
                            magicToolboxClickElement(element, 'Event', 'click');
                        }
                    }(swatchElement), magicToolboxMouseoverDelay);
                    //NOTE: return to prevent double switching when click on selector that has option title
                    return;
                }

                var elementNames = ['options['+optionID+']', 'super_attribute['+optionID+']'];
                for(var elementNameIndex = 0, elementNamesLength = elementNames.length; elementNameIndex < elementNamesLength; elementNameIndex++) {
                    var elements = document.getElementsByName(elementNames[elementNameIndex]);
                    for(var i = 0, l = elements.length; i < l; i++) {
                        if(elements[i].type == 'radio') {
                            if(elements[i].value == optionValue) {
                                setTimeout(function(element) {
                                    return function() {
                                        var radios = document.getElementsByName(element.name);
                                        for(var radioIndex = 0, radiosLength = radios.length; radioIndex < radiosLength; radioIndex++) {
                                            radios[radioIndex].checked = false;
                                        }
                                        element.checked = true;
                                        allowMagicToolboxChange = false;
                                        magicToolboxClickElement(element, 'Event', 'click');
                                    }
                                }(elements[i]), magicToolboxMouseoverDelay);
                                return;
                            }
                        } else if(elements[i].type == 'select-one') {
                            if(elements[i].options && !elements[i].disabled) {
                                for(var j = 0, k = elements[i].options.length; j < k; j++) {
                                    if(elements[i].options[j].value == optionValue) {
                                        setTimeout(function(element, optionValue) {
                                            return function() {
                                                element.value = optionValue;
                                                element.selectedIndex = j;
                                                allowMagicToolboxChange = false;
                                                magicToolboxClickElement(element, 'Event', 'change');
                                            }
                                        }(elements[i], elements[i].options[j].value), magicToolboxMouseoverDelay);
                                        return;
                                    }
                                }
                            }
                        } else {
                            break;
                        }
                    }
                }
            }
        }
    }
}

function magicToolboxOnChangeSelectorConfigurable(a) {
    if(!allowMagicToolboxChange) {
        allowMagicToolboxChange = true;
        return;
    }
    if(typeof useAssociatedProductImages != 'undefined') {
        var productId = a.getAttribute('data-id');
        var options = magicToolboxFindOptions(productId);
        if(typeof(spConfig) != 'undefined' && typeof(spConfig.settings) != 'undefined') {
            setTimeout(function(options) {
                return function() {
                    magicToolboxChangeOptions(0, options);
                }
            }(options), magicToolboxMouseoverDelay);
        }
    }
}

function magicToolboxFindOptions(associatedProductId) {
    var options = {};
    for(var attributeId in optionProductIDs) {
        for(var optionId in optionProductIDs[attributeId]) {
            for(var i in optionProductIDs[attributeId][optionId]) {
                if(associatedProductId == optionProductIDs[attributeId][optionId][i]) {
                    options[attributeId] = optionId;
                }
            }
        }
    }
    return options;
}

function magicToolboxChangeOptions(i, options) {
    var select = spConfig.settings[i];
    var attributeId = select.id.replace(/[a-z]*/, '');
    if(select.options && !select.disabled) {
        for(var j = 0; j < select.options.length; j++) {
            if(select.options[j].value == options[attributeId]) {
                select.value = select.options[j].value;
                select.selectedIndex = j;

                //NOTE: clear child elements in choosedOptions
                if(select.childSettings) {
                    for(var k = 0, childAttributeId = null; k < select.childSettings.length; k++) {
                        childAttributeId = select.childSettings[k].id.replace(/[a-z]*/, '');
                        if(typeof choosedOptions[childAttributeId] != 'undefined') {
                            delete choosedOptions[childAttributeId];
                        }
                    }
                }
                //NOTE: remember choosed option
                choosedOptions[attributeId] = select.value;

                allowMagicToolboxChange = false;
                magicToolboxClickElement(select, 'Event', 'change');
                i++;
                if(i < spConfig.settings.length) {
                    setTimeout(function(i, options) {
                        return function() {
                            magicToolboxChangeOptions(i, options);
                        }
                    }(i, options), 100);
                }
                return;
            }
        }
    }
}

function magicToolboxInArray(needle, haystack) {
    var o = {};
    for(var i = 0, l = haystack.length; i < l; i++) {
        o[haystack[i]] = '';
    }
    if(needle in o) {
        return true;
    }
    return false;
}

function magicToolboxOnChangeOptionConfigurable(element, optionTitle) {

    if(!allowMagicToolboxChange) {
        allowMagicToolboxChange = true;
        return;
    }

    var attributeId = element.id.replace(/[a-z]*/, '');

    //NOTE: clear choosedOptions
    if(typeof choosedOptions[attributeId] != 'undefined') {
        delete choosedOptions[attributeId];
    }
    //NOTE: clear child elements in choosedOptions
    if(element.childSettings) {
        for(var i = 0, childAttributeId = null; i < element.childSettings.length; i++) {
            childAttributeId = element.childSettings[i].id.replace(/[a-z]*/, '');
            if(typeof choosedOptions[childAttributeId] != 'undefined') {
                delete choosedOptions[childAttributeId];
            }
        }
    }

    //NOTE: remember choosed option
    if(element.value.length) {
        choosedOptions[attributeId] = element.value;
    }

    //NOTE: switch to labeled image of main product if it presents
    var label = mtGetOptionLabel(element);
    if(label) {
        var selector = mtGetMatchedSelector(label);
        if(selector) {
            mtSwitchToSelector(selector);
            return;
        }

        //NOTE: exluded images
        if(typeof mtLabeledImageUrls != 'undefined' && typeof mtLabeledImageUrls[label] != 'undefined') {
            var large = mtLabeledImageUrls[label]['large-image-url'],
                small = mtLabeledImageUrls[label]['small-image-url'],
                productId = mtGetProductId();
            MagicZoom.update('MagicZoomImage'+productId, large, small);
            return;
        }
    }

    if(typeof useAssociatedProductImages != 'undefined') {

        //var configurableProductId = spConfig.config.productId;
        //var mainSelectorImage = document.getElementById('imageMain'+configurableProductId);

        if(element.value.length === 0) {
            //if(mainSelectorImage) {
            //    mainSelectorImage.parentNode.click();
            //}
            return;
        }

        //var associatedProductId = magicToolboxFindProduct(attributeId, element.value);
        var associatedProductId = mtGetAssociatedProduct();
        if(!associatedProductId) {
            return;
        }

        var associatedImage = document.getElementById('imageConfigurable'+associatedProductId);
        if(associatedImage) {
            mtSwitchToSelector(associatedImage.parentNode);
            return;
        } else {
            // if(mainSelectorImage) {
            //     allowMagicToolboxChange = false;
            //     magicToolboxClickElement(mainSelectorImage.parentNode, 'MouseEvents', magicToolboxSwitchMetod);
            // }
            // return;
        }

    }

    //NOTE: switching to labeled image of main product takes place before
    //magicToolboxOnChangeOption(element, optionTitle);
}

function magicToolboxFindProduct(attributeId, optionId) {

    for(var i in optionProductIDs[attributeId][optionId]) {
        //NOTE: product associated with current option
        var pId = optionProductIDs[attributeId][optionId][i];
        for(var attrId in choosedOptions) {
            //selected option's ID
            var optId = choosedOptions[attrId];
            for(var j in optionProductIDs[attrId][optId]) {
                if(pId == optionProductIDs[attrId][optId][j]) {
                    optId = null;
                    break;
                }
            }
            if(optId != null) {
                pId = null;
                break;
            }
        }
        if(pId != null) {
            return pId;
        }
    }
    return optionProductIDs[attributeId][optionId][0];

}

function mtGetAssociatedProduct() {

    var el = null, ids = {}, id = false, first = true, keys;
    for(var attrId in optionProductIDs) {
        //NOTE: element of 'select' type for configurable options
        el = document.getElementById('attribute'+attrId);
        if(!el || el.value == '') {
            continue;
        }

        if(first) {
            first = false;
            ids = Object.assign({}, optionProductIDs[attrId][el.value]);
            continue;
        }

        for(var i in ids) {
            //NOTE: check for id in next option
            var notFound = true;
            for(var j in optionProductIDs[attrId][el.value]) {
                if(ids[i] === optionProductIDs[attrId][el.value][j]) {
                    notFound = false;
                    break;
                }
            }
            if(notFound) {
                delete ids[i];
            }
        }
    }

    keys = Object.keys(ids);
    if(keys.length) {
        id = ids[keys[0]];
    }

    return id;
}

//NOTE: add "PRESELECT COLORS PLUS SWATCHES" support (#58917)
if(typeof(colorSelected) == 'function') {
    function get_image_name(str) {
        return str.replace(/.*\/(.*?)$/i,'$1');
    }
    var colorSelectedBusy = false;
    window['old_colorSelected'] = window['colorSelected'];
    window['colorSelected'] = function() {
        jQuery('#image').attr('style', 'position:absolute;top:-10000px;');
        old_colorSelected.apply(undefined, arguments);
        if(!colorSelectedBusy) {
            jQuery('.MagicToolboxSelectorsContainer a:not([rel=""])').each(function() {
                if(get_image_name(jQuery(this).attr('href')) == get_image_name(jQuery('#image').attr('src'))) {
                    //magicToolboxClickElement(this, 'MouseEvents', magicToolboxSwitchMetod);
                    if(magicToolboxTool == 'magiczoom' || magicToolboxTool == 'magiczoomplus') {
                        MagicZoom.update(magicToolboxToolMainId, this);
                    } else {
                        //MagicThumb
                        magicToolboxClickElement(this, 'MouseEvents', magicToolboxSwitchMetod);
                    }
                }
            });
        }
    }
    window['old_magicToolboxOnChangeSelector'] = window['magicToolboxOnChangeSelector'];
    window['magicToolboxOnChangeSelector'] = function() {
        old_magicToolboxOnChangeSelector.apply(undefined, arguments);
        jQuery('li.swatchContainer img[onclick*="'+get_image_name(jQuery(arguments[0]).attr('href'))+'"]').each(function() {
            colorSelectedBusy = true;
            eval(jQuery(this).attr('onclick'));
            colorSelectedBusy = false;
        });
    }
}
