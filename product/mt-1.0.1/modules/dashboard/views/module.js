/**
 * mt-1.0.1
 * dashboard
 * /modules/dasboard/views/module.js
 * код клиентской части модуля
 *
 */
Ext.Ajax.request({
	url:'./controllers/get_modules.php',
	success:function(result, request){
        if(result.responseText != 'none'){ 
            btn = Ext.util.JSON.decode(result.responseText);
        }else{
            btn = [];
        }
		var mainPanel = new Ext.Panel({ 
			id:'dashPanel',
			layout:'table',
			layoutConfig: {columns:1},
			autoScroll:true,
			items:btn,
			listeners:{
				afterrender:function(){
					var col = Math.round(this.getWidth()/100);
					this.getLayout().columns = col;
					this.doLayout();
				},
			},
		});
		var x = new Application.Vp({
			homeBtn:false, 
			moduleItems:[mainPanel]
		});
	}
});
