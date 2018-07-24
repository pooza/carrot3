# サーバ環境
#
# @package jp.co.b-shock.carrot3
# @author 小石達也 <tkoishi@b-shock.co.jp>

require 'carrot/constants'

module Carrot
  class Environment
    def self.name
      return File.basename(ROOT_DIR)
    end

    def self.file_path
      return File.join(ROOT_DIR, "webapp/config/constant/#{name}.yaml")
    end

    def self.development?
      return Constants.new['BS_DEBUG']
    end

    def self.platform
      return 'Debian' if File.executable?('/usr/bin/apt-get')
      return `uname`.chomp
    end
  end
end
