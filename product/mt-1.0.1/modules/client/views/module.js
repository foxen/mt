/**
 * mt-1.0.1
 * client
 * views/module.js
 * код клиентской части модуля
 *
 */
var mValue = 100000; 
var days = new Array("вc", "пн", "вт","ср","чт","пт","сб");
var today = new Date();
var dayName = days[today.getDay()];

var d = new Date();
var y = d.getFullYear() + '';
var dd = d.getDate() + '';
var mm = d.getMonth() + 1;
var mm = mm + '';
if (mm.length == 1){
	mm = '0' + mm;
}

if (dd.length == 1){
	dd = '0' + dd;
}
var fDt = dd + '.' + mm + '.' + y.substr(2,2);

//var dayName = 'пн'

//stores================================================================
var clientsStore	= new Ext.data.JsonStore({
	url:'./controllers/get_clients.php',
	fields: [{name: 'ida', mapping: 'ida'},
             {name: 'id_agent', mapping: 'id_agent'},
			 {name: 'id_client', mapping: 'id_client'},
		     {name: 'alias', mapping: 'alias'},
		     {name: 'address', mapping: 'address'},
			 {name: 'dbt', mapping: 'dbt'},
             {name: 'days', mapping: 'days'},
             {name: 'ord', mapping: 'ord'}],
	root:'rows',
    sortInfo: {
        field: 'ord',
        direction: 'ASC'
    },
	autoLoad:true,
});

var agentsStore	= new Ext.data.JsonStore({
	url:'./controllers/get_agents.php',
	fields: [{name: 'id_agent', mapping: 'id_agent'},
		     {name: 'alias', mapping: 'alias'},
             {name: 'n', mapping: 'n'}],
	root:'rows',
	autoLoad:true,
    listeners:{
        'load':function(){
            var intAlias = agentsStore.getAt(0).get('alias');
            var intId = agentsStore.getAt(0).get('id_agent');
            window.setTimeout(function(){
                clientsGrid.getStore().filter([{
                property     : 'id_agent',
                value        : intId,
                anyMatch     : true,
                caseSensitive: true
            },{
                property     : 'days',
                value        : dayName,
                anyMatch     : true,
                caseSensitive: true
            }]);
            agentCombo.setValue(intAlias);
            }, 200);
            routeBtn.setDisabled(true);
            allBtn.setDisabled(false);
        }
    }
});

var orderStore	= new Ext.data.JsonStore({
	url:'./controllers/get_order.php',
    pruneModifiedRecords: true,
    method:'POST',
	fields: [{name: 'id_product', mapping: 'id_product'},
		     {name: 'grp_name', mapping: 'grp_name'},
             {name: 'alias', mapping: 'alias'},
             {name: 'order1', mapping: 'order1'},
             {name: 'order2', mapping: 'order2'},
             {name: 'order3', mapping: 'order3'},
             {name: 'order4', mapping: 'order4'},
             {name: 'price', mapping: 'price'},
             {name: 'quantity', mapping: 'quantity',type:'int'},
             {name: 'amount', mapping: 'amount',type:'int'},
             {name: 'total', mapping: 'total',type:'float'}],
	root:'rows',
    listeners:{
        'load':function(){
  
            tp.activate(1);
            var allttl1 = orderStore.sum('total').toFixed(2);
            totalLabel.setText(allttl1);
            window.setTimeout(function(){
                orderGrid.getSelectionModel().selectFirstRow();
                orderGrid.getView().focusEl.focus();
            }, 200);
        }
    },
    
});

var grpStore = new Ext.data.JsonStore({
	url:'./controllers/get_groups.php',
    method:'POST',
	fields: [{name: 'grp_name', mapping: 'grp_name'}],
	root:'rows',
});

var ordersStore	= new Ext.data.JsonStore({
	url:'./controllers/get_orders.php',
    method:'POST',
	fields: [{name: 'id_order', mapping: 'id_order'},
		     {name: 'id_amt', mapping: 'id_amt'},
             {name: 'id_agent', mapping: 'id_agent'},
             {name: 'id_client', mapping: 'id_client'},
             {name: 'cln_name', mapping: 'cln_name'},
             {name: 'dt', mapping: 'dt'},
             {name: 'tm', mapping: 'tm'},
             {name: 'total', mapping: 'total'},
             {name: 'comment', mapping: 'comment'},
             {name: 'd_date', mapping: 'd_date'},
             {name: 'state', mapping: 'state'}],
	root:'rows',
    listeners:{
        'load':function(){
			window.setTimeout(function(){
				ordersStore.filter([{
				property     : 'dt',
				value        : fDt,
				anyMatch     : false,
				caseSensitive: true}]);
				ordersCombo.setValue('День');
			},200)
        }
    },
    autoLoad:true,
});

