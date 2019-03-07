<?php

namespace Em4nl\U;


class Node {

    function __construct() {
        $this->wildcard_node = NULL;
        $this->param_node = NULL;
        $this->param_name = '';
        $this->static_nodes = array();
    }
}
