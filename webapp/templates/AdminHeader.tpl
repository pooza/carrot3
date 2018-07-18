{*
管理画面 テンプレートひな形

@package jp.co.b-shock.carrot3
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
{js name=$jsset}
{css name=$styleset}
</head>
<body {if $body.id}id="{$body.id}"{/if} class="{if $is_debug}debug{/if}">

{if $menu}
<nav id="left_menu" class="main">
  <ul>
    {foreach from=$menu item=item}
      {if $item.separator}
        <li class="separator">&nbsp;</li>
      {elseif $item.href}
        <li><a href="{$item.href}" target="{$item.target|default:'_blank'}">{$item.title}</a></li>
      {else}
        <li><a href="/{$item.module}/{$item.action}">{$item.title}</a></li>
      {/if}
    {/foreach}
  </ul>
</nav>
<script>
document.addEventListener('DOMContentLoaded', function () {
  new Elevator($('left_menu'), {
    x: 10,
    yMin: 30,
    yMargin: 10,
  });
});

document.addEventListener('DOMContentLoaded', function () {
  setInterval(function () {
    window.superagent.get('/Ping')
    .end(function (error, response) {
      if (!response.ok) {
        alert('レスポンスが不正です。(' + error + ')');
      }
    });
  }, 60000);
});
</script>
{/if}

<section>

<header>
  {const name='app_name_ja'} {$title|default:$module.title}
</header>