//EOstores==============================================================

//objects===============================================================
var agentCombo = new Ext.form.ComboBox({            
    id:'agentCombo',
    width:150,
    editable:false,
    mode:'local',
    triggerAction: 'all',
    store:agentsStore,
    valueField:'id_agent',
    displayField:'alias',
    listeners:{
        'select':function(p){
            var idAgent = p.getValue();
            newOrderBtn.setDisabled(true);
            if(routeBtn.disabled){
                clientsGrid.getStore().filter([{
                    property     : 'id_agent',
                    value        : idAgent,
                    anyMatch     : true,
                    caseSensitive: true
                },{
                    property     : 'days',
                    value        : dayName,
                    anyMatch     : true,
                    caseSensitive: true
                }]);
            }else{
                clientsGrid.getStore().filter([{
                    property     : 'id_agent',
                    value        : idAgent,
                    anyMatch     : true,
                    caseSensitive: true
                }]);
            
            }
        },
    },
});

var routeBtn = new Ext.Button({
    text:'Маршрут',
    id:'routeBtn',
    disabled:true,
    handler:function(){
        var idAgent;
        if(isNumber(agentCombo.getValue())){
            idAgent = agentCombo.getValue();
        }else{
            idAgent = agentsStore.getAt(0).get('id_agent');
        }
        clientsGrid.getStore().filter([{
            property     : 'id_agent',
            value        : idAgent,
            anyMatch     : true,
            caseSensitive: true
        },{
            property     : 'days',
            value        : dayName,
            anyMatch     : true,
            caseSensitive: true
        }]);
        routeBtn.setDisabled(true);
        allBtn.setDisabled(false);
        newOrderBtn.setDisabled(true);
    },
});

var allBtn = new Ext.Button({
    text:'Все клиенты',
    id:'allBtn',
    disabled:false,
    handler:function(){
        var idAgent;
        if(isNumber(agentCombo.getValue())){
            idAgent = agentCombo.getValue();
        }else{
            idAgent = agentsStore.getAt(0).get('id_agent');
        }
        clientsGrid.getStore().filter([{
            property     : 'id_agent',
            value        : idAgent,
            anyMatch     : true,
            caseSensitive: true
        }]);
        routeBtn.setDisabled(false);
        allBtn.setDisabled(true);
        newOrderBtn.setDisabled(true);
    },
});

var settingsBtn = new Ext.Button({
	scale:'large',
	icon:'./resources/images/settings1.png',
	tooltip: 'настройки',
    handler:show_params,
});

var syncBtn = new Ext.Button({
	scale:'large',
	width:170,
	icon:'./resources/images/sync.png',
	text:'<span style="color:#228CA5; font-weight:bold; font-size:12px;">Синхронизировать</span>',
    handler:startSync,
});

var newOrderBtn = new Ext.Button({
    text:'Новый заказ',
    disabled:true,
    handler:function(){
        var sel = clientsGrid.getSelectionModel().getSelected();
        var idag = sel.get('ida');
        var idcln = sel.get('id_client');
        var clnN = sel.get('alias');
        var idagent = sel.get('id_agent');
        
        clientsGrid.isEdit = true;
        clientsGrid.idag = idag;
        clientsGrid.idcln = idcln;
        clientsGrid.ido = 'Новый заказ';
        clientsGrid.clnN = clnN;
        
        clientLabel.setText(clnN);
        orderLabel.setText('Новый заказ');
        
        delivField.setValue('');
        commentField.setValue('');
        grpCombo.setValue('Все группы');
        orderedCombo.setValue('Весь товар');
        orderStore.reload({
            params:{ida: idag, idc:idcln},
        });
        
        grpStore.reload({
            params:{idagent: idagent},
        });
    
    },
});

