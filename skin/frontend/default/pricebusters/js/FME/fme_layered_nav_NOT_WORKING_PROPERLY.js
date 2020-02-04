// checking if IE: this variable will be understood by IE: isIE = !false
isIE = /*@cc_on!@*/false;

Control.Slider.prototype.setDisabled = function()
{
    this.disabled = true;

    if (!isIE)
    {
        this.track.parentNode.className = this.track.parentNode.className + ' disabled';
    }
};

function fme_layered_hide_products()
{
    var items = $('fme_filters_list').select('a', 'input');
    n = items.length;
    for (i = 0; i < n; ++i) {
        items[i].addClassName('fme_layered_disabled');
    }

    if (typeof (fme_slider) != 'undefined')
        fme_slider.setDisabled();

    var divs = $$('div.fme_loading_filters');
    for (var i = 0; i < divs.length; ++i)
        divs[i].show();
}

function fme_layered_show_products(transport)
{
    var resp = {};
    if (transport && transport.responseText) {
        try {
            resp = eval('(' + transport.responseText + ')');
        }
        catch (e) {
            resp = {};
        }
    }

    if (resp.products) {

        var ajaxUrl = $('fme_layered_ajax').value;

        if ($('fme_layered_container') == undefined) {

            var c = $$('.col-main')[0];// alert(c.hasChildNodes());
            if (c.hasChildNodes()) {
                while (c.childNodes.length > 2) {
                    c.removeChild(c.lastChild);
                }
            }

            var div = document.createElement('div');
            div.setAttribute('id', 'fme_layered_container');
            $$('.col-main')[0].appendChild(div);

        }

        var el = $('fme_layered_container');
        el.update(resp.products.gsub(ajaxUrl, $('fme_layered_url').value));
        catalog_toolbar_init();

        $('catalog-filters').update(
                resp.layer.gsub(
                        ajaxUrl,
                        $('fme_layered_url').value
                        )
                );

        $('fme_layered_ajax').value = ajaxUrl;
    }

    // prevent redirection to homepage after ajax request 
    var stateObj = '';
    var stateObj = window.location.pathname;
    history.pushState('', '', stateObj);

    var catstack = (typeof localStorage.getItem("catstack") !== 'undefined') ? JSON.parse(localStorage.getItem("catstack")) : [];
    if (catstack.length > 0) {        
        window.onpopstate = function(event) {
            fme_layered_clearall_listener();
        }
    }else{
        //alert(catstack.length);       
        window.onpopstate = {};        
    }

    var items = $('fme_filters_list').select('a', 'input');
    n = items.length;
    for (i = 0; i < n; ++i) {
        items[i].removeClassName('fme_layered_disabled');
    }
    if (typeof (fme_slider) != 'undefined')
        fme_slider.setEnabled();

}

function fme_layered_add_params(k, v, isSingleVal)
{
    var el = $('fme_layered_params');
    var params = el.value.parseQuery();

    var strVal = params[k];
    if (typeof strVal == 'undefined' || !strVal.length) {
        params[k] = v;
    }
    else if ('clear' == v) {
        params[k] = 'clear';
    }
    else {
        if (k == 'price')
            var values = strVal.split(',');
        else
            var values = strVal.split('-');

        if (-1 == values.indexOf(v)) {
            if (isSingleVal)
                values = [v];
            else
                values.push(v);
        }
        else {
            values = values.without(v);
        }

        params[k] = values.join('-');
    }

    el.value = Object.toQueryString(params).gsub('%2B', '+');
}


/*
function fme_layered_make_request()
{
    fme_layered_hide_products();

    var params = $('fme_layered_params').value.parseQuery();
    //console.log(params);

    if (!params['dir'])
    {
        $('fme_layered_params').value += '&dir=' + 'asc';
    }

    new Ajax.Request(
            $('fme_layered_ajax').value + '?' + $('fme_layered_params').value,
            {
                method: 'get',
                onSuccess: fme_layered_show_products
            }
    );
}*/

