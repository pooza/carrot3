#!/usr/local/bin/ruby

# 添付ファイルをリネーム
#
# @package jp.co.b-shock.carrot3
# @author 小石達也 <tkoishi@b-shock.co.jp>

ROOT_DIR = File.expand_path('..', __dir__)

Dir.glob(File.join(ROOT_DIR, 'var/*')).each do |dir|
  next unless File.directory?(dir)
  Dir.glob(File.join(dir, '/*')).each do |path|
    matches = File.basename(path).match(/^([[:digit:]]{6,})(_[[:alnum:]]+(\.[[:alnum:]]+)?)/)
    next unless matches
    new_path = File.join(dir, '%010d' % matches[1].to_i + matches[2])
    puts "rename #{path} -> #{new_path}"
    File.rename(path, new_path)
  end
end
