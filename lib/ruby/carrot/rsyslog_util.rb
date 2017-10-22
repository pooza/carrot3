# rsyslogユーティリティ
#
# @package jp.co.b-shock.carrot3
# @author 小石達也 <tkoishi@b-shock.co.jp>

require 'carrot/environment'
require 'carrot/constants'

module Carrot
  class RsyslogUtil
    def self.create
      body = []
      body.push("$template #{template_name}, \"#{log_path}\"")
      body.push("$FileOwner #{Carrot::Constants.new['BS_APP_PROCESS_UID']}")
      body.push(":programname, isequal, \"#{program_name}\" -?#{template_name}")
      body.push('')
      puts "create #{dest}"
      File.write(dest, body.join("\n"))
    rescue => e
      puts "#{e.class}: #{e.message}"
      exit 1
    end

    def self.clean
      if File.exist?(dest)
        puts "delete #{dest}"
        File.unlink(dest)
      end
    rescue => e
      puts "#{e.class}: #{e.message}"
      exit 1
    end

    private
    def self.program_name
      return "carrot-#{Carrot::Environment.name}"
    end

    def self.template_name
      return "FilePath#{Carrot::Environment.name.gsub('.', '').capitalize}"
    end

    def self.log_path
      return File.join(ROOT_DIR, "/var/log/%$now%.log")
    end

    def self.dest
      return "/usr/local/etc/rsyslog.d/#{program_name}.conf"
    end
  end
end
