settings = {
    onShiftEnter: {
        keepDefault: false,
        replaceWith: '\n'
    },

    onCtrlEnter: {
        keepDefault: false,
        openWith: 'p. ',
        closeWith: ''
    },

    onTab: {
        keepDefault: false,
        replaceWith: '    '
    },

    markupSet: [
        {
            name: 'Полужирный',
            className: 'bold',
            openWith: '*',
            closeWith: '*'
        },

        {
            name: 'Наклонный',
            className: 'italic',
            openWith: '_',
            closeWith: '_'
        },

        {
            name: 'Подчёркнутый',
            className: 'underline',
            openWith: '+',
            closeWith: '+'
        },

        {
            name: 'Зачёркнутый',
            className: 'strike',
            openWith: '-',
            closeWith: '-'
        },

        {
            separator: '---------------'
        },

        {
            name: 'Под текстом',
            className: 'sub',
            openWith: '~',
            closeWith: '~'
        },

        {
            name: 'Над текстом',
            className: 'sup',
            openWith: '^',
            closeWith: '^'
        },

        {
            separator: '---------------'
        },

        {
            name: 'Изображение',
            className: 'image',
            openWith: '![![Ссылка:!:]!]!',
            closeWith: ''
        },

        {
            name: 'Ссылка',
            className: 'link',
            openWith: '"',
            closeWith: '":[![Ссылка:!:]!]'
        },

        {
            separator: '---------------'
        },

        {
            name: 'По левому краю',
            className: 'align_left',
            openWith: 'p<. ',
            closeWith: ''
        },

        {
            name: 'По центру',
            className: 'align_center',
            openWith: 'p=. ',
            closeWith: ''
        },

        {
            name: 'По правому краю',
            className: 'align_right',
            openWith: 'p>. ',
            closeWith: ''
        },

        {
            name: 'По обоим краям',
            className: 'align_justify',
            openWith: 'p<>. ',
            closeWith: ''
        },

        {
            separator: '---------------'
        },

        {
            name: 'Абзац',
            className: 'paragraph',
            openWith: 'p. ',
            closeWith: ''
        },

        {
            name: 'Цитата',
            className: 'quote',
            openWith: 'bq. ',
            closeWith: ''
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
                	    string += '* ' + strip + '\n';
                }

                return string;
            }
        },

        {
            name: 'Цифровой список',
            className: 'list_numbers',
            replaceWith: function(markitup){
            	var array = markitup.selection.split('\n');
            	var string = '';

                for (var i = 0; i < array.length; i++){
                    var strip = array[i].strip('\n');

                    if (strip)
                	    string += '# ' + strip + '\n';
                }

                return string;
            }
        },

        {
            name: 'Код',
            className: 'code',
            openWith: '<code pre="pre" escape="escape">',
            closeWith: '</code>'
        }
    ]
}