var ordersCombo = new Ext.form.ComboBox({
    width:80,
    editable:false,
    mode:'local',
    triggerAction: 'all',
    store:new Ext.data.ArrayStore({
        id: 0,
        fields: ['displayText'],
        data: [['День'], ['Клиент'],['Все']]
    
    }),
    valueField: 'displayText',
    displayField: 'displayText',
    value : 'День',
    listeners:{
        'select':function(){
        var ordersFilter = ordersCombo.getValue();
            if(ordersFilter == 'День'){
				ordersStore.filter([{
					property     : 'dt',
					value        : fDt,
					anyMatch     : false,
					caseSensitive: true
				}]);
			}
			if(ordersFilter == 'Клиент'){
				if(clientsGrid.getSelectionModel().getSelected()== undefined){
					ordersCombo.setValue('День');
				}else{
					var clnF = clientsGrid.getSelectionModel().getSelected().get('id_client');
					ordersStore.filter([{
						property     : 'id_client',
						value        : clnF,
						anyMatch     : false,
						caseSensitive: true
					}]);
				}
			}
			if(ordersFilter == 'Все'){
				ordersStore.filter();
			}
        },
    },

});

var allBtn = new Ext.Button({
    text:'Все клиенты',
    id:'allBtn',
    disabled:false,
    handler:function(){
        var idAgent;
        if(isNumber(agentCombo.getValue())){
            idAgent = agentCombo.getValue();
        }else{
            idAgent = agentsStore.getAt(0).get('id_agent');
        }
        clientsGrid.getStore().filter([{
            property     : 'id_agent',
            value        : idAgent,
            anyMatch     : true,
            caseSensitive: true
        }]);
        routeBtn.setDisabled(false);
        allBtn.setDisabled(true);
        newOrderBtn.setDisabled(true);
    },
});

var acceptBtn = new Ext.Button({
	text:'Прин. к отпр.',
	disabled:true,
	handler:function(){		
		Ext.Ajax.request({
        method: 'POST',
			url:'./controllers/accept_order.php',
			params: {ido: ordersGrid.getSelectionModel().getSelected().get('id_order')},
			success: function(result, request){
				acceptBtn.setDisabled(true);
				ordersStore.reload();
			},
			failure: function(){
        
			}
		});
	}
});

var resendBtn = new Ext.Button({
	text:'Перевыгр.',
	disabled:true,
    handler:function(){		
		Ext.Ajax.request({
        method: 'POST',
			url:'./controllers/resend_order.php',
			params: {ido: ordersGrid.getSelectionModel().getSelected().get('id_order')},
			success: function(result, request){
				acceptBtn.setDisabled(true);
				ordersStore.reload();
			},
			failure: function(){
        
			}
		});
	}
});

var editBtn = new Ext.Button({
	text:'Редактир.',
	disabled:true,
	handler:function(){
		idOr = ordersGrid.getSelectionModel().getSelected().get('id_order');
        ida  = ordersGrid.getSelectionModel().getSelected().get('id_agent');
		clientsGrid.ido = idOr;
		commentField.setValue(ordersGrid.getSelectionModel().getSelected().get('comment'));
		delivField.setValue(ordersGrid.getSelectionModel().getSelected().get('d_date'));
		clientLabel.setText(ordersGrid.getSelectionModel().getSelected().get('cln_name'));
		orderLabel.setText(ordersGrid.getSelectionModel().getSelected().get('id_order'));
		if (ordersGrid.getSelectionModel().getSelected().get('state') != 'сохранен'){
            clientsGrid.isEdit = false;
        }else{
            clientsGrid.isEdit = true;
        }
		orderStore.reload({
			params:{
				ido: idOr
			},
        });
        grpStore.reload({
			params:{
				idagent: ida
			},
        });
	}
});

var delBtn = new Ext.Button({
	text:'Удалить',
	disabled:true,
    handler:function(){		
		Ext.Ajax.request({
        method: 'POST',
			url:'./controllers/delete_order.php',
			params: {ido: ordersGrid.getSelectionModel().getSelected().get('id_order')},
			success: function(result, request){
				acceptBtn.setDisabled(true);
				ordersStore.reload();
			},
			failure: function(){
        
			}
		});
	}
});

