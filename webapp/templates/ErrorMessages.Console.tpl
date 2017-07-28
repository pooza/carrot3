{*
エラーメッセージ表示 テンプレート

@package jp.co.b-shock.carrot3
@author 小石達也 <tkoishi@b-shock.co.jp>
*}
{if $errors}
  {foreach from=$errors key=code item=message}
    {$code|translate:$error_code_dictionary}:
    {$message}
  {/foreach}

{/if}