function fme_layered_make_request()
{
    var lastloc = localStorage.getItem('lastloc');
    var lastoldloc = localStorage.getItem('lastoldloc');
    
    var queryString = (localStorage.getItem('queryString') != null) ? localStorage.getItem('queryString') : $('fme_layered_params').value;
    console.log(queryString);
    /*if(lastloc != lastoldloc) {
        localStorage.setItem('lastoldloc', lastloc);
    }*/
    if(lastoldloc == null) {
        localStorage.setItem('lastoldloc', window.location.href);
    }
    if(window.location.href == lastloc || lastloc == null || window.location.href == lastoldloc) {
        //console.log('true');
        fme_layered_hide_products();

        //var params = $('fme_layered_params').value.parseQuery();
        var params = queryString.parseQuery();
        var catstackval = localStorage.getItem("catstack");
        //if(catstackval !== 'null')
        //console.log(localStorage.getItem("catstack"));
        var catstack = (typeof localStorage.getItem("catstack") !== 'undefined' && catstackval !== 'null') ? JSON.parse(catstackval) : [];
    
        //var fme_layered_params = $('fme_layered_params').value;
        var fme_layered_params = queryString;
        if(params['clearall'] == "true" ){
            catstack.pop();
            
            if (catstack.length > 0) {
                fme_layered_params = "cat="+catstack[catstack.length-1].cat+"&dir=asc&p="+catstack[catstack.length-1].p;
            }
            
        }else{   
            
            if(!isEmpty(params)){   
                if (catstack.length > 0) {
                    if(catstack[catstack.length-1].p != params['p'])
                        catstack.push({cat:params['cat'],p:params['p']});
                }else
                    catstack.push({cat:params['cat'],p:params['p']});
            }
        }
        console.log(JSON.stringify(catstack));
        //console.log(localStorage.getItem('lastloc'));
        localStorage.setItem('lastloc', window.location.href);
        //console.log(window.location.href);
        /*var fkey = {
            'cat':params['cat'],
            'p':params['p']
        };*/
        //console.log(fkey);
        //console.log(JSON.stringify(fkey));
        //localStorage.setItem('filterkey', JSON.stringify(params));
        //localStorage.setItem('filterkey', JSON.stringify(fkey));
        //localStorage.setItem('lastloc', window.location.href);
        //localStorage.setItem('lastcat', params['cat']);
        localStorage.setItem('catstack', JSON.stringify(catstack));

        if (!params['dir'])
        {
            fme_layered_params += '&dir=' + 'asc';
        }
        //alert($('fme_layered_ajax').value + '?' + $('fme_layered_params').value);
        new Ajax.Request(
            $('fme_layered_ajax').value + '?' + fme_layered_params,
            {
                method: 'get',
                onSuccess: fme_layered_show_products
            }
        );
        //console.log(filterkey);
    } else {
        console.log('false');
        localStorage.setItem('lastloc', window.location.href);
        //
    }

    
}

window.onload = function() {
    fme_layered_make_request();
}
function clearAllFilter(){
    var catstack = [];
    localStorage.setItem('catstack', JSON.stringify(catstack));
    fme_layered_make_request();
}


function isEmpty(obj) {
    for(var key in obj) {
        if(obj.hasOwnProperty(key))
            return false;
    }
    return true;
}

function fme_layered_update_links(evt, className, isSingleVal)
{
    var link = Event.findElement(evt, 'A'),
            sel = className + '-selected';

    if (link.hasClassName(sel))
        link.removeClassName(sel);
    else
        link.addClassName(sel);

    //only one  price-range can be selected
    if (isSingleVal) {
        var items = $('fme_filters_list').getElementsByClassName(className);
        var i, n = items.length;
        for (i = 0; i < n; ++i) {
            if (items[i].hasClassName(sel) && items[i].id != link.id)
                items[i].removeClassName(sel);
        }
    }

    fme_layered_add_params(link.id.split('-')[0], link.id.split('-')[1], isSingleVal);

    fme_layered_make_request();

    Event.stop(evt);
}


function fme_layered_attribute_listener(evt)
{
    fme_layered_add_params('p', 1, 1);
    fme_layered_update_links(evt, 'fme_layered_attribute', 0);
}


function fme_layered_price_listener(evt)
{
    fme_layered_add_params('p', 1, 1);
    fme_layered_update_links(evt, 'fme_layered_price', 1);
}

function fme_layered_clear_listener(evt)
{
    var link = Event.findElement(evt, 'A'),
            varName = link.id.split('-')[0];

    fme_layered_add_params('p', 1, 1);
    fme_layered_add_params(varName, 'clear', 1);

    if ('price' == varName) {
        var from = $('adj-nav-price-from'),
                to = $('adj-nav-price-to');

        if (Object.isElement(from)) {
            from.value = from.name;
            to.value = to.name;
        }
    }

    fme_layered_make_request();

    Event.stop(evt);
}


function roundPrice(num) {
    num = parseFloat(num);
    if (isNaN(num))
        num = 0;

    return Math.round(num);
}

function fme_layered_category_listener(evt) {
    var link = Event.findElement(evt, 'A');
    var catId = link.id.split('-')[1];


    var reg = /cat-/;
    if (reg.test(link.id)) { //is search
        fme_layered_add_params('cat', catId, 1);
        fme_layered_add_params('p', 1, 1); 
        fme_layered_make_request();

        /*var cb = link.getElementsByTagName("input")[0];
        //alert(cb.type);
        if (cb.type == 'checkbox') {
            cb.checked = true;
            cb.className = 'checked';
        }*/
        
        Event.stop(evt);
    }
    
    //do not stop event
}

