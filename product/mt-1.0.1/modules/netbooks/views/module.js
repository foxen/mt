/**
 * mt-1.0.1
 * netbooks
 * views/module.js
 * код клиентской части модуля
 *
 */


//stores============================================================================================

var formatsStore	= new Ext.data.JsonStore({
	url:'./controllers/get_licenses_formats.php',
	fields: [{name: 'formatname', mapping: 'formatname'},
			 {name: 'formatid', mapping: 'formatid'}],
	root:'rows',
	autoLoad:true,
});

var licensesStore	= new Ext.data.JsonStore({
	url:'./controllers/get_licenses.php',
	fields: [{name: 'id', mapping: 'id'},
			 {name: 'name', mapping: 'name'},
		     {name: 'format_name', mapping: 'format_name'},
		     {name: 'is_paused', mapping: 'is_paused'},
			 {name: 'is_taken', mapping: 'is_taken'},
             {name: 'export_path', mapping: 'export_path'}],
	root:'rows',
	autoLoad:true,
});

var agentsStore	= new Ext.data.JsonStore({
	url:'./controllers/get_agents.php',
	fields: [{name: 'id', mapping: 'id'},
			 {name: 'license_name', mapping: 'license_name'},
		     {name: 'name', mapping: 'name'},
		     {name: 'id_agent', mapping: 'id_agent'},
			 {name: 'grp_names', mapping: 'grp_names'},
             {name: 'is_debt', mapping: 'is_debt'},
             {name: 'is_quantity', mapping: 'is_quantity'},
             {name: 'is_paused', mapping: 'is_paused'}],
	root:'rows',
	autoLoad:true,
});

var agentsMsStore	= new Ext.data.JsonStore({
	url:'./controllers/get_agents_ms.php',
	fields: [{name: 'id', mapping: 'id'},
			 {name: 'alias', mapping: 'alias'},
		     {name: 'grp', mapping: 'grp'},
		     {name: 'taken', mapping: 'taken'},],	     
	root:'rows',
});

var licensesAddAgentStore	= new Ext.data.JsonStore({
	url:'./controllers/get_licenses_agents.php',
	fields: [{name: 'id', mapping: 'id'},
			 {name: 'name', mapping: 'name'},
		     {name: 'format_name', mapping: 'format_name'},
			 {name: 'is_taken', mapping: 'is_taken'}],
	root:'rows',
});


//EO stores=========================================================================================

//objects===========================================================================================
var agBtns = [
	{
		id:'addAgentBtn',
        xtype:'toolButton',
        icon:'./resources/images/adduser.png',
        handler:addAgent,
        tooltip:'добавить агента',
	},{
		id:'editAgentBtn',
        xtype:'toolButton',
        icon:'./resources/images/edituser.png',
        disabled:true,
        tooltip:'редактировать',
        handler:editAgent,
	},{
        id:'removeAgentBtn',
		xtype:'toolButton',
        icon:'./resources/images/removeuser.png',
        disabled:true,
        tooltip:'удалить агента',
        handler:remove_agent,
	},{
        id:'pauseAgentBtn',
		xtype:'toolButton',
        icon:'./resources/images/stop.png',
        disabled:true,
        tooltip:'приостановить агента',
        handler:pause_agent,
	}
];

var licBtns = [
	{
		id:'addLicenseBtn',
        xtype:'toolButton',
        icon:'./resources/images/add.png',
        handler:add_license,
        tooltip:'добавить лицензию',
	},{
		id:'editLicenseBtn',
        xtype:'toolButton',
        icon:'./resources/images/edit.png',
        disabled:true,
        tooltip:'редактировать',
        handler:add_license,
	},{
        id:'removeLicenseBtn',
		xtype:'toolButton',
        icon:'./resources/images/delete.png',
        disabled:true,
        tooltip:'удалить лицензию',
        handler:remove_license,
	},{
        id:'pauseLicenseBtn',
		xtype:'toolButton',
        icon:'./resources/images/stop.png',
        disabled:true,
        tooltip:'приостановить лицензию',
        handler:pause_license,
	}
];

