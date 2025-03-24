(function() {
    tinymce.create('tinymce.plugins.NBDesigner', {
        init : function(ed, url) {
            ed.addButton('nbdesigner', {
                title : 'NBDesigner',
                cmd : 'nbdesigner',
                image : url + '/../../images/icon.png'
            });

            ed.addCommand('nbdesigner', function() {
                // 添加编辑器命令处理
                var selected_text = ed.selection.getContent();
                var return_text = '';
                
                // 处理选中的文本
                if(selected_text != '') {
                    return_text = '[nbdesigner]' + selected_text + '[/nbdesigner]';
                } else {
                    return_text = '[nbdesigner][/nbdesigner]';
                }
                
                ed.execCommand('mceInsertContent', 0, return_text);
            });
        },
        
        createControl : function(n, cm) {
            return null;
        }
    });

    tinymce.PluginManager.add('nbdesigner', tinymce.plugins.NBDesigner);
})(); 