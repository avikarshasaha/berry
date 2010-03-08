settings = {
    onShiftEnter: {
        keepDefault: false,
        replaceWith: '<br />\n'
    },

    onCtrlEnter: {
        keepDefault: false,
        openWith: '<p>\n    ',
        closeWith: '\n</p>\n'
    },

    onTab: {
        keepDefault: false,
        replaceWith: '    '
    },

    markupSet: [
        {
            name: 'Полужирный',
            className: 'bold',
            openWith: '<strong>',
            closeWith: '</strong>'
        },

        {
            name: 'Наклонный',
            className: 'italic',
            openWith: '<em>',
            closeWith: '</em>'
        },

        {
            name: 'Подчёркнутый',
            className: 'underline',
            openWith: '<u>',
            closeWith: '</u>'
        },

        {
            name: 'Зачёркнутый',
            className: 'strike',
            openWith: '<strike>',
            closeWith: '</strike>'
        },

        {
            separator: '---------------'
        },

        {
            name: 'Мелкий шриФт',
            className: 'small',
            openWith: '<small>',
            closeWith: '</small>'
        },

        {
            name: 'Под текстом',
            className: 'sub',
            openWith: '<sub>',
            closeWith: '</sub>'
        },

        {
            name: 'Над текстом',
            className: 'sup',
            openWith: '<sup>',
            closeWith: '</sup>'
        },

        {
            separator: '---------------'
        },

        {
            name: 'Изображение',
            className: 'image',
            openWith: '<img src="[![Ссылка:!:]!]" alt="" border="0" />',
            closeWith: ''
        },

        {
            name: 'Ссылка',
            className: 'link',
            openWith: '<a href="[![Ссылка:!:]!]">',
            closeWith: '</a>'
        },

        {
            separator: '---------------'
        },

        {
            name: 'По левому краю',
            className: 'align_left',
            openWith: '<p align="left">',
            closeWith: '</p>'
        },

        {
            name: 'По центру',
            className: 'align_center',
            openWith: '<p align="center">',
            closeWith: '</p>'
        },

        {
            name: 'По правому краю',
            className: 'align_right',
            openWith: '<p align="right">',
            closeWith: '</p>'
        },

        {
            name: 'По обоим краям',
            className: 'align_justify',
            openWith: '<p align="justify">',
            closeWith: '</p>'
        },

        {
            separator: '---------------'
        },

        {
            name: 'Абзац',
            className: 'paragraph',
            openWith: '<p>',
            closeWith: '</p>'
        },

        {
            name: 'Цитата',
            className: 'quote',
            openWith: '<quote cite="отсюда [![Откуда:!:]!]">',
            closeWith: '</quote>'
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
                	    string += '    <li>' + strip + '</li>\n';
                }

                return '<ul>\n' + string + '</ul>';
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
                	    string += '    <li>' + strip + '</li>\n';
                }

                return '<ol>\n' + string + '</ol>';
            }
        },

        {
            name: 'Код',
            className: 'code',
            openWith: '<code pre="pre" escape="escape">',
            closeWith: '</code>'
        },

        {
            separator: '---------------'
        },

        {
            name: 'Очистить',
            className: 'clean',
            replaceWith: function(markitup){
                return markitup.selection.replace(/<(.*?)>/g, '')
            }
        }
    ]
}