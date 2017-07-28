# periodic
#
# @package jp.co.b-shock.carrot3
# @author 小石達也 <tkoishi@b-shock.co.jp>

require 'carrot/environment'
require 'fileutils'

module Carrot
  class PeriodicCreator < Hash
    def self.clean
      dirs.each do |dir|
        next unless Dir.exist?(dir)
        Dir.glob(File.join(dir, '*')) do |f|
          next unless File.symlink?(f)
          if File.readlink(f).match(ROOT_DIR)
            puts "delete #{f}"
            File.unlink(f)
          end
        end
      end
    end

    def initialize
      self[:basename] = 'carrot'
      self[:period] = 'daily'
      self[:source] = nil
    end

    def create
      unless File.exist?(dest)
        FileUtils.mkdir_p(File.dirname(dest))
        self[:source] ||= default_source
        puts "link #{self[:source]} -> #{dest}"
        File.symlink(self[:source], dest)
      end
    end

    private
    def self.dirs
      dirs = []
      ['daily', 'hourly', 'frequently'].each do |period|
        case Carrot::Environment.platform
          when 'FreeBSD', 'Darwin'
            dirs.push(File.join('/usr/local/etc/periodic', period))
          when 'Debian'
            dirs.push(File.join('/etc', "cron.#{period}"))
        end
      end
      return dirs
    end

    def default_source
      return File.join(ROOT_DIR, "bin/#{self[:basename]}-#{self[:period]}.rb")
    end

    def dest
      return "#{prefix}#{self[:basename]}-#{Carrot::Environment.name}"
    end

    def prefix
      case Carrot::Environment.platform
      when 'FreeBSD', 'Darwin'
        return "/usr/local/etc/periodic/#{self[:period]}/900."
      when 'Debian'
        return "/etc/cron.#{self[:period]}/"
      end
    end
  end
end
