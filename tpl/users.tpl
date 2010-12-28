<table class="users_table">
    <thead>
        <tr>
            <th width="40"><a href="/?module=users&amp;order=user_id&amp;sort=${sort}">ID</a></th>
            <th><a href="/?module=users&amp;order=user_login&amp;sort=${sort}">Login</a></th>
            <th width="200"><a href="/?module=users&amp;order=user_name&amp;sort=${sort}">Nazwa</a></th>
            <th width="140"><a href="/?module=users&amp;order=user_date_add&amp;sort=${sort}">Data dodania</a></th>
            <th width="120"><a href="/?module=users&amp;order=user_role&amp;sort=${sort}">Uprawnienia</a></th>
            <th width="100"><a href="/?module=users&amp;order=user_status&amp;sort=${sort}">Status</a></th>
            <th width="60">Edytuj</th>
            <th width="60">Usuń</th>
        </tr>
    </thead>
    <tbody>
        <tr tal:repeat="user users" tal:condition="users" tal:attributes="class css-odd:repeat/user/odd">
            <td align="right">${user/user_id}</td>
            <td>${user/user_login}</td>
            <td>${user/user_name}</td>
            <td align="center">${date-format:user/user_date_add}</td>
            <td align="right">${user/user_role}</td>
            <td align="center">${user/user_status}</td>
            <td align="center"><a href="/?module=user_edit&amp;user_id=${user/user_id}&amp;b=${b}">edytuj</a></td>

            <td align="center" tal:condition="not:user/user_del_deny"><a href="/?module=user_del&amp;user_id=${user/user_id}&amp;b=${b}" onclick="return confirm ('Czy jesteś pewien że chcesz usunąć użytkownika \'${user/user_name}\'? Ta operacja jest nieodwracalna!')">usuń</a></td>
            <td align="center" tal:condition="user/user_del_deny">usuń</td>
        </tr>
    </tbody>
</table>
