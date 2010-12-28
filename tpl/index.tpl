<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title>${page_title}<span tal:condition="exists:page_subtitle" tal:replace="string: :: ${page_subtitle}"></span></title>
        <style type="text/css" media="all">
            @import url("/static/css/style.css");
        </style>
        <script type="text/javascript" src="/static/js/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="/static/js/expenses.js"></script>
        <script tal:condition="js_file" type="text/javascript" src="/static/js/${js_file}.js"></script>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link tal:condition="logged_in" rel="alternate" type="application/rss+xml" title="Expenses Feed" href="" />
    </head>
    <body>
        <ul class="main_menu" tal:condition="logged_in">
            <li tal:condition="permissions/ItemAdd"><a href="/?module=item_add">Dodaj zakup</a></li>
            <li tal:condition="permissions/Items"><a href="/?module=items">Dokonane zakupy</a></li>
            <li tal:condition="permissions/Categories"><a href="/?module=categories">Lista kategorii</a></li>
            <li tal:condition="permissions/CategoryAdd"><a href="/?module=category_add">Dodaj kategorię</a></li>
            <li tal:condition="permissions/UserEdit"><a href="/?module=user_edit">Profil</a></li>
            <li tal:condition="permissions/Users"><a href="/?module=users">Lista użytkowników</a></li>
            <li tal:condition="permissions/UserAdd"><a href="/?module=user_add">Dodaj użytkownika</a></li>
            <li><a href="/?logout=1">Wyloguj</a></li>
        </ul>
        <div id="main" tal:condition="not: logged_in" tal:attributes="class string:login_page"  tal:content="structure page_content"></div>
        <div id="main" tal:condition="logged_in" tal:content="structure page_content"></div>
    </body>
</html>
