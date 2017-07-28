# carrotユーティリティタスク
#
# @package jp.co.b-shock.carrot3
# @author 小石達也 <tkoishi@b-shock.co.jp>

ROOT_DIR = File.expand_path('..', __FILE__)
$LOAD_PATH.push(File.join(ROOT_DIR, 'lib/ruby'))

require 'fileutils'
require 'carrot/constants'
require 'carrot/environment'
require 'carrot/deployer'
require 'carrot/periodic_creator'
require 'carrot/rsyslog_util'
require File.join(ROOT_DIR, 'webapp/config/Rakefile.local')

desc 'インストール'
task :install => [
  'var:init',
  'environment:init',
  'htdocs:init',
  'periodic:init',
  'rsyslog:init',
  'var:classes:clean',
  'local:init',
]

desc 'アンインストール'
task :uninstall => [
  'htdocs:clean',
  'periodic:clean',
  'rsyslog:clean',
]

desc 'テストを実行'
task :test =>['var:classes:clean'] do
  sh "sudo -u #{Carrot::Constants.new['BS_APP_PROCESS_UID']} bin/carrotctl.php -a Test"
end

namespace :database do
  desc 'データベースを初期化'
  task :init => ['local:database:init']
end

namespace :environment do
  task :init => ['file:init']

  namespace :file do
    desc 'サーバ環境設定ファイルを作成'
    task :init => [
      Carrot::Environment.file_path,
    ]

    file Carrot::Environment.file_path do
      FileUtils.touch(Carrot::Environment.file_path)
    end
  end
end

namespace :rsyslog do
  task :init => [:clean, :create]

  desc 'rsyslog設定をクリア'
  task :clean do
    Carrot::RsyslogUtil.clean
  end

  desc 'rsyslog設定を作成'
  task :create do
    Carrot::RsyslogUtil.create
  end
end

namespace :htdocs do
  task :init => [:clean, :create]

  desc 'htdocsをクリア'
  task :clean do
    Carrot::Deployer.clean
  end

  desc 'htdocsにリンクを作成'
  task :create do
    Carrot::Deployer.create
  end
end

namespace :periodic do
  desc 'periodicを初期化'
  task :init => [:clean, :daily, :hourly, :frequently]

  desc 'periodicをクリア'
  task :clean do
    Carrot::PeriodicCreator.clean
  end

  [:daily, :hourly, :frequently].each do |period|
    desc "periodic #{period}を登録"
    task period do
      periodic = Carrot::PeriodicCreator.new
      periodic[:period] = period
      periodic.create
    end
  end
end

namespace :var do
  desc 'varディレクトリを初期化'
  task :init => [:chmod]

  task :chmod do
    puts "chmod 777 #{File.join(ROOT_DIR, 'var/*')}"
    FileUtils.chmod(0777, Dir.glob(File.join(ROOT_DIR, 'var/*')))
  end

  desc '各種キャッシュをクリア'
  task :clean => [
    'css:clean',
    'js:clean',
    'image:clean',
    'render:clean',
    'classes:clean',
  ]

  namespace :image do
    desc 'イメージキャッシュをクリア'
    task :clean do
      puts "clear #{File.join(ROOT_DIR, 'var/image_cache')}"
      Dir.glob(File.join(ROOT_DIR, 'var/image_cache/*')).each do |f|
        FileUtils.rm_rf(f)
      end
    end
  end

  namespace :css do
    desc 'cssキャッシュをクリア'
    task :clean do
      puts "clear #{File.join(ROOT_DIR, 'var/css_cache')}"
      Dir.glob(File.join(ROOT_DIR, 'var/css_cache/*')).each do |f|
        FileUtils.rm_rf(f)
      end
    end
  end

  namespace :js do
    desc 'jsキャッシュをクリア'
    task :clean do
      puts "clear #{File.join(ROOT_DIR, 'var/js_cache')}"
      Dir.glob(File.join(ROOT_DIR, 'var/js_cache/*')).each do |f|
        FileUtils.rm_rf(f)
      end
    end
  end

  namespace :render do
    desc 'renderキャッシュをクリア'
    task :clean do
      puts "clear #{File.join(ROOT_DIR, 'var/output')}"
      FileUtils.rm(Dir.glob(File.join(ROOT_DIR, 'var/output/*')))
    end
  end

  namespace :classes do
    desc 'クラスヒント情報をクリア'
    task :clean do
      puts "delete #{File.join(ROOT_DIR, 'var/serialized/Loader.*')}"
      FileUtils.rm(Dir.glob(File.join(ROOT_DIR, 'var/serialized/*Loader.*')))
    end
  end
end
