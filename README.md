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
    
License
-------
Copyright (C) 2012 Rasmus Berg Palm

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.