<?php

function meta($name = '', $content = '', $type = 'name', $newline = "\n") {
    // Since we allow the data to be passes as a string, a simple array
    // or a multidimensional one, we need to do a little prepping.
    if (!is_array($name)) {
        $name = array(array('name' => $name, 'content' => $content, 'type' => $type, 'newline' => $newline));
    } else {
        // Turn single array into multidimensional
        if (isset($name['name'])) {
            $name = array($name);
        }
    }

    $str = '';
    $valid_meta_types = array('name', 'http-equiv', 'property');
    foreach ($name as $meta) {
        $type = (!isset($meta['type']) || $meta['type'] == 'name') ? 'name' : (!in_array($meta['type'], $valid_meta_types) ? 'http-equiv' : $meta['type']);
        $name = (!isset($meta['name'])) ? '' : $meta['name'];
        $content = (!isset($meta['content'])) ? '' : $meta['content'];
        $newline = (!isset($meta['newline'])) ? "\n" : $meta['newline'];

        $str .= '<meta ' . $type . '="' . $name . '" content="' . $content . '" />' . $newline;
    }

    return $str;
}