var saveBtn = new Ext.Button({
    text:'Сохранить',
    disabled:true,
    handler:function(){
        
        var idp = clientsGrid.ido;
        
        var idag  = clientsGrid.idag;
        var idcln = clientsGrid.idcln;
        var total = orderStore.sum('total');
        var ido   = clientsGrid.ido;
        
        var data  = [];
        var u;
        data.push(total);
        if (clientsGrid.ido == 'Новый заказ'){
            u = './controllers/save_new_order.php';
            data.push(idag);
            data.push(idcln);
        }else{
            u = './controllers/save_order.php';
            data.push(ido);
        }
        
        var rows = [];
        var modArr =  orderStore.getModifiedRecords();
        Ext.each(modArr, function(record){
            var row     = [];
            row.push(record.get('id_product'));
            row.push(record.get('amount'));
            row.push(record.get('price'));
            row.push(record.get('total'));
            rows.push(row);
        });
        data.push(rows);
        
        var d = delivField.getValue();
        var delivDate;
        if (d == ''){
			delivDate = '';
		}else{
			delivDate = d.getFullYear() + '-' + (d.getMonth()+1) + '-' + d.getDate();
		}
        
        var comment   = commentField.getValue();
        
        data.push(delivDate);
        data.push(comment);
        
        Ext.Ajax.request({
            method: 'POST',
            url:u,
            params: {orderstrings: Ext.util.JSON.encode(data)},
            success: function(result, request){
                var res = Ext.util.JSON.decode(result.responseText);
                var idOrdr = res.idorder;
                clientsGrid.ido = idOrdr;
            
                if(idOrdr == 'Новый заказ'){
                    orderStore.reload({
                        params:{ida: idag, idc:idcln},
                    });
                    delivField.setValue('');
                    commentField.setValue('');
                }else{
                    orderStore.reload({
                        params:{ido: idOrdr},
                    });
                }
                saveBtn.setDisabled(true);
                orderLabel.setText('Заказ: ' + idOrdr);
                ordersStore.reload();
            },
            failure: function(){
        
            }
        });
        
    },
});

var delivLabel = new Ext.form.Label({
	text:'Дата доставки:',
});

var delivField = new Ext.form.DateField({
	id:'delivField',
	value:'',
	listeners:{
        change: function(e){   
            var allttl = orderStore.sum('total');
            if ((allttl == 0 && clientsGrid.ido == 'Новый заказ') || !(clientsGrid.isEdit)){
                saveBtn.setDisabled(true);
            }
            else{
                saveBtn.setDisabled(false);
            }
        }
    },
});

var commentField = new Ext.form.TextField({
	emptyText:'Комментарий к заказу',
	width:830,
	listeners:{
        change: function(e){   
            var allttl = orderStore.sum('total');
            if ((allttl == 0 && clientsGrid.ido == 'Новый заказ')|| !(clientsGrid.isEdit)){
                saveBtn.setDisabled(true);
            }
            else{
                saveBtn.setDisabled(false);
            }
        }
    },
});

var jumpField = new Ext.form.TextField({
    emptyText:'Быстрый переход...',
    enableKeyEvents: true,
    width:150,
    listeners:{
        keyup: function(el){ 
            var val = orderStore.find('alias',el.getValue(),0,false,false);
            if (val != -1){
                orderGrid.getView().focusRow(val);
                orderGrid.getSelectionModel().selectRow(val);
                jumpField.focus(false, 10);
            }else{
                orderGrid.getView().focusRow(0);
                orderGrid.getSelectionModel().selectRow(0);
                jumpField.focus(false, 10);
            }
        },
        specialkey: function(el, e){
            if (e.getKey() == e.ENTER) {
                el.reset();
                var idx = orderStore.indexOf(orderGrid.getSelectionModel().getSelected());
                if (idx != -1){
                    orderGrid.getView().focusRow(idx);
                }else{
                    orderGrid.getView().focusRow(0);
                }
            }
        }
    },
});

var grpCombo = new Ext.form.ComboBox({
    width:150,
    editable:false,
    mode:'local',
    triggerAction: 'all',
    store:grpStore,
    valueField: 'grp_name',
    displayField: 'grp_name',
    value : 'Все группы',
    listeners:{
        'select':function(){
        var grpFilter = grpCombo.getValue();
            if (grpFilter != 'Все группы'){
                orderStore.filter('grp_name',grpFilter,true);
            }else{
                orderStore.filter();
            }
            orderGrid.getView().focusRow(0);
            orderGrid.getSelectionModel().selectRow(0);
            orderedCombo.setValue('Весь товар');
        },
    },

});

