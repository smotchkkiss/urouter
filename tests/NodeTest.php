<?php

namespace Em4nl\U;

require_once dirname(__DIR__) . '/router.php';

use PHPUnit\Framework\TestCase;


class NodeTest extends TestCase {

    function testHasDefaultProperties() {
        $node = new Node();
        $this->assertIsObject($node);
        $this->assertInstanceOf(Node::class, $node);
        $this->assertObjectHasAttribute('wildcard_node', $node);
        $this->assertObjectHasAttribute('wildcard_name', $node);
        $this->assertObjectHasAttribute('param_node', $node);
        $this->assertObjectHasAttribute('param_name', $node);
        $this->assertObjectHasAttribute('static_nodes', $node);
        $this->assertNull($node->wildcard_node);
        $this->assertIsString($node->wildcard_name);
        $this->assertNull($node->param_node);
        $this->assertIsString($node->param_name);
        $this->assertIsArray($node->static_nodes);
    }
}
