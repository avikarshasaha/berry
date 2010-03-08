settings = {
    onShiftEnter: {
        keepDefault: false,
        replaceWith: '\n'
    },

    onCtrlEnter: {
        keepDefault: false,
        openWith: '[p]\n    ',
        closeWith: '\n[/p]\n'
    },

    onTab: {
        keepDefault: false,
        replaceWith: '    '
    },

    markupSet: [
        {
            name: 'Полужирный',
            className: 'bold',
            openWith: '[b]',
            closeWith: '[/b]'
        },

        {
            name: 'Наклонный',
            className: 'italic',
            openWith: '[i]',
            closeWith: '[/i]'
        },

        {
            name: 'Подчёркнутый',
            className: 'underline',
            openWith: '[u]',
            closeWith: '[/u]'
        },

        {
            name: 'Зачёркнутый',
            className: 'strike',
            openWith: '[s]',
            closeWith: '[/s]'
        },

        {
            separator: '---------------'
        },

        {
            name: 'Изображение',
            className: 'image',
            openWith: '[img=[![Ссылка:!:]!]]',
            closeWith: '[/img]'
        },

        {
            name: 'Ссылка',
            className: 'link',
            openWith: '[url=[![Ссылка:!:]!]]',
            closeWith: '[/url]'
        },

        {
            separator: '---------------'
        },

        {
            name: 'По левому краю',
            className: 'align_left',
            openWith: '[left]',
            closeWith: '[/left]'
        },

        {
            name: 'По центру',
            className: 'align_center',
            openWith: '[center]',
            closeWith: '[/center]'
        },

        {
            name: 'По правому краю',
            className: 'align_right',
            openWith: '[right]',
            closeWith: '[/right]'
        },

        {
            separator: '---------------'
        },

        {
            name: 'Абзац',
            className: 'paragraph',
            openWith: '[p]',
            closeWith: '[/p]'
        },

        {
            name: 'Цитата',
            className: 'quote',
            openWith: '[quote]',
            closeWith: '[/quote]'
        },

        {
            name: 'Список',
            className: 'list_bullets',
            replaceWith: function(markitup){
            	var array = markitup.selection.split('\n');
            	var string = '';

                for (var i = 0; i < array.length; i++){
                    var strip = array[i].strip('\n');

                    if (strip)
                	    string += '    [li]' + strip + '[/li]\n';
                }

                return '[ul]\n' + string + '[/ul]';
            }
        },

        {
            name: 'Цифровой список',
            className: 'list_numbers',
            replaceWith: function(markitup){            	var array = markitup.selection.split('\n');
            	var string = '';
                for (var i = 0; i < array.length; i++){
                    var strip = array[i].strip('\n');

                    if (strip)
                	    string += '    [li]' + strip + '[/li]\n';
                }

                return '[ol]\n' + string + '[/ol]';
            }
        },

        {
            name: 'Код',
            className: 'code',
            openWith: '[code]',
            closeWith: '[/code]'
        },

        {
            separator: '---------------'
        },

        {
            name: 'Очистить',
            className: 'clean',
            replaceWith: function(markitup){
                return markitup.selection.replace(/\[(.*?)\]/g, '')
            }
        }
    ]
}