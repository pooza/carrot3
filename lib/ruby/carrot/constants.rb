# carrot定数
#
# @package jp.co.b-shock.carrot3
# @author 小石達也 <tkoishi@b-shock.co.jp>

require 'yaml'
require 'carrot/environment'

module Carrot
  class Constants
    def initialize
      @constants = {}
      ['carrot', 'application', Environment.name].each do |name|
        path = File.join(ROOT_DIR, 'webapp/config/constant', "#{name}.yaml")
        @constants.update(flatten('BS', YAML.load_file(path), '_'))
      end
    end

    def [] (name)
      [
        "#{name}_#{Environment.platform}",
        "#{name}_DEFAULT",
        name,
      ].each do |name|
        name.upcase!
        return @constants[name] if @constants[name]
      end
      return nil
    end

    private
    def flatten (prefix, node, glue)
      contents = {}
      if node.instance_of?(Hash)
        node.each do |key, value|
          key = prefix + glue + key
          contents.update(flatten(key, value, glue))
        end
      else
        contents[prefix.upcase] = node
      end
      return contents
    end
  end
end
