{*
@package jp.co.dipps.carrot3
@subpackage AdminUtility
@author 小石達也 <tkoishi@b-shock.co.jp>
*}
{include file='AdminHeader'}

<nav class="bread_crumbs">
  <a href="#">{$action.title}</a>
</nav>

<h1>{$action.title}</h1>
{include file='ErrorMessages'}

{if $is_restoreable}
  <div class="common_block">
    <input id="file_field" type="file">
    <input id="upload_button" type="button" value="実行">
  </div>
{else}
  <div class="alert">
    この環境では、リストアを実行することが出来ません。<br>
    SQLite以外のDBMSが使用されています。
  </div>
{/if}

{include file='AdminFooter'}