var licensesGrid = new Ext.grid.GridPanel({
	id: 'licensesGrid',
    name: 'licensesGrid',
    store: licensesStore,
    autoExpandColumn: 'name',
    colModel: new Ext.grid.ColumnModel({
		columns:[
			{id: 'id', dataIndex: 'id', header:'id'},
			{id: 'name', dataIndex: 'name', header:'Имя лицензии'},
			{id: 'format_name', dataIndex: 'format_name', header:'Формат'},
            {id: 'export_path', dataIndex: 'export_path', header:'Путь'},
			{id: 'is_paused', dataIndex: 'is_paused', header:'Состояние'},
			{id: 'is_taken', dataIndex: 'is_taken', header:'Занятость'},
        ]
    }),
    viewConfig:{
		getRowClass:function(record){
			if(record.get('is_paused') == 'приостановлена'){
				return 'paused';
            }
            else if(record.get('is_taken') == 'занята'){
                return 'taken';
            }
            else{
                return 'free';
            }
        }
    },
    sm: new Ext.grid.RowSelectionModel({
        singleSelect:true,
        listeners:{
            rowselect:function(){
                Ext.getCmp('removeLicenseBtn').setDisabled(false);
                Ext.getCmp('editLicenseBtn').setDisabled(false);
                var state = Ext.getCmp('licensesGrid').getSelectionModel().getSelected().get('is_paused');
                if(state == 'приостановлена' ){
                    Ext.getCmp('pauseLicenseBtn').setIcon('./resources/images/start.png');
                    Ext.getCmp('pauseLicenseBtn').setTooltip('возобновить лицензию');
                }
                else{
                    Ext.getCmp('pauseLicenseBtn').setIcon('./resources/images/stop.png');
                    Ext.getCmp('pauseLicenseBtn').setTooltip('приостановить лицензию');
                }
                Ext.getCmp('pauseLicenseBtn').setDisabled(false);
            }
        }
    }),
});

var agentsGrid = new Ext.grid.GridPanel({
	id: 'agentsGrid',
    name: 'agentsGrid',
    store: agentsStore,
    autoExpandColumn: 'grp_names',
    colModel: new Ext.grid.ColumnModel({
		columns:[
			{id: 'id', dataIndex: 'id', header:'id'},
			{id: 'license_name', dataIndex: 'license_name', header:'Лицензия'},
			{id: 'name', dataIndex: 'name', header:'Торговый агент'},
            {id: 'id_agent', dataIndex: 'id_agent', header:'id агента'},
            {id: 'grp_names', dataIndex: 'grp_names', header:'Группы'},
            {id: 'is_debt', dataIndex: 'is_debt', header:'Задолженность'},
            {id: 'is_quantity', dataIndex: 'is_quantity', header:'Остатки'},
			{id: 'is_paused', dataIndex: 'is_paused', header:'Состояние'},
        ]
    }),
    viewConfig:{
		getRowClass:function(record){
			if(record.get('is_paused') == 'приостановлен'){
				return 'paused';
            }
        }
    },
    sm: new Ext.grid.RowSelectionModel({
        singleSelect:true,
        listeners:{
            rowselect:function(){
                Ext.getCmp('removeAgentBtn').setDisabled(false);
                Ext.getCmp('editAgentBtn').setDisabled(false);
                var state = Ext.getCmp('agentsGrid').getSelectionModel().getSelected().get('is_paused');
                if(state == 'приостановлен' ){
                    Ext.getCmp('pauseAgentBtn').setIcon('./resources/images/start.png');
                    Ext.getCmp('pauseAgentBtn').setTooltip('возобновить агента');
                }
                else{
                    Ext.getCmp('pauseAgentBtn').setIcon('./resources/images/stop.png');
                    Ext.getCmp('pauseAgentBtn').setTooltip('приостановить агента');
                }
                Ext.getCmp('pauseAgentBtn').setDisabled(false);
            }
        }
    }),
});
//EO oblects========================================================================================

