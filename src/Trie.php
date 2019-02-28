<?php

namespace Em4nl\U;


class Trie {

    function __construct() {
        $this->root = new Node();
    }

    function &insert($path) {
        return $this->_insert($this->root, $path, 0);
    }

    function &_insert(&$trie, $path, $index) {
        if (!isset($path[$index])) {
            return $trie;
        }
        $segment = $path[$index];

        if ($segment && $segment[0] === '*') {
            $node = &$this->_insert_wildcard_node($trie, $segment);
        } elseif ($segment && $segment[0] === ':') {
            $node = &$this->_insert_param_node($trie, $segment);
        } else {
            $node = &$this->_insert_static_node($trie, $segment);
        }
        return $this->_insert($node, $path, $index + 1);
    }

    function &_insert_wildcard_node(&$trie, $segment) {
        if (!$trie->wildcard_node) {
            $name = $segment ? substr($segment, 1) : 'wildcard';
            $trie->wildcard_node = new Node();
            $trie->wildcard_name = $name;
        }
        return $trie->wildcard_node;
    }

    function &_insert_param_node(&$trie, $segment) {
        if (!$trie->param_node) {
            $name = substr($segment, 1);
            $trie->param_node = new Node();
            $trie->param_name = $name;
        }
        return $trie->param_node;
    }

    function &_insert_static_node(&$trie, $segment) {
        if (!isset($trie->static_nodes[$segment])) {
            $trie->static_nodes[$segment] = new Node();
        }
        return $trie->static_nodes[$segment];
    }

    function search($path, &$params=array()) {
        return $this->_search($this->root, $path, 0, $params);
    }

    function _search($trie, $path, $index, &$params) {
        if (!isset($path[$index])) {
            return $trie;
        }

        if (isset($trie->static_nodes[$path[$index]])) {
            return $this->_search_static_node($trie, $path, $index, $params);
        } elseif ($trie->param_node) {
            return $this->_search_param_node($trie, $path, $index, $params);
        } elseif ($trie->wildcard_node) {
            return $this->_search_wildcard_node($trie, $path, $index, $params);
        }
    }

    function _search_static_node($trie, $path, $index, &$params) {
        $node = $trie->static_nodes[$path[$index]];
        return $this->_search($node, $path, $index + 1, $params);
    }

    function _search_param_node($trie, $path, $index, &$params) {
        $params[] = $path[$index];
        $node = $trie->param_node;
        return $this->_search($node, $path, $index + 1, $params);
    }

    function _search_wildcard_node($trie, $path, $index, &$params) {
        $params[] = join('/', array_slice($path, $index));
        return $trie->wildcard_node;
    }
}
