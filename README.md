# LaravelMetrics <img src="https://image.flaticon.com/icons/svg/1340/1340105.svg" width="48" />
![Packagist Version](https://img.shields.io/packagist/v/robertogallea/laravel-metrics)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/robertogallea/LaravelMetrics/Run%20tests?label=tests)

## Introduction

This package allows to record metrics in your laravel application and performing statistics.

It also provides tools for implementing simple alerting mechanisms.

## Installation
In order to install the package run

`composer require robertogallea/laravel-metrics`

Laravel auto-discovery will register package ServiceProvider and Aliases.

## Configuration
If you wish to edit the package configuration run

```shell script
php artisan vendor:publish --provider=robertogallea\\LaravelMetrics\\MetricsServiceProvider --tag=config
```

## Usage
The package relies on the concept of `Metrics`.
A metric is a measure of some type inside the application. 

Currently, two types of measures are supported:
- `Markers`
- `Timers`

`Markers` are just their name. They could be used to determine how many times an event occurred in your application. 

`Timers` tracks events duration. They could be useful to determine how long events took to complete.

### Save metrics
This is the simplest way of using metrics. In any part of your code you can save metrics as follows:
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

#### Storing metadata

If needed, you can also store additional metadata in your metrics, by passing a data array to the 
`mark()` or `start()/stop()` methods:

Marker
```php
$data = ['key' => 'value'];
$marker->mark($data);
```

Timer
```php
$data = ['key' => 'value'];
$timerId = $timer->start($data);

doSomething();

$timer->stop($timerId);
```
or
```php
$timerId = $timer->start();

$data = doSomething();

$timer->stop($timerId, $data);
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

#### Creating measurable events
For your convenience a command is registered for creating measurable events:

```shell script
php artisan make:measurable-event MyMeterEvent
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

Time series are generated as custom collection of type `MetricCollection`.

### Statistics on time series

Once a time series is extracted, you can perform statistics on it.
In addition to standard `Collection` methods, like `mean()`, `max()`, `min()`, `avg()`, etc., the `MetricCollection` 
class adds methods for performing other operations. Actually the following are supported:
- `stDev()` - computes values standard deviation
- `variance()` - computes values variance
- `cumulative()` - computes discrete probability density function (i.e. cumulative sum)
- `histogram($nbins)` - computes the histogram for the values using `nbins` bins.
- `kolmSmirn($collection)` - computes [Kolmogorov-Smirnov](https://en.wikipedia.org/wiki/Kolmogorov%E2%80%93Smirnov_test) distance for comparing two time series.

## Treeware

You're free to use this package, but if it makes it to your production environment you are required to buy the world a tree.

It’s now common knowledge that one of the best tools to tackle the climate crisis and keep our temperatures from rising above 1.5C is to <a href="https://www.bbc.co.uk/news/science-environment-48870920">plant trees</a>. If you support this package and contribute to the Treeware forest you’ll be creating employment for local families and restoring wildlife habitats.

You can buy trees here [offset.earth/treeware](https://offset.earth/treeware?gift-trees)

Read more about Treeware at [treeware.earth](http://treeware.earth)

## Issues, Questions and Pull Requests
You can report issues and ask questions in the [issues section](https://github.com/robertogallea/LaravelMetrics/issues). Please start your issue with ISSUE: and your question with QUESTION:

If you have a question, check the closed issues first. Over time, I've been able to answer quite a few.

To submit a Pull Request, please fork this repository, create a new branch and commit your new/updated code in there. Then open a Pull Request from your new branch. Refer to [this guide](https://help.github.com/articles/about-pull-requests/) for more info.