//functions=========================================================================================

function add_license(p){
    
    var idLic = 'none';
    
	var addLicenseForm = new Ext.FormPanel({
		bodyStyle: 'padding:15px',
		labelWidth: 110,
		url:'./controllers/add_license.php',
		labelAlign:'right',
		monitorValid:true,
		frame:true,
		items:[{
			xtype: 'textfield',
			id:'licensename',
			fieldLabel: 'Имя лицензии',
			allowBlank:false,
		},{
            xtype: 'textfield',
			id:'licensepath',
			fieldLabel: 'Путь',
			allowBlank:true,
		},{
			xtype:'checkbox',
			id:'ispaused',
			fieldLabel: 'Приостановленна',
		},{
			id:'idformat',
			xtype:'combo',
			mode:'local',
			value:1,
			fieldLabel: 'Формат',
			hiddenName: 'formatid',
			width:100,
			store: formatsStore,
			valueField: 'formatid',
			displayField: 'formatname',
			triggerAction: 'all',
			editable:false,
		}],
		buttons:[{
			id:'btnSubmit',
			text:'Сохранить',
			formBind: true,	 
			type: 'submit',
			handler: submit_license
		}],
		keys: [{
            key: [Ext.EventObject.ENTER], 
            handler: submit_license,
        }],
        listeners:{
			
        },
	});
    
	if (p.id == 'editLicenseBtn'){
        licName = Ext.getCmp('licensesGrid').getSelectionModel().getSelected().get('name');
        licPath = Ext.getCmp('licensesGrid').getSelectionModel().getSelected().get('export_path');
        idLic = Ext.getCmp('licensesGrid').getSelectionModel().getSelected().get('id');
        Ext.getCmp('licensename').setValue(licName);
        Ext.getCmp('licensepath').setValue(licPath);
        licState = Ext.getCmp('licensesGrid').getSelectionModel().getSelected().get('is_paused');
        if(licState == 'приостановлена'){
            Ext.getCmp('ispaused').setValue(true);
        }
    }
    
	var addLicenseWindow = new Ext.Window({
		items:addLicenseForm,
		title:'Добавить лицензию',
		modal:true,
		border: false,
		layout:'fit',
		plain: true,
		width:340,
        height:220,
	});
    addLicenseWindow.show();
    
    function submit_license(p){
		addLicenseForm.getForm().submit({
            params:{
                idlicense:idLic,
            },
			success:function(){ 
				Ext.getCmp('licensesGrid').getStore().reload();
                Ext.getCmp('agentsGrid').getStore().reload();
				addLicenseWindow.close();
			},
			failure:function(form, action){ 
				if(action.failureType == 'server'){ 
					obj = Ext.util.JSON.decode(action.response.responseText); 
					Ext.Msg.alert('Лицензия не добавлена : ', obj.errors.reason); 
				}else{ 
					Ext.Msg.alert('Внимание!', 'Ошибка сервера : ' + action.response.responseText); 
				} 
				addLicenseForm.getForm().reset(); 
            } 
        });
	}
}

