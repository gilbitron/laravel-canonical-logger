# Laravel Canonical Logger

A package to create "canonical" logs lines for requests and queue jobs in Laravel. Inspired by Stripe's approach to
flexible, lightweight observability, [canonical log lines](https://stripe.com/blog/canonical-log-lines) are designed to
be easy to parse, filter, and analyze. Sitting somewhere between unstructured logging and full-blown tracing, canonical
logs are a great way to get a lot of value out of your logs with minimal effort.

## Installation

You can install the package via composer:

```bash
composer require gilbitron/laravel-canonical-logger
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Gilbitron\LaravelCanonicalLogger\LaravelCanonicalLoggerServiceProvider" --tag="config"
```

## Usage

By default, the package will log canonical log lines for all requests and queue jobs to your application's default log
channel. You can customize the log channel and log level in the config file.

For example, you should see log lines like this in your log file:

```log
[2024-09-30 19:25:28] local.INFO: canonical-log-line {"type":"request","environment":"local","request_id":"cabee36d-4ab3-48f1-b250-8540a349c4bd","http_method":"GET","http_uri":"/","http_status":200,"http_response_time":0.0089,"session_id":"bYVrl7EMl0nh6N1A3CTHDxgBsWx59TmFzjekwHK2","url":"http://canonical-logger.test"} 
[2024-10-09 20:57:32] local.INFO: canonical-log-line {"type":"job","environment":"local","id":"6ec632c9-75ef-4d18-acb1-485d1abc1d36","status":"processed","name":"App\\Jobs\\ExampleJob","basename":"ExampleJob","connection":"database","queue":"default","attempts":1,"request_id":"0c0c97f2-498d-4314-9c14-55382c925a9c","session_id":"bYVrl7EMl0nh6N1A3CTHDxgBsWx59TmFzjekwHK2"}
[2024-10-09 20:57:36] local.INFO: canonical-log-line {"type":"request","environment":"local","request_id":"8a89dc1d-e1be-401c-9d07-2177d923529a","http_method":"GET","http_uri":"/queue","http_status":404,"http_response_time":0.0117,"url":"http://canonical-logger.test/queue","exception_class":"Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException","exception_message":"The route queue could not be found.","exception_code":0,"exception_file":".../laravel/vendor/laravel/framework/src/Illuminate/Routing/AbstractRouteCollection.php","exception_line":44} 
```

You can then use your log service of choice to filter and analyze these log lines. For example, if you ingest your logs into
AWS Cloudwatch, you can use [Cloudwatch Log Insights](https://docs.aws.amazon.com/AmazonCloudWatch/latest/logs/AnalyzingLogData.html) to query and visualize your logs. Log Insights is smart enough to parse JSON log lines, so you can easily filter and aggregate your logs. [Logstash](https://www.elastic.co/logstash), [DataDog](https://www.datadoghq.com/), and [Splunk](https://www.splunk.com/) are other popular options for log analysis.

Here's an example Cloudwatch Log Insights query to get the average response time for requests:

```sql
fields @timestamp, @message
| filter @message like /canonical-log-line/
| filter type = 'request'
| stats avg(http_response_time) as avg_response_time by bin(1m)
| sort @timestamp desc
```

Another example might be to get the number of processed jobs per hour:

```sql
fields @timestamp, @message
| filter @message like /canonical-log-line/
| filter type = 'job'
| filter status = 'processed'
| stats count() as processed_jobs by bin(1h)
| sort @timestamp desc
```

## Testing

You can run the tests with:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
