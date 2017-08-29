<?php
namespace JsonCollectionParser;

class Parser
{
    /**
     * @var array
     */
    protected $options = [
        'line_ending' => "\n",
        'emit_whitespace' => false
    ];

    /**
     * @var \JsonStreamingParser\Parser
     */
    protected $parser;

    /**
     * @var resource
     */
    private $stream;

    /**
     * @var bool
     */
    private $pauseParsing = false;
    

    /**
     * @param string $filePath Source file path
     * @param callback|callable $itemCallback Callback
     * @param bool $assoc Parse as associative arrays
     *
     * @throws \Exception
     */
    public function parse($filePath, $itemCallback, $assoc = true)
    {
        $this->checkCallback($itemCallback);

        $this->stream = $this->openFile($filePath);

        try {
            $listener = new Listener($itemCallback, $assoc);
            $this->parser = new \JsonStreamingParser\Parser(
                $this->stream,
                $listener,
                $this->getOption('line_ending'),
                $this->getOption('emit_whitespace')
            );
            $this->parser->parse();
        } catch (\Exception $e) {
            fclose($this->stream);
            throw $e;
        }
        if (!$this->pauseParsing) {
            fclose($this->stream);
        }
    }

    /**
     *
     */
    public function resume()
    {
        $this->pauseParsing = false;
        try {
            $this->parser->resume();
        } catch (\Exception $e) {
            fclose($this->stream);
            throw $e;
        }
        if (!$this->pauseParsing) {
            fclose($this->stream);
        }
    }

    /**
     *
     */
    public function stop()
    {
        $this->parser->stop();
    }

    /**
     *
     */
    public function pause()
    {
        $this->pauseParsing = true;
        $this->parser->pause();
    }

    /**
     * @param callback|callable $callback
     *
     * @throws \Exception
     */
    protected function checkCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception("Callback should be callable");
        }
    }

    /**
     * @param string $filePath
     *
     * @return resource
     * @throws \Exception
     */
    protected function openFile($filePath)
    {
        if (!is_file($filePath)) {
            throw new \Exception('File does not exist: ' . $filePath);
        }

        $stream = @fopen($filePath, 'r');
        if (false === $stream) {
            throw new \Exception('Unable to open file for read: ' . $filePath);
        }

        return $stream;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getOption($name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        } else {
            return null;
        }
    }
}