var orderedCombo = new Ext.form.ComboBox({
    width:100,
    editable:false,
    mode:'local',
    triggerAction: 'all',
    store:new Ext.data.ArrayStore({
        id: 0,
        fields: ['displayText'],
        data: [['Весь товар'], ['Заказанный']]
    
    }),
    valueField: 'displayText',
    displayField: 'displayText',
    value : 'Весь товар',
    listeners:{
        'select':function(){
        var orderedFilter = orderedCombo.getValue();
            if (orderedFilter == 'Заказанный'){
                orderStore.filter({
                    fn:function(record) {
                        return record.get('amount') > 0
                    },
                    scope: this
                });
            }else{
                orderStore.filter();
            }
            orderGrid.getView().focusRow(0);
            orderGrid.getSelectionModel().selectRow(0);
            grpCombo.setValue('Все группы');
        },
    },

});

var clientLabel = new Ext.form.Label({
	text:'Клиент',
});

var orderLabel = new Ext.form.Label({
	text:'Новый заказ',
});

var totalLabel = new Ext.form.Label({
	text:'0.00',
    cls:'total'
});

//----------------

var clientsGrid = new Ext.grid.GridPanel({
    margins:'3 0 3 3',
    region:'center',
    id: 'clientsGrid',
    name: 'licensesGrid',
    store: clientsStore,
    autoExpandColumn: 'address',
    split: true,
    colModel: new Ext.grid.ColumnModel({
		columns:[
            {id:'ida', dataIndex:'ida', header:'Ida', hidden:true},
			{id:'id_agent', dataIndex:'id_agent', header:'Id агент', hidden:true},
			{id:'id_client', dataIndex:'id_client', header:'Id клиент', hidden:true},
			{id:'alias', dataIndex:'alias', header:'Клиент',width:200, sortable:true},
			{id:'address', dataIndex:'address', header:'Адрес'},
			{id:'dbt', dataIndex:'dbt', header:'Просроч.', width:80},
            {id:'days',dataIndex:'days',header:'Визит',sortable:true,width:60},
        ]
    }),
    viewConfig:{
		getRowClass:function(record){
			if(record.get('dbt') == 'да'){
				return 'debtor';
			}
        }
    },
    sm: new Ext.grid.RowSelectionModel({
        singleSelect:true,
        listeners:{
            rowselect:function(){
				newOrderBtn.setDisabled(false);
				if(ordersCombo.getValue()=='Клиент'){
					ordersStore.filter([{
						property     : 'id_client',
						value        : clientsGrid.getSelectionModel().getSelected().get('id_client'),
						anyMatch     : false,
						caseSensitive: true
					}]);
				}
            }
        }
    }),
    tbar:[agentCombo,routeBtn,allBtn, '->', newOrderBtn],
    bbar:[syncBtn,'->',settingsBtn],
});

