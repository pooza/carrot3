{*
ユーザー画面 テンプレートひな形

@package __PACKAGE__
@author 小石達也 <tkoishi@b-shock.co.jp>
*}
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>{strip}
  {if $is_debug}[TEST]{/if}
  {const name='app_name_ja'}
  {$title|default:$module.title}
{/strip}</title>
{if $useragent.is_trident}
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
{/if}
{js_cache name=$jsset}
{css_cache name=$styleset}
</head>
<body {if $body.id}id="{$body.id}"{/if} class="{if $is_debug}debug{/if}">

<header>
  {const name='app_name_ja'} {$title|default:$module.title}
</header>

