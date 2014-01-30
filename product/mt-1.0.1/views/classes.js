/**
 * mt-1.0.1
 * views/classes.js
 * Преднастроенные классы ExtJS уровня приложения
 *
 */

Application.Top_panel = Ext.extend(Ext.Container, {
	hgt:80,
	homeBtn:false,
	moduleIcon:'../../resources/images/home-big.png',
	moduleName:'',
	userName:'',
	initComponent:function(){
		var config = {
			id:'topPanel',
			region:'north',
			height: this.hgt,
			layout:{type:'hbox',align:'middle'},
			items:[{
				xtype:'button',
				hidden:!(this.homeBtn),
				margins:'0 0 0 5',
				scale: 'large',
				icon: '../../resources/images/home-small.png',
				tooltip: 'перейти к dasboard',
				handler:function(){window.location = '../../modules/dashboard/index.php';},
				height:this.hgt-20,
			},{
				xtype:'spacer',
				flex:1
			},{
				xtype: 'buttongroup',
				columns: 2,
				height:this.hgt-20,
				padding:'0 20 0 20',
				items:[
					{
						xtype:'container',
						html:"<img src='"+this.moduleIcon+"'/>"
					},
					{
						xtype:'label',
						html:"<b><div class='topText'>"+this.moduleName+"</div></b>",
					}
				]
			},{
				xtype:'spacer',
				flex:1
			},{
				xtype:'container',
				layout:'vbox',
				height: this.hgt-20,
				margins:'0 30 5 0',
				cls:'user',
				items:[{
                    xtype:'label',
                    text:'пользователь:',
                },{
					xtype:'label',
					id:'userNameLbl',
					cls:'userName',
					text:this.userName,
				}],
			},{
				xtype:'button',
				icon: '../../resources/images/logout.png',
				tooltip: 'завершить сенс',
				height:this.hgt-20,
				scale: 'large',
				margins:'0 5 0 0',
				handler:function(){window.location = '../../controllers/logout.php';}
			}],
		}
		Ext.apply(this, Ext.apply(this.initialConfig, config));
		Application.Top_panel.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('toppanel', Application.Top_panel);

Application.Vp = Ext.extend(Ext.Viewport, {
	hgt:80,
	homeBtn:false,
	moduleIcon:'./resources/images/module.png',
	moduleName:'Dashboard',
	userName:'',
	moduleItems:[],
	initComponent:function(){
		var config = {
			renderTo: document.body,
			layout: {type:'border'},
			items:[{
				homeBtn:this.homeBtn,
				xtype:'toppanel',
				hgt:this.hgt,
				homeBtn:this.homeBtn,
				moduleIcon:this.moduleIcon,
				userName:this.userName,
				moduleName:this.moduleName,
			},{
				xtype:'container',
				layout:'fit',
				region:'center',
				margins: '0 5 5 5',
				items:this.moduleItems,
			}],		
		};
		Ext.apply(this, Ext.apply(this.initialConfig, config));
		Application.Vp.superclass.initComponent.apply(this, arguments);
	},
	setUserName:function(uName){
		this.userName = uName;
		Ext.getCmp("userNameLbl").setText(uName);
		this.doLayout();
	},
	onRender:function(){
		var usrStore = new Ext.data.JsonStore({
			url: '../../controllers/get_user_name.php',
			autoDestroy: true,
			fields: [{name: 'name', mapping: 'name'}],
			root:'rows',
			listeners: {
				load: function(){
					this.each(function(row){
						Ext.getCmp("userNameLbl").setText(row.get("name"));
					});
				}
			}
		});
		usrStore.load()
	},	
});
Ext.reg('Vp', Application.Vp);