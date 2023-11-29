#!/usr/bin/env php
<?php

// Fix for hosts that do not have date.timezone set
date_default_timezone_set('UTC');

// if you don't want to setup permissions the proper way, just uncomment the following PHP line
//umask(0000);

if (function_exists('set_time_limit')) {
    set_time_limit(0);
}

if ($argc > 1) {
    $time_start = microtime(true);

    @parse_str($argv[1], $country_code);
    @parse_str($argv[2], $count);
    @parse_str($argv[3], $lang);

    $country_code = ( array_key_exists('--country', $country_code) ) ? $country_code['--country'] : '';
    $country_code = strtoupper($country_code);
    $count = ( array_key_exists('--count', $count) ) ? $count['--count'] : 10;
    $lang  = ( array_key_exists('--lang', $lang) ) ? $lang['--lang'] : 'en';

    if ( !empty($country_code) ) {
        $request = 'https://sites.bvsalud.org/e-blueinfo/?feed=report&country=' . $country_code . '&format=json&count=1&lang=' . $lang;

        $response = file_get_contents($request);
        if ( $response ) {
            $response_json = json_decode($response, true);
            $total  = $response_json['total'];
            $offset = 0;

            while ( $offset < $total ) {
                $request = 'https://sites.bvsalud.org/e-blueinfo/?feed=report&country=' . $country_code . '&format=json&offset=' . $offset . '&count=' . $count . '&lang=' . $lang;

                $response = file_get_contents($request);
                if ( $response ) {
                    $response_json = json_decode($response, true);
                    $docs  = $response_json['documents'];

                    if ( count($docs) > 1 ) {
                        $headers = array(
                            'TITLE',
                            'COUNTRY',
                            'COMMUNITY',
                            'COLLECTION',
                            'DOWNLOADS'
                        );

                        if ( $offset == 0 ) {
                            $fp = fopen('export-'.$country_code.'.csv', 'w');
                            fputcsv($fp, $headers);
                        }

                        foreach ($docs as $doc) {
                            $fields = array(
                                $doc['title'],
                                $country_code,
                                implode(';', $doc['community']),
                                implode(';', $doc['collection']),
                                $doc['downloads']
                            );

                            fputcsv($fp, $fields);
                        }

                        // fclose($fp);
                    }

                    $offset = $offset + $count;
                }
            }

            $time_end = microtime(true);
            // $time = date("H:i:s",$time_end-$time_start);
            $time = date("H\hi\ms\s",$time_end-$time_start);

            echo "\nFilename: export-$country_code.csv\n";
            echo "Execution time: $time\n\n";
        }
    }
} else {
    echo "\nUsage: php export.php --country=<country_code> [--count=<integer>] [--lang=<language_code>]\n";
    echo "\nExample: php export.php --country=PE --count=100 --lang=es\n\n";
}

?> 