var orderGrid = new Ext.grid.EditorGridPanel({
    title:'Заказ',
    margins:'3 3 3 3',
    id: 'orderGrid',
    name: 'orderGrid',
    store: orderStore,
    clicksToEdit: 1,
    autoExpandColumn: 'alias',
    colModel: new Ext.grid.ColumnModel({
		columns:[
            {id:'id_product', dataIndex:'id_product', header:'Код',width:50},
			{id:'alias', dataIndex:'alias', header:'Наименование'},
			{id:'grp_name', dataIndex:'grp_name', header:'Группа',width:150},
			{id:'order4', dataIndex:'order4', header:'-4',width:40,renderer:null_val},
            {id:'order3', dataIndex:'order3', header:'-3',width:40,renderer:null_val},
            {id:'order2', dataIndex:'order2', header:'-2',width:40,renderer:null_val},
            {id:'order1', dataIndex:'order1', header:'-1',width:40,renderer:null_val},
            {id:'price', dataIndex:'price', header:'Цена',width:70,renderer:null_val},
            {id:'quantity', dataIndex:'quantity', header:'Ост.',width:50,renderer:null_val},
            {id: 'amount',
                css :'color:#0000B8;',
                dataIndex: 'amount', 
                sortable : true, 
                header:'Заказ',
                width:50, 
                editable:true, 
                editor: {
                    xtype:'numberfield',
                    selectOnFocus:true,
                    allowNegative:false,
                    allowDecimals:false,
                    maxValue:99999,
                    listeners:{
                        show:function(p){
                            mv = orderGrid.getSelectionModel().getSelected().get('quantity')-0;
                            if (mv > 0){
                                p.setMaxValue(mv);
                            }else{
                                p.setMaxValue(99999);
                            }
                        },
                    },
                }
            },
            {id:'total', dataIndex:'total', header:'сумма',width:80,renderer:null_val}
        ],
        isCellEditable:function(colIndex, rowIndex){
            return clientsGrid.isEdit;
        }
    }),
    viewConfig:{
		getRowClass:function(record){
			if((record.get('amount')) > 0 && clientsGrid.isEdit){
                return 'ordered';
            }
            if(record.get('order1') > 0 ||
               record.get('order2') > 0 ||
               record.get('order3') > 0 ||
               record.get('order4') > 0 ){
                return 'bordered';
            }
        }
    },
    sm: new Ext.grid.RowSelectionModel({
        singleSelect:true,
        moveEditorOnEnter:false,
        //listeners:{
        //    rowselect:function(p,n){      
        //        
        //    }
        //}
    }),
    
    tbar:[jumpField,' ',' ',
          grpCombo,' ',' ',
          orderedCombo,' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',
          clientLabel,' ','-',
          orderLabel,'->',
          totalLabel,' ','-',
          saveBtn],
          
    bbar:[delivLabel,' ',delivField,'->',commentField],
    listeners:{
        keydown:function(e){
            //alert(e.getKey());
            if (e.getKey() == e.ENTER){
                var idx = orderStore.indexOf(orderGrid.getSelectionModel().getSelected());
                orderGrid.startEditing(idx,9);
            }
            
            //if (e.getKey() == 1103 || e.getKey() == 122 ){
            if (e.getKey() == 90 || e.getKey() == 1071 ){
                
                jumpField.focus(false, 10);
            }
        },
        afteredit: function(e){
            var ttl = e.record.get('amount') * e.record.get('price');
            e.record.set('total', ttl);
            
            var allttl = orderStore.sum('total');
            totalLabel.setText(allttl.toFixed(2));
            if (allttl == 0 && clientsGrid.ido == 'Новый заказ'){
                saveBtn.setDisabled(true);
            }
            else{
                saveBtn.setDisabled(false);
            }
            //saveBtn.setDisabled(false);
        }
    },
    
});

var ordersGrid = new Ext.grid.GridPanel({
    margins:'3 3 3 0',
    split: true,
    width:500,
    region:'east',
    store: ordersStore,
    autoExpandColumn: 'cln_name',
    split: true,
    colModel: new Ext.grid.ColumnModel({
		columns:[
            {id: 'id_agent', dataIndex: 'id_agent',header:'agent', hidden:true},
            {id:'id_order', dataIndex:'id_order', header:'№', width:40},
            {id:'cln_name', dataIndex:'cln_name', header:'Клиент'},
            {id:'dt', dataIndex:'dt', header:'Дата', width:60},
			{id:'tm', dataIndex:'tm', header:'Время', width:60},
			{id:'total', dataIndex:'total', header:'Сумма', width:50},
			{id:'state', dataIndex:'state', header:'Состояние', width:70},
			{id:'comment', dataIndex:'comment', header:'comment', hidden:true},
			{id:'d_date', dataIndex:'d_date', header:'d_date', hidden:true},
        ]
    }),
    viewConfig:{
		getRowClass:function(record){
            if(record.get('state') == 'прин. к отпр.'){
                return 'accepted';
            }
            if(record.get('state') == 'отправлен'){
                return 'sended';
            }
        
        }
    },
    sm: new Ext.grid.RowSelectionModel({
        singleSelect:true,
        listeners:{
            rowselect:function(p,n){
				var st = p.getSelected().get('state');
				if (st == 'сохранен'){
					editBtn.setText('Редактир.');
					editBtn.setDisabled(false);
					delBtn.setDisabled(false);
					acceptBtn.setDisabled(false);
                    resendBtn.setDisabled(true);
				}else{
                    editBtn.setText('Просмотр');
                    editBtn.setDisabled(false);
                    acceptBtn.setDisabled(true);
                    if (st == 'отправлен'){
                        delBtn.setDisabled(true);
                        resendBtn.setDisabled(false);
                    }else{
                        resendBtn.setDisabled(true);
                        delBtn.setDisabled(false);
                    }
                }
            }
        }
    }),
    tbar:[ordersCombo,acceptBtn,resendBtn,editBtn,'->',delBtn],
});

