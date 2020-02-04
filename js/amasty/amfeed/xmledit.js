/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Feed
*/
CodeMirror.defineSimpleMode("amasty_feed", {
  start: [
    {regex: /"(?:[^\\]|\\.)*?"/, token: "atom"},
    {regex: /(?:type|value|format|length|optional|parent)\b/,
     token: "string"},
    {regex: /attribute|custom_field|text|images|category/, token: "atom"},
    {regex: /\<!\[CDATA\[/, token: "amcdata", next: "amcdata"},
    {regex: /\</, token: "amtag", next: "amtag"},
    
    {regex: /[\{|%]/, token: "def"},
    {regex: /[\}|%]/,  token: "def"},
    
  ],
  amtag: [
    {regex: /.*?>/, token: "amtag", next: "start"},
    {regex: /.*/, token: "amtag"}
  ],
  amcdata: [
    {regex: /.*?]]>/, token: "amcdata", next: "start"},
    {regex: /.*/, token: "amcdata"}
  ]
});

var xmlEditor = {
    editor: null,
    header: null,
    footer: null,
    selectedRow: {},
    updateMode: false,
    navigator: {},
    buttons: {
        insert: null,
        update: null
    },
    updateBtn: null,
    clearSelectedRow: function(){
        this.updateMode = false;
        this.selectedRow = {
            tag: null,
            type: null,
            value: null,
            format: null,
            length: null,
            optional: null,
            parent: null
        }
    },
    refresh: function(){
        this.editor.refresh();
        this.editor.save();
        
        this.header.refresh();
        this.header.save();
        
        this.footer.refresh();
        this.footer.save();
    },
    init: function(){
        this.editor = CodeMirror.fromTextArea($('xml_body'), {
            lineNumbers: true,
            mode: 'amasty_feed',
            alignCDATA: true,
            lineNumbers    : false,
            viewportMargin : Infinity
        });
        
        this.header = CodeMirror.fromTextArea($('xml_header'), {
            lineNumbers: true,
            mode: 'amasty_feed',
            alignCDATA: true,
            lineNumbers    : false,
            viewportMargin : Infinity
        });
        
        this.footer = CodeMirror.fromTextArea($('xml_footer'), {
            lineNumbers: true,
            mode: 'amasty_feed',
            alignCDATA: true,
            lineNumbers    : false,
            viewportMargin : Infinity
        });
        
        this.editor.setSize(900, 400);
        this.header.setSize(900, 100);
        this.footer.setSize(900, 100);
        
        this.editor.on("cursorActivity", this.cursorActivity.bind(this));
        
        this.clearSelectedRow();
        this.initNavigator();
        this.initButtons();
        this.updateNavigator();
        
        setInterval(this.refresh.bind(this), 100);
    },
    initNavigator: function (){
        var container = $('xml_control_container');
        this.navigator = {
            xml_tag: container.down("#xml_tag"),
            insert_type: container.down("#insert_type"),
            
            insert_attr_attribute: container.down("#insert_attr_attribute"),
            insert_attr_custom_field: container.down("#insert_attr_custom_field"),
            insert_attr_category: container.down("#insert_attr_category"),
            insert_attr_text: container.down("#insert_attr_text"),
            insert_attr_images: container.down("#insert_attr_images"),
            
            insert_format: container.down("#insert_format"),
            insert_image_format: container.down("#insert_image_format"),
            
            insert_length: container.down("#insert_length"),
            insert_optional: container.down("#insert_optional"),
            use_parent: container.down("#use_parent")
        }
    },
    initButtons: function(){
       var container = $('xml_control_container'); 
       
       this.buttons.insert = container.down("#xml_insert_btn");
       this.buttons.update = container.down("#xml_update_btn");
       
       this.buttons.insert.observe('click', this.inserRow.bind(this));
       this.buttons.update.observe('click', this.updateRow.bind(this));
    },
    getXMLRowFormat: function(){
        var ret = "";
        switch(this.navigator.insert_type.value){
            case "images":
                ret = this.navigator.insert_image_format.value;
                break;
            default:
                ret = this.navigator.insert_format.value;
                break;
        }

        return ret;
    },
    updateRow: function(){
        var insertAttr = "insert_attr_" + this.navigator.insert_type.value;
        var originLine = this.editor.getLine(this.editor.getCursor().line);
        
        var line = originLine.replace(/(type)="(.*?)"/i, 'type="' + this.navigator.insert_type.value + '"');
        line = line.replace(/(value)="(.*?)"/i, 'value="' + this.navigator[insertAttr].value + '"');
        line = line.replace(/(format)="(.*?)"/i, 'format="' + this.getXMLRowFormat() + '"');
        line = line.replace(/(length)="(.*?)"/i, 'length="' + this.navigator.insert_length.value + '"');
        line = line.replace(/(optional)="(.*?)"/i, 'optional="' + this.navigator.insert_optional.value + '"');
        line = line.replace(/(parent)="(.*?)"/i, 'parent="' + this.navigator.use_parent.value + '"');
        
        line = line.replace('<' + this.selectedRow.tag + '>', '<' + this.navigator.xml_tag.value + '>');
        line = line.replace('</' + this.selectedRow.tag + '>', '</' + this.navigator.xml_tag.value + '>');
        
        this.editor.replaceRange(line, {
            line: this.editor.getCursor().line,
            ch: 0
        }, {
            line: this.editor.getCursor().line,
            ch: originLine.length
        });
        
        
    },
    inserRow: function(){
        
        var insertAttr = "insert_attr_" + this.navigator.insert_type.value;
        
    
        var tpl = '{type=":insert_type" value=":value" format=":insert_format" length=":insert_length" optional=":insert_optional" parent=":parent"}';

        var repl = {
            ':xml_tag': this.navigator.xml_tag.value,
            ':insert_type': this.navigator.insert_type.value,
            ':value': this.navigator[insertAttr].value,
            ':insert_format': this.getXMLRowFormat(),
            ':insert_length': this.navigator.insert_length.value,
            ':insert_optional': this.navigator.insert_optional.value,
            ':parent': this.navigator.use_parent.value
        };
        
        $H(repl).each(function(item){
            tpl = tpl.replace(eval('/' + item.key + '/g'), item.value);
        });
        
        if (this.navigator.xml_tag.value){
            tpl = "<" + this.navigator.xml_tag.value + ">" + tpl + "</" + this.navigator.xml_tag.value + ">\n";
        }
        
        this.editor.replaceSelection(tpl);
    },
    cursorActivity: function(){
        this.clearSelectedRow();
        
        var line = this.editor.getLine(this.editor.getCursor().line);
        
        var tagMatch = line.match(/<([^>]+)>(.*?)<\/\1>/);
        
        if (tagMatch && tagMatch.length == 3){
            
            this.selectedRow.tag = tagMatch[1];

            this.updateMode = true;
        }
        
        var varsRe = /(type|value|format|length|optional|parent)="(.*?)"/g
        var varsArr;

        while ((varsArr = varsRe.exec(line)) != null) {
            if (varsArr && varsArr.length == 3){
                if (this.selectedRow[varsArr[1]] !== undefined){
                    this.selectedRow[varsArr[1]] = varsArr[2];
                }
                this.updateMode = true;
            }
        }
        
        this.updateNavigator();
    },
    updateNavigator: function(){
        this.setValue(this.navigator.xml_tag, this.selectedRow.tag);
        this.setValue(this.navigator.insert_type, this.selectedRow.type);
        this.setValue(this.navigator.insert_length, this.selectedRow.length);
        this.setValue(this.navigator.insert_optional, this.selectedRow.optional);
        this.setValue(this.navigator.use_parent, this.selectedRow.parent);
        
        switch(this.selectedRow.type){
            case "attribute":
                this.setValue(this.navigator.insert_attr_attribute, this.selectedRow.value);
                this.setValue(this.navigator.insert_format, this.selectedRow.format);
            break;
            case "custom_field":
                this.setValue(this.navigator.insert_attr_custom_field, this.selectedRow.value);
                this.setValue(this.navigator.insert_format, this.selectedRow.format);
            break;
            case "category":
                this.setValue(this.navigator.insert_attr_category, this.selectedRow.value);
                this.setValue(this.navigator.insert_format, this.selectedRow.format);
            break;
            case "text":
                this.setValue(this.navigator.insert_attr_text, this.selectedRow.value);
                this.setValue(this.navigator.insert_format, this.selectedRow.format);
            break;
            case "images":
                this.setValue(this.navigator.insert_attr_images, this.selectedRow.value);
                this.setValue(this.navigator.insert_image_format, this.selectedRow.format);
            break;
        }
        
        this.disableCol('xml_tag_col', this.selectedRow.tag);
        this.disableCol('xml_insert_type', this.selectedRow.value);
        this.disableCol('attribute_values', this.selectedRow.value);
        this.disableCol('xml_image_format', this.selectedRow.format);
        this.disableCol('xml_insert_length', this.selectedRow.length);
        this.disableCol('xml_insert_optional', this.selectedRow.optional);
        this.disableCol('xml_use_parent', this.selectedRow.parent);
        
        if (this.updateMode){
            this.buttons.update.show();
            this.buttons.insert.hide();
        } else {
            this.buttons.update.hide();
            this.buttons.insert.show();
        }
        
    },
    disableCol: function(colId, value){
        var container = $('xml_control_container');
        
        if (value === null && this.updateMode){
            container.select('#' + colId + ' input, #' + colId + " select").each(function(el){
                el.disabled = true;
            });
        } else {
            container.select('#' + colId + ' input, #' + colId + " select").each(function(el){
                el.disabled = false;
            });
        }  
    },
    setValue: function(input, value){
        if (value !== null) {
            input.setValue(value)
        }
    }
    
}