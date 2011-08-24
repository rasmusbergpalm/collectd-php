CollectD PHP
=============
Sends statistics to the collectd daemon over UDP

based on https://github.com/astro/ruby-collectd and https://github.com/etsy/statsd

Example Usage
-------
    CollectD::gauge('rf-django3/foo/bar97', rand(100,200));

Setup
-------
Install collectd

Setup collectd as a server by editing /etc/collectd/collectd.conf

    LoadPlugin "network"
    <Plugin "network">
      Listen "192.168.0.42" "25826"
    </Plugin>

Setup the host and port in CollectD-php

In your php app:

    include_once('CollectD.php');
    CollectD::gauge('rf-django3/foo/bar97', rand(100,200));