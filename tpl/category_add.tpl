<ul class="errors" tal:condition="errors">
    <li><h2>Wystąpiły następujące błędy:</h2></li>
    <li tal:repeat="error errors">${error}</li>
</ul>

<form action="" method="post">
    <label for="cat_name">Nazwa kategorii</label>
    <input type="text" name="cat_name" id="cat_name" value="${category/cat_name}" />
    <label for="cat_status">Status</label>
    <select name="cat_status" id="cat_status">
        <option value="enabled" tal:attributes="selected category/cat_enabled">włączony</option>
        <option value="disabled" tal:attributes="selected not:category/cat_enabled">wyłączony</option>
    </select>

    <hr />
    <input type="submit" name="sub_save" value="zapisz" />

    <input type="hidden" name="b" value="${b}" />
</form>

