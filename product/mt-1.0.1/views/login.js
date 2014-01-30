var login = new Ext.FormPanel({ 
        id:'login',
        name:'login',
        labelWidth:80,
        url:'./controllers/login.php', 
        frame:true, 
        title:'Пожалуйста, введите имя пользователя и пароль', 
        defaultType:'textfield',
	    monitorValid:true,
        items:[{ 
                fieldLabel:'Пользователь', 
                name:'loginUsername', 
                allowBlank:false 
            },{ 
                fieldLabel:'Пароль', 
                name:'loginPassword', 
                inputType:'password', 
                allowBlank:false 
            }],
        
        keys: [{
            key: [Ext.EventObject.ENTER], 
            handler: submit_login
        }],
 
        buttons:[{ 
                id:'btnSubmit',
                text:'Войти',
                formBind: true,	 
                type: 'submit',
                handler: submit_login
        }] 
    });
 function submit_login(){
    login.getForm().submit({ 
        method:'POST', 
        waitTitle:'Соединеняемся', 
        waitMsg:'Отсылаем данные...',
        success:function(){ 
            var redirect = 'index.php'; 
                window.location = redirect;
        },
        failure:function(form, action){ 
            if(action.failureType == 'server'){ 
                obj = Ext.util.JSON.decode(action.response.responseText); 
                Ext.Msg.alert('Вход не выполнен : ', obj.errors.reason); 
            }else{ 
                Ext.Msg.alert('Внимание!', 'Ошибка сервера : ' + action.response.responseText); 
            } 
            login.getForm().reset(); 
        } 
    });
}
    var win = new Ext.Window({
        layout:'fit',
        width:300,
        height:150,
        closable: false,
        resizable: false,
        plain: true,
        border: false,
        items: [login],
        modal: true
	});
    
	win.show();