function catalog_toolbar_listener(evt) {
    catalog_toolbar_make_request(Event.findElement(evt, 'A').href);
    Event.stop(evt);
}

function catalog_toolbar_make_request(href)
{
    var pos = href.indexOf('?');
    if (pos > -1) {
        queryString = href.substring(pos + 1, href.length);
        queryString = queryString.replace("clearall=true", "");
        localStorage.setItem('queryString', queryString);
        $('fme_layered_params').value = href.substring(pos + 1, href.length);
    }
    fme_layered_make_request();
}


function catalog_toolbar_init()
{
    var items = $('fme_layered_container').select('.pages a', '.view-mode a', '.sort-by a');
    var i, n = items.length;
    for (i = 0; i < n; ++i) {
        Event.observe(items[i], 'click', catalog_toolbar_listener);
    }
}

function fme_layered_dt_listener(evt) {
    var e = Event.findElement(evt, 'DT');
    e.nextSiblings()[0].toggle();
    e.toggleClassName('fme_layered_dt_selected');
}

function fme_layered_clearall_listener(evt)
{
    //var params = $('fme_layered_params').value.parseQuery();
    var queryString = localStorage.getItem('queryString');
    var params = queryString.parseQuery();
    $('fme_layered_params').value = 'clearall=true';
    localStorage.setItem('queryString', 'clearall=true');
    if (params['q'])
    {
        $('fme_layered_params').value += '&q=' + params['q'];
        localStorage.setItem('queryString', queryString+'&q=' + params['q']);
    }
    fme_layered_make_request();
    //location.reload();
    //Event.stop(evt);
}

function price_input_listener(evt) {
    if (evt.type == 'keypress' && 13 != evt.keyCode)
        return;

    if (evt.type == 'keypress') {
        var inpObj = Event.findElement(evt, 'INPUT');
    } else {
        var inpObj = Event.findElement(evt, 'BUTTON');
    }

    var sKey = inpObj.id.split('---')[1];
    var numFrom = roundPrice($('price_range_from---' + sKey).value),
            numTo = roundPrice($('price_range_to---' + sKey).value);

    if ((numFrom < 0.01 && numTo < 0.01) || numFrom < 0 || numTo < 0)
        return;

    fme_layered_add_params('p', 1, 1);
    fme_layered_add_params(sKey, numFrom + ',' + numTo, true);
    fme_layered_make_request();
}

function fme_layered_init()
{
    var items, i, j, n,
            classes = ['category', 'attribute', 'icon', 'price', 'clear', 'dt', 'clearall'];

    for (j = 0; j < classes.length; ++j) {
        items = $('fme_filters_list').select('.fme_layered_' + classes[j]);
        n = items.length;
        for (i = 0; i < n; ++i) {
            Event.observe(items[i], 'click', eval('fme_layered_' + classes[j] + '_listener'));
        }
    }

    items = $('fme_filters_list').select('.price-input');
    n = items.length;
    var btn = $('price_button_go');
    for (i = 0; i < n; ++i)
    {
        btn = $('price_button_go---' + items[i].value);
        if (Object.isElement(btn)) {
            Event.observe(btn, 'click', price_input_listener);
            Event.observe($('price_range_from---' + items[i].value), 'keypress', price_input_listener);
            Event.observe($('price_range_to---' + items[i].value), 'keypress', price_input_listener);
        }
    }
// finish new fix code    
}

function create_price_slider(width, from, to, min_price, max_price, sKey)
{
    var price_slider = $('fme_layered_price_slider' + sKey);

    return new Control.Slider(price_slider.select('.handle'), price_slider, {
        range: $R(0, width),
        sliderValue: [from, to],
        restricted: true,
        onChange: function(values) {
            var f = calculateSliderPrice(width, from, to, min_price, max_price, values[0]),
                    t = calculateSliderPrice(width, from, to, min_price, max_price, values[1]);

            fme_layered_add_params(sKey, f + ',' + t, true);

            $('price_range_from' + sKey).update(f);
            $('price_range_to' + sKey).update(t);

            fme_layered_make_request();
        },
        onSlide: function(values) {
            $('price_range_from' + sKey).update(calculateSliderPrice(width, from, to, min_price, max_price, values[0]));
            $('price_range_to' + sKey).update(calculateSliderPrice(width, from, to, min_price, max_price, values[1]));
        }
    });
}

function calculateSliderPrice(width, from, to, min_price, max_price, value)
{
    var calculated = roundPrice(((max_price - min_price) * value / width) + min_price);

    return calculated;
}
