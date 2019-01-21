define([
         'jquery',
         'ko',
         'uiComponent',
         'Magento_Checkout/js/model/quote',
         'Magento_Checkout/js/model/shipping-service',
         'SM_Shipping/js/view/checkout/shipping/office-service',
         'mage/translate'
       ], function ($, ko, Component, quote, shippingService, officeService, t) {
  'use strict';
  
  return Component.extend({
                            defaults: {
                              template: 'SM_Shipping/checkout/shipping/form'
                            },
    
                            initialize: function (config) {
                              this.offices = ko.observableArray();
                              this.selectedOffice = ko.observable();
                              this.selectedOffice.subscribe(function(office) {
                                if (quote.shippingAddress().extensionAttributes == undefined) {
                                  quote.shippingAddress().extensionAttributes = {};
                                }
                                quote.shippingAddress().extensionAttributes.outlet_address = office.id;
                              });
                              this._super();
                            },
    
                            initObservable: function () {
                              this._super();
      
                              this.showOfficeSelection = ko.computed(function() {
                                return this.offices().length != 0
                              }, this);
    
                              this.selectedMethod = ko.computed(function() {
                                var method = quote.shippingMethod();
                                var selectedMethod = method != null ? method.carrier_code : null;
                                return selectedMethod;
                              }, this);
      
                              quote.shippingMethod.subscribe(function(method) {
                                var selectedMethod = method != null ? method.carrier_code : null;
                                if (selectedMethod == 'smstorepickup') {
                                  this.getOutletAddress();
                                }
                              }, this);
      
                              //this.selectedOffice.subscribe(function(office) {
                              //  if (quote.shippingAddress().extensionAttributes == undefined) {
                              //    quote.shippingAddress().extensionAttributes = {};
                              //  }
                              //  quote.shippingAddress().extensionAttributes.outlet_address = office;
                              //});
      
      
                              return this;
                            },
    
                            setOfficeList: function(list) {
                              this.offices(list);
                            },
  
                            getOutletAddress: function() {
                              officeService.getOfficeList(quote.shippingAddress(), this);
                              //var defaultOffice = this.offices()[0];
                              //if (defaultOffice) {
                              //  this.selectedOffice(defaultOffice);
                              //}
                            },
    
                            getOffice: function() {
                              var office;
                              if (this.selectedOffice()) {
                                for (var i in this.offices()) {
                                  var m = this.offices()[i];
                                  if (m.name == this.selectedOffice()) {
                                    office = m;
                                  }
                                }
                              }
                              else {
                                office = this.offices()[0];
                              }
      
                              return office;
                            },
    
                            initSelector: function() {
                              var startOffice = this.getOffice();
                            }
                          });
});