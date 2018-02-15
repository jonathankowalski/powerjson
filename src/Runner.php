<?php

namespace PowerJson;

use Commando\Command;

class Runner
{
    const LOCAL_CONFIG_FN = '.powerjson.config.json';

    protected $dirs = [];
    protected $entry;
    protected $pretty = false;
    protected $outputFn;

    /**
     * @var Command
     **/
    protected $cmd;

    public function setCommand(Command $cmd)
    {
        $this->cmd = $cmd;
        $this->configCmd();
    }

    protected function configCmd()
    {
        $this->cmd->option()->require()->file()->describedAs('entry');
        $this->cmd->option('p')->aka('pretty')->describedAs('prettyprint')->boolean();
        $this->cmd->option('d')->aka('dir')->describedAs('directories');
        $this->cmd->option('o')->aka('output')->describedAs('output file');
    }


    public function run()
    {
        if (!!$this->cmd) {
            $this->getConfigFromCmd();
        } else {
            $this->getConfigFromFile();
        }
        return $this->runWithConfig();
    }

    protected function getConfigFromCmd()
    {
        $this->pretty = $this->cmd['p'];
        if (!!$this->cmd['dir']) {
            $this->dirs = explode(',', $this->cmd['dir']);
        }
        $this->entry = $this->cmd[0];
        if (!!$this->cmd['output']) {
            $this->outputFn = $this->cmd['output'];
        }
    }

    protected function getConfigFromFile()
    {
        $config = $this->localconfig();
        $this->pretty = $config['options']['pretty'] ?? false;
        $this->dirs = $config['contexts'] ?? [];
        $this->entry = $config['entry'] ?? false;
        if (!$this->entry) {
            throw new \Exception('You must indicate an entry');
        }
        $this->outputFn = $config['output'] ?? null;
    }

    protected function localconfig() : array
    {
        $filename = getcwd() . DIRECTORY_SEPARATOR . self::LOCAL_CONFIG_FN;
        if (file_exists($filename)) {
            $config = json_decode(file_get_contents($filename), true);
            if (!$config) {
                throw new \InvalidArgumentException('config is not a valid json file');
            }
            return $config;
        }
    }

    protected function runWithConfig()
    {
        $pj = new PowerJson(['pretty'=>$this->pretty]);
        $pj->contexts($this->dirs);
        $pj->generate($this->entry);
        if (!!$this->outputFn) {
            $pj->output($this->outputFn);
            return $this->outputFn;
        }
        return $pj->output();
    }
}
