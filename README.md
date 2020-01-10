# LaravelMetrics

## Introduction

This package allows to record metrics in your laravel application and performs statistics.

It also provides tools for implementing simple alerting mechanisms.

## Installation
In order to install the package run

`composer require robertogallea/laravel-metrics`

Laravel auto-discovery will register package ServiceProviders and Aliases.

## Configuration
If you wish to edit the package configuration run

`php artisan vendor:publish --provider=robertogallea\\LaravelMetrics\\MetricsServiceProvider --tag=config`

## Usage
The package relies on the concept of `Metrics`.
A metric is a measure of some type inside the application. 

Currently: two types of measures are supported:
- `Markers`
- `Timers`

`Markers` are just _on/off_ metrics. They could be used to determine how many times an event occurred in your,
application. 

`Timers` tracks events duration. The could be useful to determine how long events took to complete.

### Save metrics
This is the simples way of using metrics. In any part of your code you can save metrics as follows:
- Markers:
```php
$registry = resolve(MetricRegistry::class);
$marker = $registry->meter('metric-name');

$marker->mark();


// or you can use facade
$marker = \Metrics::meter('metric-name');

$marker->mark();
```

- Timers
```php
$registry = resolve(MetricRegistry::class);
$timer = $registry->meter('metric-name', MeterType::TIMER);

$timerId = $timer->start();

// or you can use facade
$timer = \Metrics::meter('metric-name', MeterType::TIMER);

$timerId = $timer->start();

doSomething();

$timer->stop($timerId);
```

By default, timers are stored using `seconds` resolution. However, desired resolution can be changed before stopping 
the timer:

```php
$timer->inMicrosceconds()->stop($timerId);
$timer->inMilliseconds()->stop($timerId);
$timer->inSeconds()->stop($timerId);
$timer->inMinutes()->stop($timerId);
$timer->inHours()->stop($timerId);
$timer->inDays()->stop($timerId);
$timer->inWeeks()->stop($timerId);
$timer->inMonths()->stop($timerId);
$timer->inYears()->stop($timerId);
```

### Measuring events
You could automatically save marker metrics during event dispatch by doing three steps:
 - Implementing the `PerformsMetrics` interface;
 - Using the `Measurable` trait;
 - Defining the `$meter` field with the name you want to use for your metric.

```php
class TestEvent implements PerformsMetrics
{
    use Dispatchable;
    use Measurable;

    protected $meter = 'test';
}
```

Now, whenever you dispatch an event, the marker is automatically saved:

```php
event(new TestEvent());
``` 

### Measuring requests
You can also instrument your requests by using two middlewares provided:
```php
Route::get('/', 'HomeController@index')->middleware('mark:home-visits');
```
A `Marker` is saved with the `home-visits` name;
 
```php
Route::get('/page/{page}', 'PagesController@show')->middleware('measure-time:page-visits-duration');
```
A `Timer` containing the request duration is saved with the `page-visit-duration` name.
  
```php
Route::get('/details', 'DetailsController@index')->middleware('measure-time:details-duration,milliseconds');
```
Timer could be set to a specific resolution using a second parameter.


## Retrieving metrics

There are two options for retrieving metrics:
- Using `MetricRegistry`:
```php
$registry = resolve(MetricRegistry::class);
$meter = $registry->meter('meter-name');
$meter->get(); // gets the entire dataset for the meter

$from = Carbon::yesterday();
$meter->after($from)->get(); // gets the dataset for meters recorded after $from

$to = Carbon::tomorrow();
$meter->before($from)->get(); // gets the dataset for meters recorded before $to

$meter->between($from, $to)->get(); // gets the dataset for meters recorded between $from and $to
```

- Directly querying the `Metric` Eloquent model.

## Time series

One of the most important aspect of using metrics is extracting time series from them with convenient statistics.

Currently supported statistics are `count`, `average`, `max` and `min`.

```php
$registry = resolve(MetricRegistry::class);

$timer = $registry->meter('meter-name', MeterType::TIMER);

$from = Carbon::now()->subYears(2);
$to = Carbon::today();

$timeSeries = $this->timer->bySecond($from, $to, TimeSeriesStatistics::COUNT);
$timeSeries = $this->timer->byMinute($from, $to, TimeSeriesStatistics::AVERAGE);
$timeSeries = $this->timer->byHour($from, $to, TimeSeriesStatistics::MAX);
$timeSeries = $this->timer->byMonth($from, $to, TimeSeriesStatistics::MIN);
$timeSeries = $this->timer->byYear($from, $to, TimeSeriesStatistics::MIN);
``` 

## Issues, Questions and Pull Requests
You can report issues and ask questions in the [issues section](https://github.com/robertogallea/LaravelMetrics/issues). Please start your issue with ISSUE: and your question with QUESTION:

If you have a question, check the closed issues first. Over time, I've been able to answer quite a few.

To submit a Pull Request, please fork this repository, create a new branch and commit your new/updated code in there. Then open a Pull Request from your new branch. Refer to [this guide](https://help.github.com/articles/about-pull-requests/) for more info.