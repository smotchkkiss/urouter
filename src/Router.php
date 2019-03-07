<?php

namespace Em4nl\U;


class Router {

    function __construct() {
        $this->base_path = '';
        $this->get_trie = new Trie();
        $this->post_trie = new Trie();
        $this->catchall_callback = NULL;
    }

    function base($base_path) {
        $this->base_path = trim($base_path, '/');
    }

    function get($path, $callback) {
        $path = explode('/', trim($path, '/'));
        $variations = $this->get_path_variations($path);
        foreach ($variations as $variation) {
            if (!$variation) {
                $variation[] = '';
            }
            $node = &$this->get_trie->insert($variation);
            $node->callback = $callback;
        }
    }

    function post($path, $callback) {
        $path = explode('/', trim($path, '/'));
        $variations = $this->get_path_variations($path);
        foreach ($variations as $variation) {
            if (!$variation) {
                $variation[] = '';
            }
            $node = $this->post_trie->insert($variation);
            $node->callback = $callback;
        }
    }

    function catchall($callback) {
        $this->catchall_callback = $callback;
    }

    function run($mock_request_path=NULL, $mock_request_method='GET') {
        if (is_null($mock_request_path)) {
            $is_mock_run = FALSE;
            $request_path = $this->get_request_path();
        } else {
            $is_mock_run = TRUE;
            $request_path = $mock_request_path;
        }
        $path_parts = explode('?', $request_path, 2);
        $path = explode('/', trim($path_parts[0], '/'));
        $route_trie = $this->get_route_trie($is_mock_run, $mock_request_method);

        $this->execute_matching_route($route_trie, $path);
    }

    function get_route_trie($is_mock_run, $mock_request_method) {
        if (!$is_mock_run) {
            $request_method = $_SERVER['REQUEST_METHOD'];
        } else {
            $request_method = $mock_request_method;
        }
        switch ($request_method) {
        case 'GET':
            return $this->get_trie;
        case 'POST':
            return $this->post_trie;
        default:
            return new Trie();
        }
    }

    function execute_matching_route($trie, $path) {
        $params = array();
        $node = $trie->search($path, $params);

        if ($node && isset($node->callback)) {
            $callback = $node->callback;
            $context = $this->get_context($path, $params);
            $callback($context);
        } elseif ($this->catchall_callback) {
            $callback = $this->catchall_callback;
            $context = $this->get_context($path, $params);
            $callback($context);
        }
    }

    function get_context($path, $params) {
        return array(
            'path' => $this->reconstruct_request_path($path),
            'params' => $params,
        );
    }

    function reconstruct_request_path($path) {
        $path = join('/', $path);
        if (!$path || $path[0] !== '/') {
            return "/$path";
        }
        return $path;
    }

    function get_path_variations($path) {
        $optionals = array_keys(array_filter(
            $path,
            function($segment) {
                return substr($segment, -1) === '?';
            }
        ));
        $permutations = $this->get_permutations($optionals);

        $no_question = array_map(function($segment) {
            if (substr($segment, -1) === '?') {
                return substr($segment, 0, -1);
            } else {
                return $segment;
            }
        }, $path);

        $variations = array($no_question);
        foreach ($permutations as $permutation) {
            $variation = $no_question;
            $length_correction = 0;
            foreach ($permutation as $index) {
                array_splice($variation, $index - $length_correction, 1);
                $length_correction++;
            }
            $variations[] = $variation;
        }
        return $variations;
    }

    function get_permutations($input) {
        $res = array();
        while ($input) {
            $input_element = array_shift($input);
            $solution = array($input_element);
            $res[] = $solution;
            foreach ($input as $rest) {
                $solution = array_merge($solution, array($rest));
                $res[] = $solution;
            }
        }
        return $res;
    }

    function get_request_path() {
        $req_path = trim(urldecode($_SERVER['REQUEST_URI']), '/');
        if ($this->base_path && strpos($req_path, $this->base_path) === 0) {
            $req_path = trim(substr($req_path, strlen($this->base_path)), '/');
        }
        return $req_path;
    }
}