var routeTab = new Ext.Container({
    title:'Маршрут',
    layout:'border',
    items:[clientsGrid,ordersGrid],
});

var orderTab = new Ext.Container({
    title:'Заказ',
});

//EOobjects============================================================
function isNumber (o) {
  return ! isNaN (o-0);
}

function null_val(value, metaData, record, rowIndex, colIndex,store) {
    if (value == 0){
        return '<span style="color:gray;">' + value + '</span>';
    }else {
        return value;
    }
}

function show_params(){
    var license;
    var address;
    var port;
    
    var licenseNameField = new Ext.form.TextField({
        fieldLabel: 'Имя лицензии',
        allowBlank:false,
        id:'license',
    });
    
    var addressField = new Ext.form.TextField({
        fieldLabel: 'IP адрес',
        allowBlank:false,
        id:'address',
    });
    
    var portField = new Ext.form.TextField({
        fieldLabel: 'Порт',
        allowBlank:false,
        id:'port',
    });
    
    var paramsForm = new Ext.FormPanel({
		bodyStyle: 'padding:15px',
		labelWidth: 110,
		url:'./controllers/set_params.php',
		labelAlign:'right',
		monitorValid:true,
		frame:true,
		items:[licenseNameField,addressField,portField],
		buttons:[{
			id:'btnSubmit',
			text:'Сохранить',
			formBind: true,	 
			type: 'submit',
			handler:submit_params
		}],
		keys: [{
            key: [Ext.EventObject.ENTER], 
            handler: submit_params,
        }],
        listeners:{
			
        },
	});
    
    Ext.Ajax.request({
        url:"./controllers/get_params.php",
        method:'POST',
        success:function(result, request){
            res = Ext.util.JSON.decode(result.responseText);
            license = res.license;
            address = res.address;
            port = res.port;
            
            licenseNameField.setValue(license);
            addressField.setValue(address);
            portField.setValue(port);
            
        },
    });
    
    
    function submit_params(){
       
       var params = [];
        
        if(licenseNameField.getValue() != license){
            params.push(licenseNameField.getValue());
        }else{
            params.push('none');
        }
        
        if(addressField.getValue() != address){
            params.push(addressField.getValue());
        }else{
            params.push('none');
        }
        
        if(portField.getValue() != port){
            params.push(portField.getValue());
        }else{
            params.push('none');
        }
        
        Ext.Ajax.request({
            method: 'POST',
            url:'./controllers/set_params.php',
            params: {paramets: Ext.util.JSON.encode(params)},
            success: function(result, request){
                paramsWindow.close();
            },
            failure: function(){
                if(action.failureType == 'server'){ 
                    obj = Ext.util.JSON.decode(action.response.responseText); 
                    Ext.Msg.alert('Параметры не сохранены:', obj.errors.reason); 
                }else{ 
                    Ext.Msg.alert('Внимание!','Ошибка сервера:' + action.response.responseText); 
                }
                paramsWindow.close();
            }
        });
    }
    
    var paramsWindow = new Ext.Window({
		items:paramsForm,
		title:'Параметры',
		modal:true,
		border: false,
		layout:'fit',
		plain: true,
		width:340,
        height:180,
	});
    
    paramsWindow.show();
}

//======

function startSync(){
    var syncWindow = new Ext.Window({
        id:'syncWindow',
        title:'Синхронизация с сервером...',
        height:300,
        width:500,
        modal:true,
        closable:false,
        innerText:'Проверка соединения с интернет...</br>Начало синхронизации</br>',
        progress: 0,
        items:[{
            xtype:'panel',
            border:false,
            padding:'15 15 0 15',
            items:[{
                id:'syncProgress',
                animate:true,
                xtype:'progress',
                text:'0 из 9',
                cls:'left-align',
            }]
        },
        {
            xtype:'panel',
            autoScroll:true,
            height:211,
            border:false,
            padding:'15 10 10 15',
            items:[{
                id:'syncLabel',
                xtype:'label',
                html:'Проверка соединения с интернет...</br>Начало синхронизации</br>',
            }]
        }],
        bbar: new Ext.Toolbar({
            items:[
                '->',
            {
                id:'syncOkButton',
                text:'Ok',
                width:70,
                disabled:true,
                handler: function(){
                    syncWindow.close();
                }
            }]
        }),
    });
    syncWindow.show();
    testconnection();
}
 
