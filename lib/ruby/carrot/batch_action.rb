# バッチ処理
#
# @package jp.co.b-shock.carrot3
# @author 小石達也 <tkoishi@b-shock.co.jp>

require 'carrot/constants'
require 'carrot/environment'

module Carrot
  class BatchAction < Array
    def initialize (period)
      @period = period
      tasks.each do |task|
        register(task)
      end
    end

    def execute
      log nil
      log "#{Carrot::Environment.name} #{@period} tasks:"
      self.each do |action|
        log "== module:#{action[:m]} action:#{action[:a]}"
        system(*create_command(action))
      end
    end

    private
    def register (task)
      task = task.split(':')
      push({
        m: task[0],
        a: task[1],
      })
    end

    def create_command (action)
      command = [
        File.join(Carrot::Constants.new['BS_SUDO_DIR'], 'bin/sudo'),
        '-u',
        Carrot::Constants.new['BS_APP_PROCESS_UID'],
        File.join(Carrot::Constants.new['BS_PHP_DIR'], 'bin/php'),
        File.join(ROOT_DIR, 'bin/carrotctl.php')
      ]
      action.each do |key, value|
        command.push("-#{key.to_s}")
        command.push(value)
      end
      return command
    end

    def tasks
      if Carrot::Environment.development?
        key = "BS_PERIODIC_DEVELOPMENT_#{@period}"
      else
        key = "BS_PERIODIC_PRODUCTION_#{@period}"
      end
      return Carrot::Constants.new[key] || []
    end

    def silent?
      return @period == 'frequently'
    end

    def log (message)
      puts message unless silent?
    end
  end
end
