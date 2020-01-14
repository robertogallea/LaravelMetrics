<?php


namespace robertogallea\LaravelMetrics\Console;

use Illuminate\Console\GeneratorCommand;


class MakeMeasurableEventCommand extends GeneratorCommand
{
    protected $name = 'make:measurable-event';

    protected $description = 'Create a new measurable event';

    protected $type = 'Event';

    protected function getStub()
    {
        return __DIR__.'/../../stubs/measurable_event.php.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Events';
    }

    public function handle()
    {
        parent::handle();

        $this->rewriteMeterName();
    }

    protected function rewriteMeterName()
    {
        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($name);
        $content = file_get_contents($path);

        $meterName = \Str::kebab(str_replace('Event', '', $this->getNameInput()));

        $content = str_replace("protected \$meter = 'DummyMeter';", "protected \$meter = '{$meterName}';", $content);

        file_put_contents($path, $content);
    }
}