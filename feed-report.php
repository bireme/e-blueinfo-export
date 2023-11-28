<?php
/**
 * Output report template.
 */

$eblueinfo_data = get_option('eblueinfo_data');

$country_id   = '';
$country_code = ( $_GET['country'] ) ? sanitize_text_field($_GET['country']) : '';
$country_code = strtoupper($country_code);
$collection   = ( $_GET['col'] ) ? sanitize_text_field($_GET['col']) : '';
$format = ( $_GET['format'] && in_array($_GET['format'], array('xml', 'json')) ) ? $_GET['format'] : 'json';
$offset = ( !empty($_GET['offset']) ? $_GET['offset'] : 0 );
$count  = ( !empty($_GET['count']) ? $_GET['count'] : 10 );
$lang   = ( !empty($_GET['lang']) ? $_GET['lang'] : 'en' );
$total  = 0;
$downloads = 0;

$countries_list = array();
if ( !empty($country_code) && 'US' != $country_code ) {
    $country_service_request = EBLUEINFO_SERVICE_URL . 'get_country_list/?format=json';
    $response = @file_get_contents($country_service_request);
    if ($response){
        $response_json = json_decode($response);
        // echo "<pre>"; print_r($response_json); echo "</pre>"; die();
        $countries_list = wp_list_pluck( $response_json, 'id', 'code' );
    }
}

if ( count($countries_list) > 0 && array_key_exists($country_code, $countries_list) ) {
    $country_id = $countries_list[$country_code];
}

$docs_list = array();
if ( !empty($country_id) ) {
    $community_ids = array();
    $eblueinfo_service_request = EBLUEINFO_SERVICE_URL . '?country=' . $country_id . '&format=json&lang=' . $lang;
    $response = @file_get_contents($eblueinfo_service_request);
    if ($response){
        $response_json = json_decode($response);
        // echo "<pre>"; print_r($response_json); echo "</pre>"; die();
        $total = $response_json->meta->total_count;
        $start = $response_json->meta->offset;
        $next  = $response_json->meta->next;
        $community_list = $response_json->objects;
        $community_ids = wp_list_pluck($community_list, 'id');
        $community_id = implode(',', $community_ids);
    }

    $query = '';
    $expr = array();
    if ( count($community_ids) > 0 ) {
        foreach ($community_ids as $com_id) {
            $expr[] = '(com:' . $com_id . '|*)';
        }

        $query = implode(' OR ', $expr);
    }

    if ( !empty($query) ) {
        $pdf_service_request = PDF_SERVICE_URL . '?fl=id,ti,com,col,is&q=' . urlencode($query) . '&start=' . $offset . '&rows=' . $count . '&lang=' . $lang;
        $response = @file_get_contents($pdf_service_request);
        if ($response){
            $response_json = json_decode($response);
            // echo "<pre>"; print_r($response_json); echo "</pre>"; die();
            $total = $response_json->response->numFound;
            $start = $response_json->response->start;
            $docs_list  = $response_json->response->docs;
        }
    }
}

$report = array();
if ( count($docs_list) > 0 ) {
    $docs = array();
    foreach ( $docs_list as $doc ) {
        $_doc = array();
        $_doc['title'] = ( 'leisref' == $doc->is ) ? get_leisref_title($doc, $lang) : $doc->ti[0];
        
        $_doc['community'] = array();
        foreach ($doc->com as $com) {
            $com_name = get_parent_name($com, $lang);
            $_doc['community'][] = $com_name;
        }

        $_doc['collection'] = array();
        foreach ($doc->col as $col) {
            $col_name = get_parent_name($col, $lang);
            $_doc['collection'][] = $col_name;
        }

        $_doc['downloads'] = ( $eblueinfo_data[$doc->id] ) ? $eblueinfo_data[$doc->id] : 0;
        $downloads = $downloads + $_doc['downloads'];

        $docs[] = $_doc;
    }

    // usort($docs, "intcmp");
    // $docs = array_reverse($docs);

    $report['total']     = $total;
    $report['downloads'] = $downloads;
    $report['documents'] = $docs;
}

// output format
if ( 'json' == $format ) {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    print json_encode($report);
} else {
    header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
    echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
?>

<data>
    <total><?php echo $report['total']; ?></total>
    <downloads><?php echo $report['downloads']; ?></downloads>
    <documents>
        <?php foreach ( $report['documents'] as $doc ) : ?>
        <document>
            <field name="title"><![CDATA[<?php echo $doc['title']; ?>]]></field>
            <?php foreach ($doc['community'] as $com) : ?>
            <field name="community"><![CDATA[<?php echo $com; ?>]]></field>
            <?php endforeach; ?>
            <?php foreach ($doc['collection'] as $col) : ?>
            <field name="collection"><![CDATA[<?php echo $col; ?>]]></field>
            <?php endforeach; ?>
            <field name="downloads"><?php echo $doc['downloads']; ?></field>
        </document>
        <?php endforeach; ?>
    </documents>
</data>

<?php } ?>