function remove_license(){
    var licenseId = Ext.getCmp('licensesGrid').getSelectionModel().getSelected().get('id');
    var licenseName = Ext.getCmp('licensesGrid').getSelectionModel().getSelected().get('name');
    var isTaken = Ext.getCmp('licensesGrid').getSelectionModel().getSelected().get('is_taken');
    
    var alertLicDelete = new Ext.Window({
        modal:true,
        title:'Внимание!',
        width:300,
        height:150,
        layout:{type:'vbox', align:'center'},
        items:[{          
            xtype:'label',
            text:'Вы действительно собираетесь удалить лицензию',
            margins:'15 0 0 0'
        },
        {
            cls:'userName',
            xtype:'label',
            text:licenseName+"?",
            margins:'2 0 0 0'
        }],
        buttons:[{
            text:'Да',
            handler:function(){
                Ext.Ajax.request({
                    url:"./controllers/remove_license.php",
                    method:'POST',
                    params:{
                        idlicense:licenseId,
                    },
                    success:function(){
                        Ext.getCmp('pauseLicenseBtn').setDisabled(true);
                        Ext.getCmp('editLicenseBtn').setDisabled(true);
                        Ext.getCmp('removeLicenseBtn').setDisabled(true);
                        Ext.getCmp('licensesGrid').getStore().reload();
                        alertLicDelete.close();
                        Ext.getCmp('agentsGrid').getStore().reload();
                    },
                });
            },
        },
        {
            text:'Нет',
            handler:function(){alertLicDelete.close();}
        }]
    });
    
    if (isTaken == "занята"){
        Ext.Msg.alert("Внимание!","Лицензия занята, сначала удалите связанных с ней агентов");
    }
     else{
        alertLicDelete.show();
    }   
}

function pause_license(){
    var licenseId = Ext.getCmp('licensesGrid').getSelectionModel().getSelected().get('id');
    var state = Ext.getCmp('licensesGrid').getSelectionModel().getSelected().get('is_paused');
    var icn;
    var toolTipText;
    if(state == 'приостановлена'){
        pause_url = "./controllers/start_license.php";
        icn = './resources/images/stop.png';
        toolTipText = 'приостановить лицензию';
    }
    else{
        pause_url = "./controllers/pause_license.php";
        icn = './resources/images/start.png';
        toolTipText = 'возобновить лицензию';
    }
    Ext.Ajax.request({
        url:pause_url,
        method:'POST',
        params:{
            idlicense:licenseId,
        },
        success:function(){
            Ext.getCmp('pauseLicenseBtn').setIcon(icn);
            Ext.getCmp('pauseLicenseBtn').setTooltip(toolTipText);
            Ext.getCmp('licensesGrid').getStore().reload();
            Ext.getCmp('agentsGrid').getStore().reload();
        },
    });
}

