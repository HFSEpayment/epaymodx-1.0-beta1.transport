Ext.onReady(function () {
    var cfg = window.UniversalEpayConfig || {};
    var connector = cfg.connectorUrl;

    function request(action, data, callback) {
        data = data || {};
        data.action = action;
        Ext.Ajax.request({
            url: connector,
            method: 'POST',
            params: data,
            success: function (response) {
                var result;
                try { result = Ext.decode(response.responseText); }
                catch (e) { result = {success:false, message:'Некорректный ответ сервера'}; }
                callback(result);
            },
            failure: function () {
                callback({success:false, message:'Ошибка соединения с сервером'});
            }
        });
    }

    request('get', {}, function (r) {
        if (!r.success) {
            Ext.Msg.alert('UniversalEpay', r.message || 'Не удалось загрузить настройки');
            return;
        }

        var v = r.object || {};

        var form = new Ext.form.FormPanel({
            border: false,
            bodyStyle: 'padding:20px',
            labelWidth: 190,
            autoScroll: true,
            items: [
                {xtype:'displayfield', value:'<h2>UniversalEpay — Halyk ePay</h2><p>Введите реквизиты один раз. Они используются способом оплаты MiniShop3.</p>'},
                {xtype:'fieldset', title:'Режим', items:[
                    {xtype:'combo', fieldLabel:'Режим', name:'test_mode', hiddenName:'test_mode',
                     mode:'local', triggerAction:'all', editable:false,
                     store:[[1,'Тестовый'],[0,'Боевой']], value: v.test_mode ? 1 : 0}
                ]},
                {xtype:'fieldset', title:'Тестовый Halyk', items:[
                    {xtype:'textfield', fieldLabel:'Client ID', name:'test_client_id', value:v.test_client_id || '', anchor:'100%'},
                    {xtype:'textfield', fieldLabel:'Client Secret', name:'test_client_secret', value:v.test_client_secret || '', inputType:'password', anchor:'100%'},
                    {xtype:'textfield', fieldLabel:'Terminal ID', name:'test_terminal', value:v.test_terminal || '', anchor:'100%'}
                ]},
                {xtype:'fieldset', title:'Боевой Halyk', items:[
                    {xtype:'textfield', fieldLabel:'Client ID', name:'prod_client_id', value:v.prod_client_id || '', anchor:'100%'},
                    {xtype:'textfield', fieldLabel:'Client Secret', name:'prod_client_secret', value:v.prod_client_secret || '', inputType:'password', anchor:'100%'},
                    {xtype:'textfield', fieldLabel:'Terminal ID', name:'prod_terminal', value:v.prod_terminal || '', anchor:'100%'}
                ]},
                {xtype:'fieldset', title:'Общие настройки', items:[
                    {xtype:'textfield', fieldLabel:'Валюта', name:'currency', value:v.currency || 'KZT', anchor:'100%'},
                    {xtype:'combo', fieldLabel:'Язык', name:'language', hiddenName:'language',
                     mode:'local', triggerAction:'all', editable:false,
                     store:[['rus','Русский'],['kaz','Қазақша'],['eng','English']], value:v.language || 'rus'},
                    {xtype:'numberfield', fieldLabel:'Статус успешной оплаты', name:'paid_status', value:v.paid_status || 3, allowDecimals:false},
                    {xtype:'numberfield', fieldLabel:'Статус неуспешной оплаты', name:'failed_status', value:v.failed_status || 4, allowDecimals:false}
                ]}
            ],
            buttons: [{
                text:'Сохранить',
                handler:function () {
                    var values = form.getForm().getValues();
                    request('save', values, function (r) {
                        if (r.success) {
                            Ext.Msg.alert('UniversalEpay', 'Настройки сохранены.');
                        } else {
                            Ext.Msg.alert('UniversalEpay', r.message || 'Не удалось сохранить настройки.');
                        }
                    });
                }
            }]
        });

        form.render('universalepay-panel-home-div');
    });
});