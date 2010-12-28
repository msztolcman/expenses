<form method="get" action="">
    <table class="items_filter_table">
        <tfoot>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Zmień" />
                </td>
            </tr>
        </tfoot>
        <tbody>
            <tr>
                <td>
                    <label for="date_start">Data początkowa:</label>
                    <input type="text" name="date_start" id="date_start" value="${date_start}" />
                </td>
                <td>
                    <label for="date_end">Data końcowa (nie jest wliczana):</label>
                    <input type="text" name="date_end" id="date_end" value="${date_end}" />
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label for="categories">Wliczaj kategorie:</label>
                    <select name="categories[]" id="categories" multiple="multiple">
                        <option tal:repeat="category categories" value="${category/cat_id}" tal:attributes="selected category/cat_selected">${category/cat_name}</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label for="search">Szukaj w nazwie:</label>
                    <input type="text" name="search" id="search" value="${search}" />
                </td>
            </tr>
        </tbody>
    </table>

    <input type="hidden" name="module" value="items" />
    <input type="hidden" name="order" value="${current_order}" />
    <input type="hidden" name="sort" value="${sort}" />
</form>
<table class="items_table">
    <thead>
        <tr>
            <th width="20"><input type="checkbox" id="items_select_all" checked="checked" /></th>
            <th width="40"><a href="/?${uri_data:POST;GET;[sort=$sort,date_start=$date_start,date_end=$date_end,module=items,order=item_id]}">ID</a></th>
            <th><a href="/?${uri_data:POST;GET;[module=items,order=item_name,sort=$sort,date_start=$date_start,date_end=$date_end]}">Produkt</a></th>
            <th width="200"><a href="/?${uri_data:POST;GET;[module=items,order=cat_name,sort=$sort,date_start=$date_start,date_end=$date_end]}">Kategoria</a></th>
            <th width="100">Ilość</th>
            <th width="120"><a href="/?${uri_data:POST;GET;[module=items,order=item_value,sort=$sort,date_start=$date_start,date_end=$date_end]}">Wartość</a></th>
            <th width="100"><a href="/?${uri_data:POST;GET;[module=items,order=item_date_buy,sort=$sort,date_start=$date_start,date_end=$date_end]}">Data zakupu</a></th>
            <th width="60">Edytuj</th>
            <th width="60">Usuń</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan="8">Wartość zakupów z zakresu dat między ${date_start} a ${date_end}: <span id="expense_sum">${value}</span> zł</td>
        </tr>
    </tfoot>
    <tbody>
        <tr tal:repeat="item items" tal:condition="items" tal:attributes="class css-odd:repeat/item/odd" id="item_${item/item_id}">
            <td align="center"><input type="checkbox" name="item_id" value="${item/item_id}" checked="checked" /></td>
            <td align="right">${item/item_id}</td>
            <td align="left">${item/item_name}</td>
            <td align="left">${item/cat_name}</td>
            <td align="right">${item/item_quant} ${item/item_quant_unit}</td>
            <td align="right" id="item_${item/item_id}_value">${item/item_value} zł</td>
            <td align="center">${item/item_date_buy}</td>

            <td align="center" tal:condition="permissions/ItemEdit"><a href="/?module=item_edit&amp;item_id=${item/item_id}">edytuj</a></td>
            <td align="center" tal:condition="not:permissions/ItemEdit">edytuj</td>

            <td align="center" tal:condition="permissions/ItemDel"><a href="/?module=item_del&amp;item_id=${item/item_id}" onclick="return confirm ('Czy jesteś pewien że chcesz usunąć produkt \'${item/item_name}\'? Ta operacja jest nieodwracalna!')">usuń</a></td>
            <td align="center" tal:condition="not:permissions/ItemDel">usuń</td>
        </tr>
    </tbody>
</table>
