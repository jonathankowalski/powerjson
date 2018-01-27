<?php

namespace PowerJson;

class PowerJson
{
    protected $config;
    protected $dirList = [];
    protected $variables = [];
    protected $content = '';
    protected $encodedContent = [];
    protected $fileList = [];
    protected $ressources = [];

    const PATTERN_PJSON = '#"json://([^?"]*)\??([^"]*)"#';
    const PREFIX_VAR = '$';
    const JSON_EXT = '.json';

    const LOCAL_CONFIG_FN = 'powerjson.config.json';

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    protected function localconfig() : array
    {
        if (file_exists(self::LOCAL_CONFIG_FN)) {
            $config = json_decode(file_get_contents(self::LOCAL_CONFIG_FN), true);
            if (!$config) {
                throw new \InvalidArgumentException('config is not a valid json file');
            }
            $this->config = $config;
        }
    }

    public function context(string $dir) : PowerJson
    {
        if (!in_array($dir, $this->dirList)) {
            $this->dirList[] = $dir;
        }
        return $this;
    }

    public function contexts(array $dirs) : PowerJson
    {
        foreach ($dirs as $dir) {
            $this->context($dir);
        }
        return $this;
    }

    public function assign(string $name, string $value)
    {
        $this->variables[$name] = $value;
        return $this;
    }

    public function generate(string $entry) : PowerJson
    {
        $this->explore();
        $this->build($entry);
        return $this;
    }

    protected function explore()
    {
        if (!$this->fileList) {
            foreach ($this->dirList as $dir) {
                $it =  new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dir),
                    \RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($it as $name => $file) {
                    if (false !== strpos($name, self::JSON_EXT)) {
                        $this->fileList[str_replace($dir, '', $name)] = $name;
                    }
                }
            }
        }
    }

    protected function build(string $fileName) : PowerJson
    {
        $this->content = file_get_contents($fileName);
        preg_match_all(self::PATTERN_PJSON, $this->content, $matches);
        foreach ($matches[1] as $index => $clue) {
            $data = $matches[2][$index] ?? '';
            $this->createContentAndReplace($clue, $data, $matches[0][$index]);
        }
        $this->replaceVariables();
        return $this;
    }

    protected function createContentAndReplace($clue, $data, $replace)
    {
        $newContent = $this->createContent($clue, $data);
        if (!!$newContent) {
            $this->content = str_replace($replace, $newContent, $this->content);
        }
    }

    protected function createContent(string $filename, string $dataString) : string
    {
        if (isset($this->fileList[$filename])) {
            $pj = new PowerJson($this->config);
            $pj->contexts($this->dirList);
            if (!!$dataString) {
                $this->parseAndReplace($dataString, $pj);
            }
            $pj->generate($this->fileList[$filename]);
            return $pj->output();
        }
        return '';
    }

    protected function parseAndReplace(string $dataString, PowerJson $pj) : void
    {
        parse_str($dataString, $data);
        foreach ($data as $name => $value) {
            $pj->assign($name, $value);
        }
    }

    protected function replaceVariables()
    {
        foreach ($this->variables as $name => $value) {
            $this->content = str_replace(self::PREFIX_VAR . $name, $value, $this->content);
        }
    }

    public function decode() : array
    {
        $this->encodedContent = json_decode($this->content, true);
        if (!$this->encodedContent) {
            throw new \InvalidArgumentException('not valid json');
        }
        return $this->encodedContent;
    }

    public function output(string $filename = null)
    {
        $pretty = ($this->config['pretty'] ?? false) ? JSON_PRETTY_PRINT : 0;
        $json = json_encode($this->decode(), $pretty);
        if (!!$filename) {
            return file_put_contents($filename, $json);
        }
        return $json;
    }
}
