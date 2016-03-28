/**
 * Coobus
 *
 * @category    Coobus
 * @package     Coobus_Catalog
 */
var catalogTabs = new Class.create();

catalogTabs.prototype = {
    initialize : function(containerId, destElementId, activeTabId, itemLinkClass, prepend, shadowTabs){
        this.containerId    = containerId;
        this.destElementId  = destElementId;
        this.activeTab = null;
        this.itemLinkClass = itemLinkClass? itemLinkClass:'li a.tab-item-link';
        this.prepend = prepend;

        this.tabOnClick     = this.tabMouseClick.bindAsEventListener(this);
        this.pagerOnClick     = this.pagerMouseClick.bindAsEventListener(this);

        this.tabs = $$('#'+this.containerId+' '+this.itemLinkClass);

        this.hideAllTabsContent();
        for (var tab=0; tab<this.tabs.length; tab++) {
            Event.observe(this.tabs[tab],'click',this.tabOnClick);
            // move tab contents to destination element
            if($(this.destElementId)){
                var tabContentElement = $(this.getTabContentElementId(this.tabs[tab]));
                if(tabContentElement && tabContentElement.parentNode.id != this.destElementId){
                    $(this.destElementId).appendChild(tabContentElement);
                    tabContentElement.container = this;
                    tabContentElement.statusBar = this.tabs[tab];
                    tabContentElement.tabObject  = this.tabs[tab];
                    this.tabs[tab].contentMoved = true;
                    this.tabs[tab].container = this;
                    this.tabs[tab].show = function(){
                        this.container.showTabContent(this);
                    }
                    if(varienGlobalEvents){
                        varienGlobalEvents.fireEvent(this.prepend+'moveTab', {tab:this.tabs[tab]});
                    }
                }
            }
/*
            // this code is pretty slow in IE, so lets do it in tabs*.phtml
            // mark ajax tabs as not loaded
            if (Element.hasClassName($(this.tabs[tab].id), 'ajax')) {
                Element.addClassName($(this.tabs[tab].id), 'notloaded');
            }
*/
                        
            // bind shadow tabs
            if (this.tabs[tab].id && shadowTabs && shadowTabs[this.tabs[tab].id]) {
                this.tabs[tab].shadowTabs = shadowTabs[this.tabs[tab].id];
            }
        }

        this.displayFirst = activeTabId;
        Event.observe(window,'load',this.moveTabContentInDest.bind(this));
    },
    
    initializePagers : function(tab) {       
        for (var i=0; i<this.tabs.length; i++) {
        	if ( tab && this.tabs[i].id != tab ) continue;
        	
            var pagers = $$('#'+this.getTabContentElementId(this.tabs[i])+' .pager .pages li a');
            for (var idx=0; pagers && idx<pagers.length; idx++) {
            	Event.observe(pagers[idx],'click',this.pagerOnClick);
            	pagers[idx].tab = this.tabs[i].id;
            }
            this.tabs[i].pagers = pagers;
        }
    },
    
    setSkipDisplayFirstTab : function(){
        this.displayFirst = null;
    },

    moveTabContentInDest : function(){
        for(var tab=0; tab<this.tabs.length; tab++){
            if($(this.destElementId) &&  !this.tabs[tab].contentMoved){
                var tabContentElement = $(this.getTabContentElementId(this.tabs[tab]));
                if(tabContentElement && tabContentElement.parentNode.id != this.destElementId){
                    $(this.destElementId).appendChild(tabContentElement);
                    tabContentElement.container = this;
                    tabContentElement.statusBar = this.tabs[tab];
                    tabContentElement.tabObject  = this.tabs[tab];
                    this.tabs[tab].container = this;
                    this.tabs[tab].show = function(){
                        this.container.showTabContent(this);
                    }
                    if(varienGlobalEvents){
                        varienGlobalEvents.fireEvent(this.prepend+'moveTab', {tab:this.tabs[tab]});
                    }
                }
            }
        }
        if (this.displayFirst) {
            this.showTabContent($(this.displayFirst));
            this.displayFirst = null;
        }
    },

    getTabContentElementId : function(tab){
        if(tab){
            return tab.id+'_content';
        }
        return false;
    },
    
    getPagerContentElementId : function(pager){
        if(pager){
            return pager.tab+'_content';
        }
        return false;
    },

    tabMouseClick : function(event) {
        var tab = Event.findElement(event, 'a');

        // go directly to specified url or switch tab
        if ((tab.href.indexOf('#') != tab.href.length-1)
            && !(Element.hasClassName(tab, 'ajax'))
        ) {
            location.href = tab.href;
        }
        else {
            this.showTabContent(tab);
        }
        Event.stop(event);
    },

    pagerMouseClick : function(event) {
        var pager = Event.findElement(event, 'a');

        // go directly to specified url or switch tab
        if ((pager.href.indexOf('#') != pager.href.length-1)
            && !(Element.hasClassName(pager, 'ajax'))
        ) {
            location.href = pager.href;
        }
        else {
            this.showPagerContent(pager);
        }
        Event.stop(event);
    },
    
    hideAllTabsContent : function(){
        for(var tab in this.tabs){
            this.hideTabContent(this.tabs[tab]);
        }
    },

    // show tab, ready or not
    showTabContentImmediately : function(tab) {
        this.hideAllTabsContent();
        var tabContentElement = $(this.getTabContentElementId(tab));
        if (tabContentElement) {
            Element.show(tabContentElement);
            Element.addClassName(tab, 'active');
            // load shadow tabs, if any
            if (tab.shadowTabs && tab.shadowTabs.length) {
                for (var k in tab.shadowTabs) {
                    this.loadShadowTab($(tab.shadowTabs[k]));
                }
            }
            if (!Element.hasClassName(tab, 'ajax only')) {
                Element.removeClassName(tab, 'notloaded');
            }
            this.activeTab = tab;
        }
        this.initializePagers(tab.id);
        if (varienGlobalEvents) {
            varienGlobalEvents.fireEvent(this.prepend+'showTab', {tab:tab});
        }
    },

    // the lazy show tab method
    showTabContent : function(tab) {
        var tabContentElement = $(this.getTabContentElementId(tab));
        if (tabContentElement) {
            if (this.activeTab != tab) {
                if (varienGlobalEvents) {
                    if (varienGlobalEvents.fireEvent(this.prepend+'tabChangeBefore', $(this.getTabContentElementId(this.activeTab))).indexOf('cannotchange') != -1) {
                        return;
                    };
                }
            }
            // wait for ajax request, if defined
            var isAjax = Element.hasClassName(tab, 'ajax');
            var isEmpty = tabContentElement.innerHTML=='' && tab.href.indexOf('#')!=tab.href.length-1;
            var isNotLoaded = Element.hasClassName(tab, 'notloaded');

            if ( isAjax && (isEmpty || isNotLoaded) )
            {
                new Ajax.Request(tab.href, {
                    parameters: {form_key: FORM_KEY},
                    evalScripts: true,
                    onSuccess: function(transport) {
                        try {
                            if (transport.responseText.isJSON()) {
                                var response = transport.responseText.evalJSON()
                                if (response.error) {
                                    alert(response.message);
                                }
                                if(response.ajaxExpired && response.ajaxRedirect) {
                                    setLocation(response.ajaxRedirect);
                                }
                            } else {
                                $(tabContentElement.id).update(transport.responseText);
                                this.showTabContentImmediately(tab)
                            }
                        }
                        catch (e) {
                            $(tabContentElement.id).update(transport.responseText);
                            this.showTabContentImmediately(tab)
                        }
                    }.bind(this)
                });
            }
            else {
                this.showTabContentImmediately(tab);
            }
        }
    },

    // the lazy show tab method
    showPagerContent : function(pager) {
        var pagerContentElement = $(this.getPagerContentElementId(pager));
        if (pagerContentElement) {
            if (varienGlobalEvents) {
                varienGlobalEvents.fireEvent(this.prepend+'pagerBeforeShow', {pager: pager});
            }
            // wait for ajax request, if defined
            var isAjax = Element.hasClassName(pager, 'ajax');
            var isEmpty = pagerContentElement.innerHTML=='' && pager.href.indexOf('#')!=pager.href.length-1;
            /*@TODO Evitar o recarregamento de páginas já carregadas uma vez*/
            var isNotLoaded = Element.hasClassName(pager, 'notloaded');

            if ( isAjax && (isEmpty || isNotLoaded) )
            {
                new Ajax.Request(pager.href, {
                    parameters: {form_key: FORM_KEY},
                    evalScripts: true,
                    onSuccess: function(transport) {
                        try {
                            if (transport.responseText.isJSON()) {
                                var response = transport.responseText.evalJSON()
                                if (response.error) {
                                    alert(response.message);
                                }
                                if(response.ajaxExpired && response.ajaxRedirect) {
                                    setLocation(response.ajaxRedirect);
                                }
                            } else {
                                $(pagerContentElement.id).update(transport.responseText);
                                this.showPagerContentImmediately(pager)
                            }
                        }
                        catch (e) {
                            $(pagerContentElement.id).update(transport.responseText);
                            this.showPagerContentImmediately(pager)
                        }
                    }.bind(this)
                });
            }
            else {
                this.showPagerContentImmediately(pager);
            }
        }
    },
    
    // show tab, ready or not
    showPagerContentImmediately : function(pager) {
        this.hideAllTabsContent();
        var pagerContentElement = $(this.getPagerContentElementId(pager));
        if (pagerContentElement) {
            Element.show(pagerContentElement);
            Element.addClassName($(pager.tab), 'active');
            if (!Element.hasClassName($(pager.tab), 'ajax only')) {
                Element.removeClassName($(pager.tab), 'notloaded');
            }
            this.activeTab = pager.tab;
        }
        this.initializePagers(pager.tab);
        if (varienGlobalEvents) {
            varienGlobalEvents.fireEvent(this.prepend+'showPager', {pager:pager});
        }
    },
    
    loadShadowTab : function(tab) {
        var tabContentElement = $(this.getTabContentElementId(tab));
        if (tabContentElement && Element.hasClassName(tab, 'ajax') && Element.hasClassName(tab, 'notloaded')) {
            new Ajax.Request(tab.href, {
                parameters: {form_key: FORM_KEY},
                evalScripts: true,
                onSuccess: function(transport) {
                    try {
                        if (transport.responseText.isJSON()) {
                            var response = transport.responseText.evalJSON()
                            if (response.error) {
                                alert(response.message);
                            }
                            if(response.ajaxExpired && response.ajaxRedirect) {
                                setLocation(response.ajaxRedirect);
                            }
                        } else {
                            $(tabContentElement.id).update(transport.responseText);
                            if (!Element.hasClassName(tab, 'ajax only')) {
                                Element.removeClassName(tab, 'notloaded');
                            }
                        }
                    }
                    catch (e) {
                        $(tabContentElement.id).update(transport.responseText);
                        if (!Element.hasClassName(tab, 'ajax only')) {
                            Element.removeClassName(tab, 'notloaded');
                        }
                    }
                }.bind(this)
            });
        }
    },

    hideTabContent : function(tab){
        var tabContentElement = $(this.getTabContentElementId(tab));
        if($(this.destElementId) && tabContentElement){
           Element.hide(tabContentElement);
           Element.removeClassName(tab, 'active');
        }
        if(varienGlobalEvents){
            varienGlobalEvents.fireEvent(this.prepend+'hideTab', {tab:tab});
        }
    }
}