function addAgent(p){
    agentsMsStore.load();
    licensesAddAgentStore.load();
    
    var licensesAddAgentGrid = new Ext.grid.GridPanel({
		region:'east',
		split: true,
		width:270,
		id: 'licensesAddAgentGrid',
		name: 'licensesAddAgentGrid',
		store: licensesAddAgentStore,
		autoExpandColumn: 'name',
		colModel: new Ext.grid.ColumnModel({
			columns:[
				{id: 'id', dataIndex: 'id', header:'id', width:30},
				{id: 'name', dataIndex: 'name', header:'Имя лицензии'},
				{id: 'format_name', dataIndex: 'format_name', header:'Формат',width:60},
				{id: 'is_taken', dataIndex: 'is_taken', header:'Занятость',width:70},
			]
		}),
		viewConfig:{
			getRowClass:function(record){
				if(record.get('is_taken') == 'занята'){
					return 'taken';
				}
				else{
					return 'free';
				}
			}
		},
		sm: new Ext.grid.RowSelectionModel({
			singleSelect:true,
			listeners:{
				rowselect:function(){
					Ext.getCmp('selectLicenseBtn').setDisabled(false);
				}
			}
		}),
		bbar:[{
			id:'selectLicenseBtn',
			//scale:'medium',
			width:40,
			icon:'./resources/images/leftarrow.png',
			disabled:true,
			tooltip:'Выбрать лицензию',
			handler:function(){
				var licenseName = licensesAddAgentGrid.getSelectionModel().getSelected().get('name');
				var licenseId = licensesAddAgentGrid.getSelectionModel().getSelected().get('id');
				Ext.getCmp('licenseField').setValue(licenseName);
				Ext.getCmp('licenseIdField').setValue(licenseId);
				if(Ext.getCmp('agentField').getValue() != ''){
					Ext.getCmp('saveAgentBtn').setDisabled(false);
				}
			},
		}]
	});
    
    var agentsAddAgentGrid = new Ext.grid.GridPanel({
		region:'west',
		width:500,
		split: true,
		id: 'agentsAddAgentGrid',
		name: 'agentsAddAgentGrid',
		store: agentsMsStore,
		autoExpandColumn: 'grp',
		colModel: new Ext.grid.ColumnModel({
			columns:[
				{id: 'id', dataIndex: 'id', header:'id', width:50},
				{id: 'alias', dataIndex: 'alias', header:'Имя агента', width:150, sortable:true},
				{id: 'grp', dataIndex: 'grp', header:'Группы', sortable:true},
				{id: 'taken', dataIndex: 'taken', header:'Занятость',width:100,sortable:true},
			]
		}),
		viewConfig:{
			getRowClass:function(record){
				if(record.get('taken') == 'уже привязан'){
					return 'taken';
				}
				else{
					return 'free';
				}
			}
		},
		sm: new Ext.grid.RowSelectionModel({
			singleSelect:true,
			listeners:{
				rowselect:function(){
					Ext.getCmp('selectAgentBtn').setDisabled(false);
				}
			}
		}),
		bbar:[{
			id: 'TpFilter1',
            xtype: 'textfield',
            name: 'TpFilter1',
            fieldLabel: 'Фильтр:',
            emptyText:'Фильтр',
            enableKeyEvents: true,
            listeners:{
				keyup: function(el){ 
					$val = el.getValue();
					agentsMsStore.filter('alias', $val, true, false);
                }
            }
		},
			' ',
		{
			text: 'Очистить фильтр',
			handler: function(){
				agentsMsStore.filter('alias', "");
                Ext.getCmp('TpFilter1').setValue( "" );
            }
        },
			'->',
		{
			id:'selectAgentBtn',
			//scale:'medium',
			width:40,
			icon:'./resources/images/rightarrow.png',
			disabled:true,
			tooltip:'Выбрать агента',
			handler:function(){
				var agentName = agentsAddAgentGrid.getSelectionModel().getSelected().get('alias');
				var grpName = agentsAddAgentGrid.getSelectionModel().getSelected().get('grp');
				var agentId = agentsAddAgentGrid.getSelectionModel().getSelected().get('id');
				Ext.getCmp('agentField').setValue(agentName);
                Ext.getCmp('agentNameField').setValue(agentName);
				Ext.getCmp('grpField').setValue(grpName);
                Ext.getCmp('agentGrpsField').setValue(grpName);
				Ext.getCmp('agentIdField').setValue(agentId);
				if(Ext.getCmp('licenseField').getValue() != ''){
					Ext.getCmp('saveAgentBtn').setDisabled(false);
				}
			},
		}]
	});
    
    var agentsAddForm = new Ext.FormPanel({
		url:'./controllers/add_agent.php',
		region:'center',
		frame:true,
		labelAlign:'right',
		items:[{
            xtype:'field',
			id:'agentNameField',
			value:'',
			hidden:true
		},{
            xtype:'field',
			id:'agentGrpsField',
			value:'',
			hidden:true
		},{
			xtype:'field',
			id:'licenseIdField',
			value:'',
			hidden:true
		},{
			xtype:'field',
			id:'agentIdField',
			value:'',
			hidden:true
		},{
			xtype:'displayfield',
			id:'agentField',
			value:'',
			fieldLabel:'Агент',
		},{
			xtype:'displayfield',
			id:'grpField',
			value:'',
			fieldLabel:'Группы',
		},{
			xtype:'displayfield',
			id:'licenseField',
			value:'',
			fieldLabel:'Лицензия',
		},{
			xtype:'checkbox',
			id:'isquantity',
			fieldLabel: 'Показывать остатки',
		},{
			xtype:'checkbox',
			id:'isdebt',
			fieldLabel: 'Показывать задолженность',
		},{
			xtype:'checkbox',
			id:'ispaused',
			fieldLabel: 'Приостановить',
		}]
	});
	
	var agentsAddSouth = new Ext.Container({
		region:'south',
		height:40,
		layout:{type:'hbox',align:'middle'},
		items:[{
			xtype:'spacer',
			flex:1
		},{
			xtype:'button',
			id:'saveAgentBtn',
			disabled:true,
			text:'Сохранить',
			scale:'medium',
			margins:'0 5 0 0',
			handler:saveAgent,
		}]
	})
    
    var addAgentContainer = new Ext.Container({
		layout:'border',
		items:[licensesAddAgentGrid,agentsAddAgentGrid,agentsAddForm,agentsAddSouth]
	});
    
    var addAgentWindow = new Ext.Window({
		items:addAgentContainer,
		title:'Добавить агента',
		modal:true,
		border: false,
		layout:'fit',
		plain: true,
		width:1200,
        height:300,
	});
	
	addAgentWindow.show();
	
	function saveAgent(){
		agentsAddForm.getForm().submit({
            success:function(){
				Ext.getCmp('licensesGrid').getStore().reload();
                Ext.getCmp('agentsGrid').getStore().reload();
				addAgentWindow.close();
			},
			failure:function(form, action){ 
				if(action.failureType == 'server'){ 
					obj = Ext.util.JSON.decode(action.response.responseText); 
					Ext.Msg.alert('Агент не добавлен : ', obj.errors.reason); 
				}else{ 
					Ext.Msg.alert('Внимание!', 'Ошибка сервера : ' + action.response.responseText); 
				} 
				agentsAddForm.getForm().reset(); 
            }
		});
	}
    
}

