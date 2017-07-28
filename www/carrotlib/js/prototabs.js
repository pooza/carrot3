/**
 * Prototabs.jsを、carrotむけに拡張したもの
 *
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

/*  Prototabs
 *  (c) 2007 James Starmer
 *
 *  Prototabs is freely distributable under the terms of an MIT-style license.
 *  For details, see the web site: http://www.jamesstarmer.com/prototabs
 *
/*--------------------------------------------------------------------------*/

var ProtoTabs = Class.create();
ProtoTabs.prototype = {
  initialize: function(element, options) {
    this.options = Object.extend({
      defaultPanel: '',
      ajaxUrls: {},
      evalScripts: false,
      ajaxLoadingText: 'Loading...'
    }, options || {});

    this.currentTab = '';
    this.element = $(element);
    this.listElements = $A(this.element.getElementsByTagName('li'));

    for (var i = 0; i < this.listElements.length; i++) {
      var tab = this.listElements[i];
      var links = tab.getElementsByTagName('a');
      tab.linkedID = links[0].href.split("#")[1];
      tab.linkedPanel = $(tab.linkedID);
      tab.linkedPanel.style.clear = "both";

      if ((this.options.defaultPanel != '') && (this.options.defaultPanel == tab.linkedID)) {
        this.openPanel(tab, this.options);
      }else{
        $($(tab).linkedPanel).hide();
      }

      $(links[0]).observe('click', function (event) {
        var element = Event.findElement(event, 'li');
        this.openPanel(element, this.options);
        Event.stop(event);
      }.bind(this));
    }
  },

  openPanel: function(tab, options){
    tab = $(tab); // ie hack

    if (this.currentTab != '') {
      this.currentTab.linkedPanel.hide();
      this.currentTab.removeClassName('selected');
    }

    this.currentTab = tab;

    tab.linkedPanel.show();
    tab.addClassName('selected');
    var url = this.options.ajaxUrls[tab.linkedID];

    if (url != undefined) {
      tab.linkedPanel.update(this.options.ajaxLoadingText);
      new Ajax.Updater(tab.linkedPanel, url, {
        evalScripts: !!options.evalScripts
      });
    }
  }
};