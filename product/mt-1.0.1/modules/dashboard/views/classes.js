/**
 * mt-1.0.1
 * modules/dashboard/views/classes.js
 * Преднастроенные классы ExtJS 
 * уровня модуля dashboard
 *
 */
Application.Button_widget  = Ext.extend(Ext.Container, {
	buttonIcon:'../../resources/images/home-small.png',
	buttonLabel:'',
	wdth:100,
	hgt:100,
	hndlr:'',
	initComponent:function(){
		var config = {
			layout:{type:'vbox', align :'center'},
			width:this.wdth,
			height:this.hgt,
			items:[{
				margins: '15 0 0 0',
				scale:'large',
				xtype:'button',
				icon:this.buttonIcon,
				hndlr:this.hndlr,
				handler:function(b){window.location = b.hndlr;},
			},{
				xtype:'label',
				text:this.buttonLabel,
				cls:'btnwidgetlbl',
			}],
		};
		Ext.apply(this, Ext.apply(this.initialConfig, config));
		Application.Button_widget.superclass.initComponent.apply(this, arguments);
	},
});
Ext.reg('btnwidget', Application.Button_widget);
