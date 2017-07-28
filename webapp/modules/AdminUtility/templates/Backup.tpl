{*
@package jp.co.b-shock.carrot3
@subpackage AdminUtility
@author 小石達也 <tkoishi@b-shock.co.jp>
*}
{include file='AdminHeader'}

<nav class="bread_crumbs">
  <a href="#">{$action.title}</a>
</nav>

<h1>{$action.title}</h1>
{include file='ErrorMessages'}
{form onsubmit='' class='no_indicator'}
  バックアップファイルをダウンロードします。<br>
  <input type="submit" value="実行">
{/form}

{include file='AdminFooter'}

