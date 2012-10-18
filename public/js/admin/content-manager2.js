/**
 * Main Javascript file for the content manager.  This file is required for
 * the admin side of the content manager and is pulled in through the admin
 * layout template.
 *
 * @type {Object}
 */
function RcmEdit(config) {

    /**
     * Always refers to this object unlike the 'this' JS variable;
     *
     * @type {RcmEdit}
     */
    var me = this;

    /**
     * Set the config items.  Must be passed in when creating your RCM Edit
     * Object.
     *
     * @type {String}
     */
    me.config = config;


    /***********************/
    /*       Setters       */
    /*   Use these to set  */
    /*      Properties     */
    /***********************/

    /**
     * Set the current page to be used for redirects and JSON queries
     *
     * @param page
     */
    me.setPage = function(page) {
        me.page = page;
    };

    /**
     * Set the current page revision.
     *
     * @param pageRevision
     */
    me.setPageRevision = function(pageRevision) {
        me.pageRevision = pageRevision;
    };

    /**
     * Set the language to be used for redirectes and JSON queries
     *
     * @param language
     */
    me.setLanguage = function(language) {
        me.language = language;
    };

    /**
     * Set the begining new instance counter
     *
     * $param newInstanceId
     */
    me.setNewInstanceId = function(newInstanceId) {
        me.newInstanceID = newInstanceId;
    };

    /**
     * Set the editor to use for the Content Manager
     * @param editor
     */
    me.setEditor = function (editor) {

        if (typeof(editor.init) !== 'function') {
            console.error('No Editor Strategy Passed In.  Please Setup Your' +
                'Applications config file acording to the docs.');

            return;
        }

        me.editor = editor;
    };

    /**
     * Set the route to get a new plugin instance.  Must come out of ZF2 or
     * manually when setting up the object.
     *
     * @param path
     */
    me.setNewPluginInstanceAjaxPath = function (path) {
        me.newPluginInstanceAjaxPath = path;
    };


    /*************************/
    /*        Actions        */
    /*************************/

    /**
     * Setup the admin environment
     */
    me.init = function() {

        if (typeof(me.editor.init) !== 'function') {
            console.error('No Editor Strategy Passed In.  Please Setup Your' +
                'Applications config file acording to the docs.');

            return;
        }

        me.editor.init();
        me.ui.init();
        me.layoutEditor.init();
    };

    /**
     * Starts The Edit Mode
     */
    me.switchToEditMode = function(){
        $('html').addClass('rcmEditingPlugins');
        me.ui.initEditMode();
        me.editor.initEditMode();
        me.rcmPlugins.initEditMode();
        me.editMode = true;
    };

    /**
     * Cancel Edit Mode
     */
    me.cancelEditMode = function() {
        if (me.editMode === false) {
            return;
        }

        me.ui.showPleaseWaitInEasyNav();

        location.reload();
    };

    /**
     * Save the current page
     */
    me.savePage = function() {
        if (me.editMode === false) {
            return;
        }

        me.ui.showPleaseWaitInEasyNav();
        var containerInfo = me.rcmPlugins.GetAllInstancesAndOrder();
        var pageMetaData = me.savePageProperties();

        var pluginData = me.rcmPlugins.savePlugins();

        var dataToSave = $.extend(true, pluginData, pageMetaData, containerInfo);

        var dataToSend = JSON.stringify(dataToSave);

        var input = $('<input type="hidden" ' +
            'name="saveData" value="" />').val(dataToSend);

        var form = $('<form method="post" action="/rcm-admin-save/' +
            this.page+'/'+this.language+'/'+this.pageRevision+'" name="rcmDataForm" id="rcmDataForm">').append(input);

        $("body").append(form);

        $("#rcmDataForm").submit();
    };

    /**
     * Gets all params from the url query string
     * To get your params
     * <code>
     *     var params = object.getUrlParams();
     *     params.myparm
     * </code>
     *
     * @return {Object}
     */
    me.getUrlParams = function(){
        var params = {};

        if (location.search) {
            var parts = location.search.substring(1).split('&');

            for (var i = 0; i < parts.length; i++) {
                var nv = parts[i].split('=');
                if (!nv[0]) continue;
                params[nv[0]] = nv[1] || true;
            }
        }
        return params;
    };

    /**
     * Get a new instance ID
     *
     * @return {Number}
     */
    me.getNewInstanceId = function() {
        var newInstanceId = me.newInstanceID;
        me.newInstanceId--;
        return newInstanceId;
    };



    /*******************************/
    /*      Needed Parameters      */
    /*  Do Not set these directly  */
    /*  Use setters when creating  */
    /*     Your RCM Edit object    */
    /*******************************/

    /**
     * Current Page or Template
     *
     * @type {Object}
     */
    me.page = {};

    /**
     * Current Page Revision
     *
     * @type {Number}
     */
    me.pageRevision = 0;

    /**
     * Current Language - ISO 3 digit language var.  Defaults to English
     *
     * @type {String}
     */
    me.language = 'eng';

    /**
     * New Instance ID Counter
     *
     * @type {Number}
     */
    me.newInstanceID = 0;

    /**
     * Editor Object to use - An editor strategy must be passed in.  Current
     * strategies can be found in developers guide and generally set
     * via a config file.
     *
     * @type {Object}
     */
    me.editor = {};

    /**
     * Route to get a new plugin instance.
     *
     * @type {String}
     */
    me.newPluginInstanceAjaxPath = '';


    /**************************/
    /*   Private Properties   */
    /*   Do not edit these    */
    /**************************/

    /**
     * User Interface Object.  To help organize and categorize UI functions
     * @type {Object}
     */
    me.ui = {};

    /**
     * Plugin Object.  To help organize and categorize Plugin functions and
     * properties
     *
     * @type {Object}
     */
    me.rcmPlugins = {};

    /**
     * Layout Editor Object.  To help organize and categorize Layout Editor
     * functions and properties.
     *
     * @type {Object}
     */
    me.layoutEditor = {};

    /**
     * List of currently active called plugins.  Keeps track of what plugins
     * have been called and stores the edit objects needed for saving the
     * called plugin.
     *
     * @type {Array}
     */
    me.rcmPlugins.calledPlugins = [];

    /**
     * List of currently active Editors.  Keeps track of what plugins
     * have been called and stores the edits needed for saving the
     * called plugin.
     *
     * @type {Array}
     */
    me.rcmPlugins.activeEditors = [];

    /**
     * Flag for edit mode.
     * @type {Boolean}
     */
    me.editMode = false;

    /**
     * Flag for Site Wide edit mode.
     * @type {Boolean}
     */
    me.siteWidesEditable = false;



    /********************************/
    /*        Page Properties       */
    /********************************/

    /**
     * Use this to show and edit the Page Properties
     */
    me.showPageProperties = function() {

        if (me.editMode == false) {
            me.switchToEditMode();
        }

        var pageTitle = $('title');
        var pageKeywords = $('meta[name="keywords"]');
        var pageDesc = $('meta[name="description"]');

        //Show the dialog
        var form = $('<form>')
            .addInput('metaTitle', 'Page Title', $(pageTitle).html())
            .addInput('metaDesc',  'Page Description', $(pageDesc).attr('content'))
            .addInput('metaKeywords',  'Keywords', $(pageKeywords).attr('content'))
            .dialog({
                title:'Properties',
                modal:true,
                width:620,
                buttons:{
                    Cancel:function () {

                        $(this).dialog("close");
                    },
                    Ok:function () {

                        //Grab the non-jquery form so we can get its fields
                        var domForm = form.get(0);

                        $('title').html(domForm.metaTitle.value);
                        $('meta[name="description"]').attr('content', domForm.metaDesc.value);
                        $('meta[name="keywords"]').attr('content', domForm.metaKeywords.value);

                        $(this).dialog("close");
                    }
                }
        });
    };

    me.savePageProperties = function() {
        return  {
            main : {
                metaTitle : $("title").html(),
                metaDesc  : $('meta[name="description"]').attr('content'),
                metaKeyWords : $('meta[name="keywords"]').attr('content')
            }
        };
    };


    /********************************/
    /*        User Interface        */
    /********************************/

    /**
     * initial init method.  Used when the main init method is fired.
     */
    me.ui.init = function() {
        me.ui.addEditButtonClickEvent();
        me.ui.addEditMenuLinkClickEvent();
        me.ui.addEditSiteWideMenuLinkClickEvent();
        me.ui.addPagePropertiesMenuLinkClickEvent();
        me.ui.addEditLayoutMenuLinkClickEvent();
    };

    /**
     * Prep the ui for edit mode.
     */
    me.ui.initEditMode = function() {
        me.ui.disablePageLinks();
        me.ui.switchEasyEditNavButtons();
        me.ui.disableLink($(".rcmEditPage"));

    };

    /**
     * Add Clink Binds to the Edit Buttons
     */
    me.ui.addEditButtonClickEvent = function() {
        $(".rcmEditButton").click(function(e){
            me.switchToEditMode();
            e.preventDefault();
        });
    };

    /**
     * Add Click Binds to Edit Nav Link
     */
    me.ui.addEditMenuLinkClickEvent = function() {
        $(".rcmEditPage").click(function(e){
            me.switchToEditMode();
            e.preventDefault();
        });
    };

    /**
     * Add Click Binds to Edit Site Wide Nav Link
     */
    me.ui.addEditSiteWideMenuLinkClickEvent = function() {
        $(".rcmEditSiteWide").click(function(){
            me.rcmPlugins.initSiteWidePlugins();
        });
    };

    /**
     *  Add Click Binds to Page Properties
     */
    me.ui.addPagePropertiesMenuLinkClickEvent = function() {
        $(".rcmPageProperties").click(function(){
            me.showPageProperties();
        })
    };

    /**
     *  Add Click Binds to Layout Editor
     */
    me.ui.addEditLayoutMenuLinkClickEvent = function() {
        $(".rcmShowLayoutEditor").click(function(){
            me.layoutEditor.startLayoutEditor();
        })
    };

    /**
     * Switch the easy edit nav buttons for edit mode
     */
    me.ui.switchEasyEditNavButtons = function() {

        if (me.editMode === true) {
            return;
        }

        $("#rcmAdminToolbarSaveCancel").show();
        $("#rcmAdminToolbarEdit").hide();

        $(".rcmCancelButton").click(function(){
            me.cancelEditMode();
        });

        $(".rcmSaveButton").click(function(){
            me.savePage();
        });
    };

    /**
     * Show Please Wait in Easy Nav
     */
    me.ui.showPleaseWaitInEasyNav = function() {
        $("#rcmAdminToolbarEdit").hide();
        $("#rcmAdminToolbarSaveCancel").hide();
        $("#rcmAdminToolbarPleaseWait").show();
    };

    /**
     * Disable all page links so you can't accidentally navigate to another
     * pate while editing.
     */
    me.ui.disablePageLinks = function() {
        $("#RcmRealPage").find("a").unbind('click').click(function(e){
            e.preventDefault();
        });
    };

    /**
     * Disable a link in the admin menu
     *
     * @param container Container to disable
     */
    me.ui.disableLink = function (container) {
        $(container).unbind('click').click(
            function(){
                $(this).preventDefault();
                return false;
            }
        ).css('color', '#DCDCDC');

        $(container).find("a").css('color', '#DCDCDC');

    };

    /**
     * Add an overlay to disable clicks and edit regions
     *
     * @param container
     */
    me.ui.addOverlay = function (container) {
        var divHeight = me.ui.getElementHeight(container);
        var divWidth = me.ui.getElementWidth(container);

        if (divHeight == 0) {
            return;
        }

        var newDiv = $('<div style="position: absolute; top: 0; left: 0; z-index: 10000;"></div>');
        $(newDiv).height(divHeight);
        $(newDiv).width(divWidth);
        $(newDiv).addClass('rcmLockOverlay');

        $(container).css('position', 'relative').append(newDiv);

    };

    /**
     * Remove overlays on a container
     *
     * @param container
     */
    me.ui.removeOverlay = function (container) {
        $(container).find(".rcmLockOverlay").remove();
    };

    /**
     * Get the correct Height of an element.
     *
     * @param container
     * @return {*|jQuery}
     */
    me.ui.getElementHeight = function(container) {
        var elementToUse = container;

        var loopCounter = 0;

        while($(elementToUse).height() == 0 && loopCounter < 10) {
            elementToUse = $(elementToUse).parent();
            loopCounter++;
        }

        return $(elementToUse).height();
    };

    /**
     * Get the correct Width of an element
     *
     * @param container
     * @return {*|jQuery}
     */
    me.ui.getElementWidth = function(container) {
        var elementToUse = container;

        var loopCounter = 0;

        while($(elementToUse).width() == 0 && loopCounter < 10) {
            elementToUse = $(container).parent();
            loopCounter++;
        }

        return $(elementToUse).width();
    };

    /**
     * Add an Unlock option to the right click menu for locked plugins.
     */
    me.ui.addUnlockRightClick = function() {
        $.contextMenu({
            selector: '.rcmLockOverlay',

            //Here are the right click menu options
            items:{
                unlockMe:{
                    name:'Unlock',
                    icon:'delete',
                    callback:function (action, el, pos) {
                        var container = $(this);
                        var isSiteWide = $(container).parent().attr('data-rcmSiteWidePlugin');

                        if (isSiteWide == 'Y') {
                            me.rcmPlugins.initSiteWidePlugins()
                        } else {
                            me.switchToEditMode();
                        }

                    }
                }
            }
        });
    };

    /***********************/
    /*     RCM Plugins     */
    /***********************/

    /**
     * Initiate the edit mode for all plugins.
     */
    me.rcmPlugins.initEditMode = function() {
        me.rcmPlugins.initAllPagePlugins();

    };

    /**
     * Init all Page plugins for edit mode.  This is the default starting
     * mode of the editor.  Layout manager and Site wide plugins will
     * start out disabled.
     */
    me.rcmPlugins.initAllPagePlugins = function() {
        $("#RcmRealPage").find(".rcmPlugin").each(function(){

            var containerData = me.rcmPlugins.getPluginContainerInfo(this);

            if (containerData.isSiteWide == 'Y') {
                if (me.editMode != true) {
                    me.rcmPlugins.lockPlugin(this);
                }

                return;
            }

            me.rcmPlugins.initPluginEditMode(this);
        });

        me.ui.addUnlockRightClick();
        me.editMode = true;
    };

    me.rcmPlugins.GetAllInstancesAndOrder = function() {
        var dataToReturn = {};

        $(".rcmContainer").each(function(){
            var containerNumber = $(this).attr('data-containerId');
            $(this).find(".rcmPlugin").each(function(index, value){
                var instanceId = $(value).attr('data-rcmPluginInstanceId');
                var pluginName = $(value).attr('data-rcmPluginName');
                dataToReturn[instanceId] = {
                    'container' : containerNumber,
                    'order' : index,
                    'pluginName' : pluginName
                }
            })
        });

        return dataToReturn;
    };

    /**
     * Init all Site Wide plugins for edit mode.
     */
    me.rcmPlugins.initSiteWidePlugins = function() {

        var editButton = this;

        $().confirm(
            'Please note:  Any changes you make to a site wide plugin will be published and made live when you save your changes.',
            function() {
                me.ui.switchEasyEditNavButtons();
                me.rcmPlugins.preformUnlockSiteWide();
                me.ui.disableLink(editButton);
            }
        );

    };

    /**
     * Unlock Site Wide Plugins
     */
    me.rcmPlugins.preformUnlockSiteWide = function() {
        $("#RcmRealPage").find(".rcmPlugin").each(function(){

            var containerData = me.rcmPlugins.getPluginContainerInfo(this);

            if (containerData.isSiteWide != 'Y') {
                if (me.editMode != true) {
                    me.rcmPlugins.lockPlugin(this);
                }

                return;
            }
            me.rcmPlugins.initPluginEditMode(this);
        });

        me.ui.addUnlockRightClick();
        me.editMode = true;
    };

    me.rcmPlugins.savePlugins = function() {
        var dataToReturn = {};

        var pluginData = me.rcmPlugins.getSaveDataFromCalledPlugins();
        var pluginEdits = me.rcmPlugins.getSaveDataFromPluginEdits();

        dataToReturn = $.extend(true, pluginEdits, pluginData);

        return dataToReturn;
    };

    /**
     * Initiate Plugin editor
     *
     * @param container
     */
    me.rcmPlugins.initPluginEditMode = function(container) {
        me.rcmPlugins.callPluginEditInit(container);
        me.rcmPlugins.initPluginRichEdits(container);
        me.rcmPlugins.initHtml5Edits(container);
        me.rcmPlugins.unlockPlugin(container);
    };



    /**
     * Disable edit regions of a container
     *
     * @param container
     */
    me.rcmPlugins.lockPlugin = function(container) {
        me.ui.addOverlay(container);
        $(container).fadeTo(500, 0.2);
    };

    /**
     * Unlock a plugin.  Removes Overlay and fade
     *
     * @param container
     */
    me.rcmPlugins.unlockPlugin = function(container) {
        me.ui.removeOverlay(container);
        $(container).fadeTo(500, 1);
    };

    /**
     * Call a plugin based on the jquery object passed it.
     *
     * @param pluginContainer rcmPluginContainer
     */
    me.rcmPlugins.callPluginEditInit = function(pluginContainer) {

        var containerData = me.rcmPlugins.getPluginContainerInfo(pluginContainer);

        if(typeof(window[containerData.editClass])=='function'){

            try {
                var plugin = new window[containerData.editClass](
                    containerData.instanceId,
                    $(pluginContainer)
                );

                plugin.initEdit();

                me.rcmPlugins.calledPlugins.push({
                    pluginObject : plugin,
                    instanceId   : containerData.instanceId,
                    pluginName   : containerData.pluginName
                });
            } catch (err) {
                console.error(err);
            }
        }
    };

    /**
     * Get Plugin Save Data.
     *
     * @return {Array}
     */
    me.rcmPlugins.getSaveDataFromCalledPlugins = function() {
        var dataToReturn = {};

        $.each(me.rcmPlugins.calledPlugins, function(index, value){

            if (!me.rcmPlugins.calledPlugins.hasOwnProperty(index)) {
                return;
            }

            var instanceId = me.rcmPlugins.calledPlugins[index].instanceId;
            var pluginObject = me.rcmPlugins.calledPlugins[index].pluginObject;

            dataToReturn[instanceId] = {
                pluginData : {}
            };

            if ($.isFunction(pluginObject.getSaveData)) {
                dataToReturn[instanceId].pluginData = pluginObject.getSaveData();
            }

            if ($.isFunction(pluginObject.getAssets)) {
                dataToReturn[instanceId].pluginData.assets =  pluginObject.getAssets();
            }

        });

        return dataToReturn;
    };

    /**
     * Find and initialize the Rich edits within the plugin passed.
     *
     * @param pluginContainer
     */
    me.rcmPlugins.initPluginRichEdits = function(pluginContainer) {

        var containerData = me.rcmPlugins.getPluginContainerInfo(pluginContainer);

        $(pluginContainer).find("[data-richEdit]").each(function() {

            var textAreaId = $(this).attr('data-richEdit');

            if(textAreaId == undefined || textAreaId == '') {
                return;
            }

            var newTextAreaId = 'rcm_richEdit_'
                +containerData.pluginName
                +'_'
                +containerData.instanceId
                +'_'
                +textAreaId;

            var newEditor = me.editor.addRichEditor(this, newTextAreaId);

            me.rcmPlugins.activeEditors.push({
                editor : newEditor,
                instanceId   : containerData.instanceId,
                textId       : textAreaId,
                pluginName   : containerData.pluginName,
                type         : 'rich'
            });
        });
    };

    /**
     * Find and initialize the HTML5 edits with the plugin passed
     * @param pluginContainer
     */
    me.rcmPlugins.initHtml5Edits = function(pluginContainer) {

        var containerData = me.rcmPlugins.getPluginContainerInfo(pluginContainer);

        $(pluginContainer).find('[data-textEdit]').each(function() {

            var textAreaId = $(this).attr('data-textEdit');

            var newEditor = me.editor.addHtml5Editor(this, textAreaId);

            me.rcmPlugins.activeEditors.push({
                editor : newEditor,
                instanceId   : containerData.instanceId,
                textId       : textAreaId,
                pluginName   : containerData.pluginName,
                type         : 'html5'
            });
        });
    };

    /**
     * Get Plugin Save Data.
     *
     * @return {Array}
     */
    me.rcmPlugins.getSaveDataFromPluginEdits = function() {
        var dataToReturn = {};

        $.each(me.rcmPlugins.activeEditors, function(index, value) {
            if (!me.rcmPlugins.activeEditors.hasOwnProperty(index)) {
                return;
            }

            var instanceId = me.rcmPlugins.activeEditors[index].instanceId;
            var textId = me.rcmPlugins.activeEditors[index].textId;


            if (me.rcmPlugins.activeEditors[index].type == 'rich') {
                var saveData = me.editor.getRichEditorData(
                    me.rcmPlugins.activeEditors[index].editor
                );
            } else {
                var saveData = me.editor.getHtml5EditorData(
                    me.rcmPlugins.activeEditors[index].editor
                );
            }

            //Setup the data to return

            if (dataToReturn[instanceId] == undefined) {
                dataToReturn[instanceId] = {
                    pluginName : '',
                    pluginData : {
                        assets : []
                    }
                };
            }

            dataToReturn[instanceId].pluginName = me.rcmPlugins.activeEditors[index].pluginName;
            dataToReturn[instanceId].pluginData[textId] = saveData.html;
            dataToReturn[instanceId].pluginData.assets = saveData.assets;

        });

        return dataToReturn;
    };

    /**
     * Get the container info
     *
     * @param pluginContainer
     */
    me.rcmPlugins.getPluginContainerInfo = function(pluginContainer) {
        var containerData = {};

        containerData.pluginName = $(pluginContainer).attr('data-rcmPluginName');
        containerData.isSiteWide = $(pluginContainer).attr('data-rcmSiteWidePlugin');
        containerData.instanceId = $(pluginContainer).attr('data-rcmPluginInstanceId');
        containerData.displayName = $(pluginContainer).attr('data-rcmPluginDisplayName').replace(/\s/g, '-');
        containerData.editClass = containerData.pluginName + 'Edit';

        return containerData;
    };

    /*************************/
    /*     Layout Editor     */
    /*************************/

    /**
     * Initiate the layout editor
     */
    me.layoutEditor.init = function() {
        me.layoutEditor.checkUrlAndShow();
        me.layoutEditor.addLayoutPopOut();
        me.layoutEditor.addLayoutClose();
    };

    /**
     * Check URL to see if we should show the layout editor
     */
    me.layoutEditor.checkUrlAndShow = function() {
        var myParams = me.getUrlParams();

        if (myParams == undefined || myParams.rcmShowLayoutEditor == undefined) {
            return;
        }

        if(myParams.rcmShowLayoutEditor == 'Y') {
            me.layoutEditor.startLayoutEditor();
        }
    };

    /**
     * Show the layout editor
     */
    me.layoutEditor.startLayoutEditor = function() {
        if (me.editMode == false) {
            me.switchToEditMode();
        }

        $("#rcmLayoutEditorColumn").show('slide').resizable({
            handles: "e"
        });

        //Add Menu Accordian
        $( "#rcmLayoutAccordion" ).accordion({ active: 22, collapsible: true });

        me.layoutEditor.makePluginsDraggable();

        me.layoutEditor.makePluginsSortable();
    };

    /**
     * Closes the layout editor and returns the edit mode back to normal
     */
    me.layoutEditor.stopLayoutEditor = function() {
        var rcmLayoutEditorColumn = $("#rcmLayoutEditorColumn");
        $( "#rcmLayoutAccordion" ).accordion("destroy");
        rcmLayoutEditorColumn.resizable("destroy");
        rcmLayoutEditorColumn.hide('slide');
        me.layoutEditor.stopPluginsSortable();
        me.layoutEditor.stopPluginsDraggable();
    };

    /**
     * Add popout click event to icon
     */
    me.layoutEditor.addLayoutPopOut = function() {
        var rcmLayoutMenuPopOut = $("#rcmLayoutMenuPopout");

        rcmLayoutMenuPopOut.removeAttr('style');
        rcmLayoutMenuPopOut.click(function() {
            me.layoutEditor.popOutLayoutEditor();
        });
    };

    /**
     * Add Close Button Clicks
     */
    me.layoutEditor.addLayoutClose = function() {
        $("#rcmLayoutMenuClose").click(function(){
            me.layoutEditor.stopLayoutEditor();
        })
    };

    /**
     * Pop out the layout editor
     */
    me.layoutEditor.popOutLayoutEditor = function() {
        var layoutEditorColumn = $("#rcmLayoutEditorColumn");
        var rcmLayoutMenuPopOut = $("#rcmLayoutMenuPopout");

        layoutEditorColumn.resizable("destroy");
        layoutEditorColumn.appendTo("body");
        layoutEditorColumn.css('position', 'fixed');
        layoutEditorColumn.css('top', 0);
        layoutEditorColumn.css('left', 0);
        layoutEditorColumn.height($( "#rcmLayoutAccordion").outerHeight()+50);
        layoutEditorColumn.zIndex(9999999999);
        layoutEditorColumn.draggable();
        layoutEditorColumn.show().resizable({
            handles: "all"
        });

        rcmLayoutMenuPopOut.css('background-position', '-242px -34px');
        rcmLayoutMenuPopOut.click(function(){
            me.layoutEditor.pinLayoutEditor();
        });
    };

    /**
     * Pin the layout editor to the left side of the page
     */
    me.layoutEditor.pinLayoutEditor = function() {

        var layoutEditorColumn = $("#rcmLayoutEditorColumn");

        layoutEditorColumn.resizable("destroy");
        layoutEditorColumn.removeAttr('style');
        layoutEditorColumn.draggable("destroy");
        layoutEditorColumn.appendTo("#rcmLayoutEditorContainer");
        layoutEditorColumn.show().resizable({
            handles: "e"
        });

        me.layoutEditor.addLayoutPopOut();
    };

    /**
     * Make plugins in the layout editor menu draggable
     */
    me.layoutEditor.makePluginsDraggable = function() {
        $("#rcmLayoutAccordion").find(".rcmPluginDrag").each(function(v, e){
            $(e).draggable({
                cursorAt : {left:40, top : 10},
                helper: function(){
                    return me.layoutEditor.pluginDraggableHelper(this)
                },

                drag:function(){
                    me.layoutEditor.pluginDraggableDrag(this);
                },
                revert: 'invalid',
                connectToSortable: '.rcmContainer',
                appendTo: 'body'
            });
        });
    };

    /**
     * Disable dragging on plugins
     */
    me.layoutEditor.stopPluginsDraggable = function() {
        $("#rcmLayoutAccordion").find(".rcmPluginDrag").each(function(v, e){
            $(e).draggable("destroy");
        });
    };

    /**
     * Callback for Draggable - Helper
     *
     * @param container
     * @return {*|jQuery|HTMLElement}
     */
    me.layoutEditor.pluginDraggableHelper = function(container) {
        var pluginContainer = $(container).find(".rcmPlugin");
        var containerData = me.rcmPlugins.getPluginContainerInfo(pluginContainer);

        if (containerData.isSiteWide != 'Y') {
            $(pluginContainer).attr(
                'data-rcmPluginInstanceId',
                $(pluginContainer).attr('data-rcmPluginInstanceId')*10
            );
        }
        var helper = $(pluginContainer).clone(false);

        //Get Ajax
        me.layoutEditor.pluginDraggableStart(helper, pluginContainer);

        me.layoutEditor.setHelperWidth(helper, pluginContainer);

        return $(helper);
    };

    /**
     * Callback for Draggable - Start.  Preforms Ajax Request for new
     * Plugin instance to add to page.
     */
    me.layoutEditor.pluginDraggableStart = function(helper, pluginContainer) {

        if ($(pluginContainer).html() != '') {
            return;
        }

        var pluginData = me.rcmPlugins.getPluginContainerInfo(pluginContainer);

        $.getJSON(
            me.newPluginInstanceAjaxPath+'/'+pluginData.pluginName,
            function(data) {
                me.layoutEditor.getInstanceSuccessCallback(data, helper, pluginContainer)
            }
        );
    };

    /**
     * Set the width for helper divs when dragging new plugins.  This
     * keeps plugins from spanning the entire page.
     *
     * @param helper
     * @param pluginContainer
     */
    me.layoutEditor.setHelperWidth = function(helper, pluginContainer) {
        var divWidth = me.ui.getElementWidth(pluginContainer);

        if (divWidth > 1000) {
            $(helper).width(350);
        } else {
            $(helper).width(divWidth);
        }
    };

    /**
     * Runs after a successful ajax request for a new plugin.
     *
     * @param data
     * @param helper
     * @param pluginContainer
     */
    me.layoutEditor.getInstanceSuccessCallback = function(data, helper, pluginContainer) {

        if (data.js != undefined && data.js != '') {
            me.layoutEditor.loadPluginJs(data.js);
        }

        $(helper).html(data.display);
        $(pluginContainer).html(data.display);

        me.layoutEditor.setHelperWidth(helper, pluginContainer);
    };

    /**
     * Load a plugins edit script.
     *
     * @param jsPath
     */
    me.layoutEditor.loadPluginJs = function (jsPath) {
        var scriptAlreadyLoadedCheck = $('script[src="'+jsPath+'"]');

        if (scriptAlreadyLoadedCheck.length < 1) {
            $.getScript(jsPath);
        }
    };

    /**
     * Callback for Draggable - Drag event
     */
    me.layoutEditor.pluginDraggableDrag = function(container) {
        /* This is required for adding items to an empty
         * sortable. the sortable "change" event handles
         * everything else.
         */
        var placeHolder = $('.rcmPluginSortPlaceHolder');

        /*
         * If placeholder exists and has not yet been filled with a plugin
         */
        if(placeHolder.length && !placeHolder.html().length){
            me.layoutEditor.pluginDragPlaceHolder($(container).find(".rcmPlugin"));
        }
    };

    /**
     * Fix for containers that have no current plugins.
     *
     * @param container
     */
    me.layoutEditor.pluginDragPlaceHolder = function(container){
        var placeHolder = $('.rcmPluginSortPlaceHolder');
        //If placeholder exists and has not yet been filled with a plugin
        if(placeHolder.length && !placeHolder.html().length){
            //Copy plugin css classes
            placeHolder.attr(
                'class',
                container.attr('class')
                    + ' rcmPluginSortPlaceHolder'
            );
            //Copy plugin html
            placeHolder.html(container.html());
        }
    };


    /**
     * Makes plugins sortable.
     */
    me.layoutEditor.makePluginsSortable = function() {
        $(".rcmContainer").each(function(v, e){
            $(e).sortable({
                items: '.rcmPlugin',
                connectWith: '.rcmContainer',
                dropOnEmpty: true,
                helper: "original",
                tolerance : 'pointer',
                placeholder: "rcmPluginSortPlaceHolder",
                forcePlaceholderSize: false,
                cursorAt : {left:40, top : 10},
                change: function(event, ui) {
                    me.layoutEditor.pluginSortableChange(ui);
                },
                receive: function(event, ui) {
                    me.layoutEditor.pluginSortableReceive(this, ui);
                },
                start: function(event, ui){
                    $('html').addClass('rcmDraggingPlugins');

                    /* Advise the editor that we are moving it's container */
                    var richEdit = $(ui.item).find('[data-richedit="html"]');

                    if (richEdit.length > 0) {
                        me.editor.startDrag(richEdit);
                    }


                },
                stop: function (event, ui){
                    $('html').removeClass('rcmDraggingPlugins');

                    /* Let the editor know that dragging has stopped */
                    me.editor.stopDrag(ui.item);
                },
                cancel: '[data-textedit]'
            });
        });
    };

    /**
     * Makes plugins sortable.
     */
    me.layoutEditor.stopPluginsSortable = function() {
        $(".rcmContainer").each(function(v, e){
            $(e).sortable("destroy");
        });
    };


    /**
     * Plugin Sortable Change event
     *
     * @param ui
     */
    me.layoutEditor.pluginSortableChange = function(ui) {
        var pluginDiv;
        var placeHolder = $('.rcmPluginSortPlaceHolder');

        if(placeHolder.length && !placeHolder.html().length){

            if(ui.item.hasClass('rcmPluginDrag')){
                pluginDiv = $(ui.item).find(".rcmPlugin");
            } else {
                pluginDiv = ui.item;
            }

            placeHolder.attr(
                'class',
                pluginDiv.attr('class') + ' rcmPluginSortPlaceHolder'
            );

            placeHolder.html(pluginDiv.html());
        }
    };

    /**
     * Tells the sortable objects what to do with a new plugin.
     *
     * @param container
     * @param ui
     */
    me.layoutEditor.pluginSortableReceive = function(container, ui) {
        //Get the current Item
        var newItem = $(container).data().sortable.currentItem;

        //Find the actual plugin instance
        var initialInstance = $(ui.item).find(".initialState");

        //Create a new element to insert once dropped
        var newDiv = $(initialInstance).find(".rcmPlugin").clone(false);

        var containerData = me.rcmPlugins.getPluginContainerInfo(newDiv);



        if ($(initialInstance).is('.initialState')) {
            $(newItem).replaceWith($(newDiv));

            if (containerData.isSiteWide == 'Y') {
                $('#'+containerData.displayName).hide();
            }

            $(newDiv).find("a").unbind('click').click(function(e){
                e.preventDefault();
            });

            me.rcmPlugins.initPluginEditMode(newDiv);

        }
    };
}