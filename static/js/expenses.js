function exp_focus (fields_list) {
    if (fields_list && fields_list.length > 0) {
        var i, field, force;
        for (i=0; i<fields_list.length; ++i) {
            if (fields_list[i].substr (0, 1) == '!') {
                force = true;
                fields_list[i] = fields_list[i].substring (1)
            }
            else {
                force = false;
            }

            if ((field = $(fields_list[i])) && (force || field.val () == '')) {
                field.focus ();
                break;
            }
        }
    }
}

function set_check (value, fields) {
    var i=0;

    for (i=0; i<fields.length; ++i) {
        if (fields[i].type == 'checkbox') {
            fields[i].checked = value;
        }
    }
}

function exp_init () {
};

$(exp_init);
