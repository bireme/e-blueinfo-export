<?php

if ( !function_exists( 'intcmp' ) ) {
    /**
     * Integer comparison
     *
     * @param int $a
     * @param int $b
     * @return boolean
     */
    function intcmp($a, $b) {
        return $a["downloads"] - $b["downloads"];
    }
}

if ( !function_exists('print_lang_value') ) {
    function print_lang_value($value, $lang_code='en', $echo=true){
        $lang_code = substr($lang_code,0,2);
        
        if ( is_array($value) ) {
            foreach ($value as $current_value) {
                $print_values[] = get_lang_value($current_value, $lang_code);
            }
            
            $text = implode(', ', $print_values);
        } else {
            $text = get_lang_value($value, $lang_code);
        }

        if ( $echo ) {
            echo $text;
        } else {
            return $text;
        }
    }
}

if ( !function_exists('get_lang_value') ) {
    function get_lang_value($string, $lang_code, $default_lang_code='en'){
        $lang_value = array();
        $occs = preg_split('/\|/', $string);

        foreach ($occs as $occ){
            $re_sep = (strpos($occ, '~') !== false ? '/\~/' : '/\^/');
            $lv = preg_split($re_sep, $occ);
            $lang = substr($lv[0],0,2);
            $value = $lv[1];
            $lang_value[$lang] = $value;
        }

        if ( isset($lang_value[$lang_code]) ) {
            $translated = $lang_value[$lang_code];
        } elseif ( isset($lang_value[$default_lang_code]) ) {
            $translated = $lang_value[$default_lang_code];
        } else {
            $translated = array_values($lang_value)[0];
        }

        return $translated;
    }
}

if ( !function_exists('get_leisref_title') ) {
    function get_leisref_title($doc, $lang) {
        if ( $doc->ti ) {
            $title = $doc->ti[0];
        } else {
            $act_type = print_lang_value($doc->at, $lang, false);
            $title = $act_type.' Nº '.$doc->an[0];
        }

        return $title;
    }
}

if ( !function_exists('remove_prefix') ) {
    function remove_prefix($name){
        $name = explode('|', $name);
        $prefix = array_shift($name);
        $name = implode('|', $name);

        return $name;
    }
}

if ( !function_exists('get_parent_name') ) {
    function get_parent_name($text, $lang='') {
        if ( empty($lang) ) {
            $site_language = strtolower(get_bloginfo('language'));
            $lang = substr($site_language,0,2);
        }

        $name = remove_prefix($text);
        $parent_name = explode('|', $name);
        $lang = ( 'pt' == $lang ) ? 'pt-br' : $lang;

        if ( count($parent_name) == 1 ) {
            $name = explode(' ', $text, 2)[1];
        } else {
            foreach ($parent_name as $pname) {
                $prefix = '('.$lang.')';

                if (substr($pname, 0, strlen($prefix)) === $prefix) {
                    $name = trim(substr($pname, strlen($prefix)));
                    break;
                }
            }
        }

        return $name;
    }
}

if ( !function_exists('array_iunique') ) {
    function array_iunique( $array ) {
        return array_intersect_key(
            $array,
            array_unique( array_map( "strtolower", $array ) )
        );
    }
}

?>
