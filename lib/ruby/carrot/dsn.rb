# DSN
#
# @package jp.co.b-shock.carrot3
# @author 小石達也 <tkoishi@b-shock.co.jp>

require 'carrot/constants'

module Carrot
  class DSN
    def initialize (name = 'default')
      @name = name
      @dsn = Carrot::Constants.new["BS_PDO_#{name}_DSN"]
      dsn = @dsn.split(':')
      @scheme = dsn.first
      @db = dsn[1].sub('%BS_VAR_DIR%', File.join(ROOT_DIR, 'var'))
    end

    def install
      raise "不正なスキーム: #{@scheme}" unless installable?
      raise "データベース '#{@db}' が既にあります。" if File.exist?(@db)
      raise "スキーマ '#{schema_file}' がありません。" unless schema_file
      puts "import #{schema_file} -> #{@db}"
      system("sqlite3 #{@db} < #{schema_file}")
      File.chmod(0666, @db)
    rescue => e
      puts "#{e.class}: #{e.message}"
      exit 1
    end

    def clean
      if File.exist?(@db)
        puts "delete #{@db}"
        File.unlink(@db)
      end
    rescue => e
      puts "#{e.class}: #{e.message}"
      exit 1
    end

    private
    def installable?
      return @scheme == 'sqlite'
    end

    def schema_file
      path = File.join(ROOT_DIR, "share/sql/#{@name.downcase}.sqlite.sql")
      return path if File.exist?(path)
      return nil
    end
  end
end