function testconnection(){
    Ext.Ajax.request({
        method: 'GET',
        url:'./controllers/sync_client.php',
        params: {'stage': 'testconnection'},
        success: function(result, request){
            var jsonResult = Ext.util.JSON.decode(result.responseText);
            var res = jsonResult.success;
            var reason = jsonResult.reason;
            insertlog(reason);
            if(res){
                testorders();
            }
            else{
                errorhandler();
            }
        },
        failure: function(){
			insertlog('Ошибка приложения, обратитесь к разработчику!');
        }
    });
};

function testorders(){
    insertlog('Проверка заказов...');
    pbar(1);
    Ext.Ajax.request({
        method: 'GET',
        url:'./controllers/sync_client.php',
        params: {'stage': 'testorders'},
        success: function(result, request){
            var jsonResult = Ext.util.JSON.decode(result.responseText);
            var res = jsonResult.success;
            var orders = jsonResult.orders;
            var reason = jsonResult.reason;
            insertlog(reason);
            if(res){
                if (orders == 'presist'){
                    createfile();
                }
                else {
                    takechanges();
                } 
            }
            else{
                errorhandler();
            }
        },
        failure: function(){
			insertlog('Ошибка приложения, обратитесь к разработчику!');
        }
    });
};

function createfile(){
    insertlog('Выгрузка заказов...');
    pbar(2);
    
    Ext.Ajax.request({
        method: 'GET',
        url:'./controllers/sync_client.php',
        params: {'stage': 'createfile'},
        success: function(result, request){
            var jsonResult = Ext.util.JSON.decode(result.responseText);
            var res = jsonResult.success;
            var reason = jsonResult.reason;
            insertlog(reason);
            if(res){
                sendorders();
            }
            else{
                errorhandler();
            }
        },
        failure: function(){
			insertlog('Ошибка приложения, обратитесь к разработчику!');
        }
    });
}

function sendorders(){
    insertlog('Отправка заказов...');
    pbar(3);
    Ext.Ajax.request({
        method: 'GET',
        url:'./controllers/sync_client.php',
        params: {'stage': 'sendorders'},
        success: function(result, request){
            var jsonResult = Ext.util.JSON.decode(result.responseText);
            var res = jsonResult.success;
            var reason = jsonResult.reason;
            insertlog(reason);
            if(res){
                takechanges();
            }
            else{
                errorhandler();
            }
        },
        failure: function(){
			insertlog('Ошибка приложения, обратитесь к разработчику!');
        }
    });
}

function takechanges(){
    insertlog('Получение обновлний...');
    pbar(4);
    Ext.Ajax.request({
        method: 'GET',
        url:'./controllers/sync_client.php',
        params: {'stage': 'getchanges'},
        success: function(result, request){
            var jsonResult = Ext.util.JSON.decode(result.responseText);
            var res = jsonResult.success;
            var reason = jsonResult.reason;
            insertlog(reason);
            if(res){
                pbar(5);
                insertlog('Синхронизация успешно завершена');
                Ext.getCmp('syncOkButton').setDisabled(false);
                Ext.getCmp('syncWindow').setTitle('Успех!');
                clientsStore.reload();
                agentsStore.reload();
                ordersStore.reload();
            }
            else{
                errorhandler();
            }
        },
        failure: function(){
			insertlog('Ошибка приложения, обратитесь к разработчику!');
        }
    });
}

function errorhandler(){
    insertlog('Синхронизация завершилась неуспешно, повторите попытку позже!');
    Ext.getCmp('syncOkButton').setDisabled(false);
    Ext.getCmp('syncWindow').setTitle('Неудача!');
}

function insertlog(text){
    var txt =  Ext.getCmp('syncWindow').innerText;
    txt = text + '</br>' + txt;
    Ext.getCmp('syncLabel').setText(txt, false);
    Ext.getCmp('syncWindow').innerText = txt;
}

function pbar(stage){
    var pg = stage/5;
    Ext.getCmp('syncProgress').updateProgress(pg, stage + ' из 5');
}
//сборка================================================================

var tp = new Ext.TabPanel({
    autoScroll: true,
    activeTab:0,
    items:[routeTab,orderGrid]
});

var vp = new Ext.Viewport({
    layout:'fit',
    items:tp
});

//EOсборка=============================================================

