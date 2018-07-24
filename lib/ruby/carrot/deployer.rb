require 'carrot/environment'
require 'carrot/constants'
require 'fileutils'

module Carrot
  class Deployer
    def self.clean
      raise 'kariyonをアンインストールしてください。' if kariyon?
      if carrot?
        puts "delete #{dest}"
        File.unlink(dest)
      end
    rescue => e
      puts "#{e.class}: #{e.message}"
      exit 1
    end

    def self.create
      raise 'kariyonをアンインストールしてください。' if kariyon?
      unless carrot?
        puts "link #{ROOT_DIR} -> #{dest}"
        File.symlink(ROOT_DIR, dest)
      end
    rescue => e
      puts "#{e.class}: #{e.message}"
      exit 1
    end

    def self.carrot?(path = nil)
      path ||= dest
      return File.exist?(File.join(path, 'www/carrotctl.php'))
    end

    def self.kariyon?(path = nil)
      path ||= dest
      return File.exist?(File.join(path, '.kariyon'))
    end

    def self.dest
      return File.join(
        Constants.new['BS_APP_DEPLOY_DIR'],
        Environment.name,
      )
    end
  end
end
