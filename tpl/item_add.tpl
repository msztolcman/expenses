<ul class="errors" tal:condition="errors">
    <li><h2>Wystąpiły następujące błędy:</h2></li>
    <li tal:repeat="error errors">${error}</li>
</ul>

<form action="" method="post">
    <label for="item_name">Nazwa produktu</label>
    <input type="text" name="item_name" id="item_name" value="${item/item_name}" />

    <label for="cat_id">Kategoria</label>
    <select name="cat_id" id="cat_id">
        <option
            tal:condition="categories"
            tal:repeat="category categories"
            tal:attributes="value category/cat_id; selected php: category['cat_id'] == item.cat_id"
            tal:content="category/cat_name"
         />
    </select>

    <label for="item_note">Dodatkowy opis</label>
    <textarea name="item_note" id="item_note">${item/item_note}</textarea>

    <label for="item_quant">Ilość</label>
    <input type="text" name="item_quant" id="item_quant" value="${item/item_quant}" />

    <label for="item_quant_unit">Jednostka</label>
    <select name="item_quant_unit" id="item_quant_unit">
        <option
            tal:condition="quant_units"
            tal:repeat="unit quant_units"
            tal:attributes="value unit; selected php: unit == item.item_quant_unit"
            tal:content="unit"
         />
    </select>

    <label for="item_value">Wartość</label>
    <input type="text" name="item_value" id="item_value" value="${item/item_value}" />

    <label for="item_date_buy">Data zakupu</label>
    <input type="text" name="item_date_buy" id="item_date_buy" value="${item/item_date_buy}" />

    <hr />
    <input type="submit" name="sub_save" value="zapisz" />
</form>

