# Laravel Canonical Logger

A package to create "canonical" logs lines for requests and queue jobs in Laravel. Inspired by Stripe's approach to
flexible, lightweight observability, [canonical log lines](https://stripe.com/blog/canonical-log-lines) are designed to
be easy to parse, filter, and analyze. Sitting somewhere between unstructured logging and full-blown tracing, canonical
logs are a great way to get a lot of value out of your logs with minimal effort.

