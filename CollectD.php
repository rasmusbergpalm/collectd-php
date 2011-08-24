<?php

/**
 * Sends statistics to the collectd daemon over UDP
 * @author Rasmus Berg Palm, rasmus at bergpalm.dk
 * @example CollectD::gauge('rf-django3/foo/bar97', rand(100,200));
 * based on https://github.com/astro/ruby-collectd and https://github.com/etsy/statsd
 *
 * A note about data types: (taken from http://collectd.org/wiki/index.php/Counter)
 * GAUGE
 * A GAUGE value is simply stored as-is. This is the right choice for values which may increase as well as decrease, such as temperatures or the amount of memory used.
 * DERIVE
 * These data sources assume that the change of the value is interesting, i.e. the derivative. Such data sources are very common with events that can be counted, for example the number of emails that have been received by an MTA since it was started. The total number of emails is not interesting, but the change since the value has been read the last time. The value is therefore converted to a rate using the following formula (see: Finite difference (Wikipedia)):
 * Please note that if valuenew < valueold, the resulting rate will be negative. If you set the minimum value to zero, such data points will be discarded. Using DERIVE data sources and a minimum value of zero is recommended for counters that rarely overflow, i.e. wrap-around after their maximum value has been reached. This data source type is available since version 4.8.
 * COUNTER
 * These data sources behave exactly like DERIVE data sources in the “normal” case. Their behavior differs when valuenew < valueold, i.e. when the new value is smaller than the previous value. In this case, COUNTER data sources will assume the counter “wrapped around” and take this into account. The formula for wrap-around cases is:
 * Please note that the rate of a COUNTER data source is never negative. If a counter is reset to zero, for example because an application was restarted, the wrap-around calculation may result in a huge rate. Thus setting a reasonable maximum value is essential when using COUNTER data sources. Because of this, COUNTER data sources are only recommended for counters that wrap-around often, for example 32 bit octet counters of a busy switch port.
 * ABSOLUTE
 * This is probably the most exotic type: It is intended for counters which are reset upon reading. In effect, the type is very similar to GAUGE except that the value is an (unsigned) integer and will be divided by the time since the last reading. This data source type is available since version 4.8 and has been added mainly for consistency with the data source types available in RRDtool.
 * For a description of data source types in RRDtool please refer to the rrdcreate(1) manual page.
**/

class CollectD {

    public static $host = '';
    public static $port = 25826;


    /**
     * Log counter value to stat in CollectD
     * @param string $stat the metric to save. 3 string seperated by 2 forward slashes. e.g. 'host/statname/subname'. Identical statsnames can be plotted together.
     * @param float $value The value to log.
     **/
    public static function counter($stat, $value) {
        CollectD::send($stat, 0, $value);
    }
    /**
     * Log gauge value to stat in CollectD
     * @param string $stat the metric to save. 3 string seperated by 2 forward slashes. e.g. 'host/statname/subname'. Identical statsnames can be plotted together.
     * @param float $value The value to log.
     **/
    public static function gauge($stat, $value) {
        CollectD::send($stat, 1, $value);
    }
    /**
     * Log deriv value to stat in CollectD
     * @param string $stat the metric to save. 3 string seperated by 2 forward slashes. e.g. 'host/statname/subname'. Identical statsnames can be plotted together.
     * @param float $value The value to log.
     **/
    public static function deriv($stat, $value) {
        CollectD::send($stat, 2, $value);
    }
    /**
     * Log absolute value to stat in CollectD
     * @param string $stat the metric to save. 3 string seperated by 2 forward slashes. e.g. 'host/statname/subname'. Identical statsnames can be plotted together.
     * @param float $value The value to log.
     **/
    public static function absolute($stat, $value) {
        CollectD::send($stat, 3, $value);
    }

    /*
     * Squirt the metric over UDP. 
     **/
    public static function send($stat, $t, $value) {
        $types = array(
            'host' => 0,
            'time' => 1,
            'plugin' => 2,
            'plugin_instance' => 3,
            'type' => 4,
            'type_instance' => 5,
            'value' => 6,
        );
        
        $dataTypes = array(
            'counter',
            'gauge',
            'derive',
            'absolute'
        );
        
        $p['type'] = $dataTypes[$t];

        list($p['host'], $p['plugin'], $p['type_instance']) = explode('/', $stat);

        $packet = "";
        $v = pack('NN', 0x00000000, time());
        $packet .= pack('nn', $types['time'], strlen($v) + 4).$v;

        foreach($p as $k => $v){ //Strings
            $v = $v."\000";
            $packet .= pack('nn', $types[$k], strlen($v) + 4).$v;
        }

        if($t===1){ //GAUGE
            $value = pack('d', $value);
        }else{ //COUNTER, DERIVE, ABSOLUTE
            $value = pack('NN', 0x00000000, $value);
        }
        
        $t = pack("C", $t);
        $v = pack("n",1). $t . $value; //TODO: send more than one value at a time
        $packet .= pack('nn', $types['value'], strlen($v) + 4).$v;

        // Wrap this in a try/catch - failures in any of this should be silently ignored
        try {
            $fp = fsockopen("udp://".CollectD::$host, CollectD::$port, $errno, $errstr);
            if (! $fp) { return; }
            fwrite($fp, $packet);
            fclose($fp);
        } catch (Exception $e) {
        }
    }
}