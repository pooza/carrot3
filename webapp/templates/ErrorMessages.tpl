{*
エラーメッセージ表示 テンプレート

@package jp.co.dipps.carrot3
@author 小石達也 <tkoishi@b-shock.co.jp>
*}
{if $errors}
  <ul class="error_messages">
    {foreach from=$errors key=code item=message}
      <li>{$code|translate:$error_code_dictionary}:{$message|url2link|nl2br}</li>
    {/foreach}
  </ul>
{/if}