function editAgent(){
    
    var selected    = agentsGrid.getSelectionModel().getSelected();
    var agentName   = selected.get('name');
    var agentGrp    = selected.get('grp_names');
    var licenseName = selected.get('license_name');
    var debt        = selected.get('is_debt');
    var paused      = selected.get('is_paused');
    var quantity    = selected.get('is_quantity');
    var agentId     = selected.get('id');
    
    var isDebt     = false;
    var isPaused   = false;
    var isQuantity = false;
    
    if  (debt == 'показывать'){
        isDebt =true;
    }
    
    if  (paused == 'приостановлен'){
        isPaused =true;
    }
    
    if  (quantity == 'показывать'){
        isQuantity =true;
    }
    
    var agentsEditForm = new Ext.FormPanel({
        url:'./controllers/edit_agent.php',
		frame:true,
		labelAlign:'right',
		items:[{
            xtype:'field',
			id:'agentIdField',
			value:agentId,
			hidden:true,
		},{
            xtype:'displayfield',
			id:'agentField',
			value:agentName,
			fieldLabel:'Агент',
		},{
            xtype:'displayfield',
			id:'grpField',
			value:agentGrp,
			fieldLabel:'Группы',
		},{
            xtype:'displayfield',
			id:'licenseField',
			value:licenseName,
			fieldLabel:'Лицензия',
		},{
            xtype:'checkbox',
			id:'isquantity',
			fieldLabel: 'Показывать остатки',
            checked:isQuantity,
		},{
            xtype:'checkbox',
			id:'isdebt',
			fieldLabel: 'Показывать задолженность',
            checked:isDebt,
		},{
			xtype:'checkbox',
			id:'ispaused',
			fieldLabel: 'Приостановить',
            checked:isPaused,
        }],
        buttons:[{
			id:'btnSubmit',
			text:'Сохранить',
			formBind: true,	 
			type: 'submit',
			handler: submit_agent
		}],
		keys: [{
            key: [Ext.EventObject.ENTER], 
            handler: submit_agent,
        }],
        
        
    });
    
    function submit_agent(p){
		agentsEditForm.getForm().submit({
			success:function(){ 
				Ext.getCmp('agentsGrid').getStore().reload();
				editAgentWindow.close();
			},
			failure:function(form, action){ 
				if(action.failureType == 'server'){ 
					obj = Ext.util.JSON.decode(action.response.responseText); 
					Ext.Msg.alert('Агент не изменен : ', obj.errors.reason); 
				}else{ 
					Ext.Msg.alert('Внимание!', 'Ошибка сервера : ' + action.response.responseText); 
				} 
				editAgentWindow.close();
            } 
        });
	}
    
    var editAgentWindow = new Ext.Window({
		items:agentsEditForm,
		title:'Редактировать',
		modal:true,
		border: false,
		layout:'fit',
		plain: true,
		width:400,
        height:300,
	});
    
    editAgentWindow.show();
}

