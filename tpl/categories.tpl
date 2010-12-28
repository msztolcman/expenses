<table class="categories_table">
    <thead>
        <tr>
            <th width="40"><a href="/?module=categories&amp;order=cat_id&amp;sort=${sort}">ID</a></th>
            <th><a href="/?module=categories&amp;order=cat_name&amp;sort=${sort}">Nazwa kategorii</a></th>
            <th width="60"><a href="/?module=categories&amp;order=cat_status&amp;sort=${sort}">Status</a></th>
            <th width="60">Edytuj</th>
            <th width="60">Usuń</th>
        </tr>
    </thead>
    <tbody>
        <tr tal:repeat="category categories" tal:condition="categories" tal:attributes="class css-odd:repeat/category/odd">
            <td align="right">${category/cat_id}</td>
            <td>${category/cat_name}</td>
            <td align="center">${category/cat_status}</td>

            <td align="center" tal:condition="permissions/CategoryEdit"><a href="/?module=category_edit&amp;cat_id=${category/cat_id}&amp;b=${b}">edytuj</a></td>
            <td align="center" tal:condition="not:permissions/CategoryEdit">edytuj</td>

            <td align="center" tal:condition="php: !category['cat_del_deny'] AND permissions['CategoryDel']"><a href="/?module=category_del&amp;cat_id=${category/cat_id}&amp;b=${b}" onclick="return confirm ('Czy jesteś pewien że chcesz usunąć kategorię \'${category/cat_name}\'? Ta operacja jest nieodwracalna!')">usuń</a></td>
            <td align="center" tal:condition="php: category['cat_del_deny'] OR !permissions['CategoryDel']">usuń</td>
        </tr>
    </tbody>
</table>
