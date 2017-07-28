require 'carrot/environment'
require 'carrot/constants'
require 'fileutils'

module Carrot
  class Deployer
    def self.clean
      begin
        raise 'kariyonをアンインストールしてください。' if kariyon?
        if carrot?
          puts "delete #{dest}"
          File.unlink(dest)
        end
      rescue => e
        puts "#{e.class}: #{e.message}"
        exit 1
      end
    end

    def self.create
      begin
        raise 'kariyonをアンインストールしてください。' if kariyon?
        unless carrot?
          puts "link #{ROOT_DIR} -> #{dest}"
          File.symlink(ROOT_DIR, dest)
        end
      rescue => e
        puts "#{e.class}: #{e.message}"
        exit 1
      end
    end

    def self.carrot? (f = nil)
      f ||= dest
      return File.exist?(File.join(f, 'www/carrotctl.php'))
    end

    def self.kariyon? (f = nil)
      f ||= dest
      return File.exist?(File.join(f, '.kariyon'))
    end

    private
    def self.dest
      return File.join(
        Carrot::Constants.new["BS_APP_DEPLOY_DIR"],
        Carrot::Environment.name
      )
    end
  end
end
