#!/usr/local/bin/ruby

# 1時間ごとに実行する処理
#
# @package jp.co.b-shock.carrot3
# @author 小石達也 <tkoishi@b-shock.co.jp>

path = File.expand_path(__FILE__)
while (File.ftype(path) == 'link')
  path = File.expand_path(File.readlink(path))
end
ROOT_DIR = File.expand_path('../..', path)
$LOAD_PATH.push(File.join(ROOT_DIR, 'lib/ruby'))

require 'carrot/batch_action'

Carrot::BatchAction.new('hourly').execute
