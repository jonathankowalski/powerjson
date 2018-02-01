<?php

namespace PowerJson;

use Commando\Command;

class Runner
{
    protected $exeDir;
    protected $dirs;
    protected $entry;
    protected $pretty = false;
    protected $outputFn;

    protected $cmd;

    public function __construct(string $exeDir, Command $cmd)
    {
        $this->exeDir = $exeDir;
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
        $pj = new PowerJson(['pretty'=>$this->cmd['p']]);
        if (!!$this->cmd['dir']) {
            $pj->contexts(explode(',', $this->cmd['dir']));
        }
        $pj->generate($this->cmd[0]);
        if (!!$this->cmd['output']) {
            $pj->output($this->cmd['output']);
            return $this->cmd['output'];
        }
        return $pj->output();
    }
}
