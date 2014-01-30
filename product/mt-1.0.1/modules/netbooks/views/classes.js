/**
 * mt-1.0.1
 * netbooks
 * modules/netbooks/views/classes.js
 * Преднастроенные классы ExtJS 
 * уровня модуля netbooks
 *
 */
Application.ToolButton = Ext.extend(Ext.Button, {
	initComponent:function(){
		var config = {
			margins:'5 5 0 5',
			width:38,
			scale:'large',
		};
		Ext.apply(this, Ext.apply(this.initialConfig, config));
		Application.ToolButton.superclass.initComponent.apply(this, arguments);
	},
});
Ext.reg('toolButton', Application.ToolButton);

Application.Tab = Ext.extend(Ext.Panel, {
	btns:[],
	cntr:[],
	est:[],
	isEst:false,
	initComponent:function(){
		var config = {
			layout:'border',
			items:[{
				xtype:'container',
				region:'west',
				width:48,
				items:this.btns,
				layout:'vbox'
			},{
				xtype:'container',
				region:'center',
				margins:'3 3 3 0',
				items:this.cntr,
				layout:'fit',
			},{
				xtype:'container',
				region:'east',
				width:200,
            	split: true,
            	margins:'3 3 3 0',
            	layout:'fit',
            	hidden:!(this.isEst),
            	items:this.est,
			}],
			listeners:{
				//activate:function(p){p.doLayout();},
			},
		};
		Ext.apply(this, Ext.apply(this.initialConfig, config));
		Application.Tab.superclass.initComponent.apply(this, arguments);
	},
});
Ext.reg('tab', Application.Tab);

Application.TabPanel = Ext.extend(Ext.TabPanel,{
	initComponent:function(){
		var config = {
			autoScroll: true,
    		activeTab:0,
    		plain:true,
		};
		Ext.apply(this, Ext.apply(this.initialConfig, config));
		Application.TabPanel.superclass.initComponent.apply(this, arguments);
	},
});
Ext.reg('tabPanel', Application.TabPanel);
