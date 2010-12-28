<ul class="errors" tal:condition="errors">
    <li><h2>Wystąpiły następujące błędy:</h2></li>
    <li tal:repeat="error errors">${error}</li>
</ul>

<form action="" method="post">
    <tal:block tal:condition="mode_new">
        <label for="user_login">Login</label>
        <input type="text" name="user_login" id="user_login" value="${user_data/user_login}" />
    </tal:block>
    <input type="hidden" name="user_login" value="{$user_data/user_login}" tal:condition="not:mode_new" />

    <label for="user_name">Nazwa</label>
    <input type="text" name="user_name" id="user_name" value="${user_data/user_name}" />
    <label for="user_email">Email</label>
    <input type="text" name="user_email" id="user_email" value="${user_data/user_email}" />
    <label for="user_password_new">Hasło</label>
    <input type="password" name="user_password_new" id="user_password_new" />
    <label for="user_password_repeat">Powtórz hasło</label>
    <input type="password" name="user_password_repeat" id="user_password_repeat" />
    <tal:block tal:condition="mode_new">
    <label for="user_role">Rola</label>
    <select name="user_role" id="user_role">
        <option value="admin" tal:attributes="selected user_data/user_is_admin">admin</option>
        <option value="user" tal:attributes="selected not:user_data/user_is_admin">user</option>
    </select>
    </tal:block>
    <label for="user_status">Status</label>
    <select name="user_status" id="user_status">
        <option value="enabled" tal:attributes="selected user_data/user_enabled">włączony</option>
        <option value="disabled" tal:attributes="selected not:user_data/user_enabled">wyłączony</option>
    </select>
    <hr tal:condition="is_admin" />
    <table class="user_permissions" tal:condition="is_admin">
        <thead>
            <tr>
                <th>Moduł</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <tr tal:repeat="user_permission user_permissions">
                <td>${user_permission/name}</td>
                <td><input type="checkbox" name="user_permissions[${user_permission/name}]" value="1" tal:attributes="checked user_permission/value" /></td>
            </tr>
        </tbody>
    </table>
    <hr />
    <input type="submit" name="sub_save" value="zapisz" />

    <input type="hidden" name="b" value="${b}" tal:condition="b" />
</form>