function remove_agent(){
    var selected    = agentsGrid.getSelectionModel().getSelected();
    var idAgent     = selected.get('id');
    var agentName   = selected.get('name');
    var licenseName = selected.get('license_name');
    var alertDelete = new Ext.Window({
        modal:true,
        title:'Внимание!',
        width:300,
        height:150,
        layout:{type:'vbox', align:'center'},
        items:[{      
            xtype:'label',
            text:'Вы действительно собираетесь удалить агента',
            margins:'15 0 0 0'
        },
        {
            xtype:'label',
            text:agentName,
            margins:'2 0 0 0'
        },
        {
            xtype:'label',
            text:' связанного с лицензией',
            margins:'2 0 0 0'
        },
        {
            xtype:'label',
            text:licenseName+'?',
            margins:'3 3 3 3'
        }],
        buttons:[{
            text:'Да',
            handler:function(){
                Ext.Ajax.request({
                    url:"./controllers/remove_agent.php",
                    method:'POST',
                    params:{
                        idAgent:idAgent
                    },
                });
                Ext.getCmp('agentsGrid').getStore().reload();
                Ext.getCmp('licensesGrid').getStore().reload();
                
                Ext.getCmp('pauseAgentBtn').setDisabled(true);
                Ext.getCmp('editAgentBtn').setDisabled(true);
                Ext.getCmp('removeAgentBtn').setDisabled(true);
                
                alertDelete.close();
            }
        },
        {
            text:'Нет',
            handler:function(){alertDelete.close();}
        }]
    });
    alertDelete.show();
}

function pause_agent(){
    var selected    = agentsGrid.getSelectionModel().getSelected();
    var agentId = selected.get('id');
    var state = selected.get('is_paused');
    var licenseName = selected.get('license_name');
    var lic_row = Ext.getCmp('licensesGrid').getStore().find('name',licenseName);
    var lic_state = Ext.getCmp('licensesGrid').getStore().getAt(lic_row).get('is_paused');
    if (lic_state != 'приостановлена'){ 
        if(state == 'приостановлен'){
            pause_url = "./controllers/start_agent.php";
            Ext.getCmp('pauseAgentBtn').setIcon('./resources/images/stop.png');
            Ext.getCmp('pauseAgentBtn').setTooltip('приостановить агента');
        }
        else{
            pause_url = "./controllers/pause_agent.php";
            Ext.getCmp('pauseAgentBtn').setIcon('./resources/images/start.png');
            Ext.getCmp('pauseAgentBtn').setTooltip('возобновить агента');
        }
        Ext.Ajax.request({
            url:pause_url,
            method:'POST',
            params:{
                agentId:agentId,
            },
            success:function(){
                Ext.getCmp('agentsGrid').getStore().reload();
            },
        });
        
    }
    else {
        Ext.Msg.alert("Внимание!","Агент связан с приостановленной лицензией, сначала возобновите лицензию");
    }
}
//EO functions======================================================================================

//Сборка============================================================================================

var agentsTab = new Application.Tab({
	title:'Агенты',
	btns:agBtns,
	cntr:agentsGrid,
});

var licensesTab = new Application.Tab({
	title:'Лицензии',
	btns:licBtns,
	cntr:licensesGrid,
});

var netbooksTabPanel = new Application.TabPanel({
	items:[
		agentsTab,licensesTab
	],
});

var vp = new Application.Vp({
			moduleName:'Netbooks',
			homeBtn:true, 
			moduleItems:[netbooksTabPanel]
});
