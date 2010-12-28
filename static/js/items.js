function items_calculate () {
    var i, sum, value, items;

    items = $('input[name=item_id]');

    sum = 0;
    for (i=0; i<items.length; ++i) {
        if (!items[i].checked) {
            continue;
        }

        value = $('#item_' + items[i].value + '_value').html ();
        if ((value = value.match (/^(\d+\.\d\d)/))) {
            sum += parseFloat (value[1]);
        }
    }

    $('#expense_sum').html (Math.round (sum * 100) / 100);
}

function items_select_all () {
    set_check (this.checked, $('.items_table input[name=item_id]'))
    items_calculate ();
}

function items_init () {
    var i, items;

    items = $('input[name=item_id]');
    for (i=0; i<items.length; ++i) {
        $(items[i]).change (items_calculate);
    }

    items_calculate ();

    $('#items_select_all').click (items_select_all);
}

$(items_init);
