<?php

namespace Em4nl\U;

require_once dirname(__DIR__) . '/router.php';

use PHPUnit\Framework\TestCase;


class TrieTest extends TestCase {

    function testHasDefaultProperties() {
        $trie = new Trie();
        $this->assertInstanceOf(Trie::class, $trie);
        $this->assertObjectHasAttribute('root', $trie);
        $this->assertInstanceOf(Node::class, $trie->root);
    }

    function testInsertEmptyPathYieldsNodeReference() {
        $trie = new Trie();
        $node = &$trie->insert(['']);
        $this->assertInstanceOf(Node::class, $node);
    }

    function testInsertSingleSegmentStaticPathYieldsNodeReference() {
        $trie = new Trie();
        $node = &$trie->insert(['wurm']);
        $this->assertInstanceOf(Node::class, $node);
    }

    function testInsertMultiSegmentStaticPathYieldsNodeReference() {
        $trie = new Trie();
        $node = &$trie->insert(['wurm', '0917']);
        $this->assertInstanceOf(Node::class, $node);
    }

    function testInsertWildcardSingleSegmentPath() {
        $trie = new Trie();
        $node = &$trie->insert(['*']);
        $this->assertInstanceOf(Node::class, $node);
    }

    function testInsertSingleParameterSegmentPath() {
        $trie = new Trie();
        $node = &$trie->insert([':wurm']);
        $this->assertInstanceOf(Node::class, $node);
    }

    function testInsertOptionalSingleSegmentPath() {
        $trie = new Trie();
        $node = &$trie->insert(['wurm?']);
        $this->assertInstanceOf(Node::class, $node);
    }

    function testGetRootNode() {
        $trie = new Trie();
        $node1 = &$trie->insert([]);
        $this->assertEquals($trie->root, $node1);
    }

    function testInsertWildcardNode() {
        $trie = new Trie();
        $node = &$trie->insert(['*']);
        $this->assertInstanceOf(Node::class, $trie->root->wildcard_node);
        $this->assertEquals($trie->root->wildcard_node, $node);
    }

    function testInsertParamNode() {
        $trie = new Trie();
        $node = &$trie->insert([':wurm']);
        $this->assertInstanceOf(Node::class, $trie->root->param_node);
        $this->assertEquals($trie->root->param_name, 'wurm');
        $this->assertEquals($trie->root->param_node, $node);
    }

    function testInsertStaticNode() {
        $trie = new Trie();
        $node = &$trie->insert(['wurm']);
        $this->assertEquals(count($trie->root->static_nodes), 1);
        $this->assertInstanceOf(Node::class, $trie->root->static_nodes['wurm']);
        $this->assertEquals($trie->root->static_nodes['wurm'], $node);
    }

    function testInsertStaticNodes() {
        $trie = new Trie();
        $wurm_node = &$trie->insert(['wurm']);
        $empty_node = &$trie->insert(['']);
        $elephant_node = &$trie->insert(['elephant']);
        $this->assertEquals(count($trie->root->static_nodes), 3);
        $this->assertInstanceOf(Node::class, $trie->root->static_nodes['wurm']);
        $this->assertInstanceOf(Node::class, $trie->root->static_nodes['']);
        $this->assertInstanceOf(
            Node::class,
            $trie->root->static_nodes['elephant']
        );
        $this->assertEquals($trie->root->static_nodes['wurm'], $wurm_node);
        $this->assertEquals($trie->root->static_nodes[''], $empty_node);
        $this->assertEquals(
            $trie->root->static_nodes['elephant'],
            $elephant_node
        );
    }

    function testInsertNestedStaticNode() {
        $trie = new Trie();
        $node = &$trie->insert(['wurm', '007', 'elephant']);
        $this->assertEquals(count($trie->root->static_nodes), 1);
        $this->assertEquals(
            count($trie->root->static_nodes['wurm']->static_nodes),
            1
        );
        $this->assertEquals(
            count(
                $trie->root->static_nodes[
                    'wurm'
                ]->static_nodes['007']->static_nodes
            ),
            1
        );
        $this->assertEquals(
            $trie->root->static_nodes[
                'wurm'
            ]->static_nodes['007']->static_nodes['elephant'],
            $node
        );
    }

    function testInsertNestedParamNode() {
        $trie = new Trie();
        $node = &$trie->insert([':wurm', ':warm', ':harm']);
        $this->assertInstanceOf(Node::class, $trie->root->param_node);
        $this->assertInstanceOf(
            Node::class,
            $trie->root->param_node->param_node
        );
        $this->assertInstanceOf(
            Node::class,
            $trie->root->param_node->param_node->param_node
        );
        $this->assertEquals($trie->root->param_name, 'wurm');
        $this->assertEquals($trie->root->param_node->param_name, 'warm');
        $this->assertEquals(
            $trie->root->param_node->param_node->param_name,
            'harm'
        );
        $this->assertEquals(
            $trie->root->param_node->param_node->param_node,
            $node
        );
    }

    function testInsertDeepWildcardNode() {
        $trie = new Trie();
        $node = &$trie->insert(['worm', 'popsicle', '*']);
        $this->assertEquals(count($trie->root->static_nodes), 1);
        $this->assertInstanceOf(Node::class, $trie->root->static_nodes['worm']);
        $this->assertEquals(
            count($trie->root->static_nodes['worm']->static_nodes),
            1
        );
        $this->assertInstanceOf(
            Node::class,
            $trie->root->static_nodes['worm']->static_nodes['popsicle']
        );
        $this->assertInstanceOf(
            Node::class,
            $trie->root->static_nodes[
                'worm'
            ]->static_nodes['popsicle']->wildcard_node
        );
        $this->assertEquals(
            $trie->root->static_nodes[
                'worm'
            ]->static_nodes['popsicle']->wildcard_node,
            $node
        );
    }

    function testInsertTwoSegmentMixedNode1() {
        $trie = new Trie();
        $node = &$trie->insert([':worm', 'harmony']);
        $this->assertEquals(
            $trie->root->param_node->static_nodes['harmony'],
            $node
        );
    }

    function testInsertTwoSegmentMixedNode2() {
        $trie = new Trie();
        $node = &$trie->insert([':endurance', '*']);
        $this->assertEquals($trie->root->param_node->wildcard_node, $node);
    }

    function testInsertThreeSegmentMixedNode1() {
        $trie = new Trie();
        $node = &$trie->insert(['path', ':finder', ':fox']);
        $this->assertEquals(
            $trie->root->static_nodes['path']->param_node->param_node,
            $node
        );
    }

    function testInsertThreeSegmentMixedNode2() {
        $trie = new Trie();
        $node = &$trie->insert([':peter', 'pan', '*']);
        $this->assertEquals(
            $trie->root->param_node->static_nodes['pan']->wildcard_node,
            $node
        );
    }

    function testInsertThreeSegmentMixedNode3() {
        $trie = new Trie();
        $node = &$trie->insert([':notos', ':caravan', 'hublot']);
        $this->assertEquals(
            $trie->root->param_node->param_node->static_nodes['hublot'],
            $node
        );
    }

    function testSearchRootPath() {
        $trie = new Trie();
        $res = $trie->search([]);
        $this->assertEquals($trie->root, $res);
    }

    function testSearchStaticSingleSegmentPath() {
        $trie = new Trie();
        $node = &$trie->insert(['mock']);
        $res = $trie->search(['mock']);
        $this->assertEquals($node, $res);
    }

    function testSearchStaticMultiSegmentPath() {
        $trie = new Trie();
        $node = &$trie->insert(['mick', 'mock', 'muck']);
        $res1 = $trie->search(['mick', 'mock', 'muck']);
        $this->assertEquals($node, $res1);
        $res2 = $trie->search(['mick']);
        $res3 = $trie->search(['mick', 'mock']);
        $this->assertEquals($trie->root->static_nodes['mick'], $res2);
        $this->assertEquals(
            $trie->root->static_nodes['mick']->static_nodes['mock'],
            $res3
        );
    }

    function testSearchParamPath() {
        $trie = new Trie();
        $node = &$trie->insert([':mock']);
        $res1 = $trie->search(['tick']);
        $res2 = $trie->search(['trick']);
        $res3 = $trie->search(['track']);
        $this->assertEquals($res1, $node);
        $this->assertEquals($res2, $node);
        $this->assertEquals($res3, $node);
    }

    function testSearchNestedParamPath() {
        $trie = new Trie();
        $node = &$trie->insert(['mack', ':muck', 'puck']);
        $res1 = $trie->search(['mack', 'tick', 'puck']);
        $res2 = $trie->search(['mack', 'trick', 'puck']);
        $res3 = $trie->search(['mack', 'track', 'puck']);
        $this->assertEquals($res1, $node);
        $this->assertEquals($res2, $node);
        $this->assertEquals($res3, $node);
        $res4 = $trie->search(['mack', 'siegfried']);
        $res5 = $trie->search(['mack', 'brunhilde']);
        $this->assertEquals(
            $trie->root->static_nodes['mack']->param_node,
            $res4
        );
        $this->assertEquals(
            $trie->root->static_nodes['mack']->param_node,
            $res5
        );
    }

    function testSearchWildcard() {
        $trie = new Trie();
        $node = &$trie->insert(['*']);
        $res1 = $trie->search(['wurm']);
        $res3 = $trie->search(['holm']);
        $res4 = $trie->search(['arielle', 'bibi']);
        $res5 = $trie->search(['tatoes', 'my', 'precious']);
        $this->assertEquals($res1, $node);
        $this->assertEquals($res3, $node);
        $this->assertEquals($res4, $node);
        $this->assertEquals($res5, $node);
    }

    function testSearchWildcardWithStaticPrefix1() {
        $trie = new Trie();
        $node = &$trie->insert(['wurm', '*']);
        $res1 = $trie->search(['wurm', 'katapult']);
        $res2 = $trie->search(['wurm', 'hallo', 'captain']);
        $this->assertEquals($res1, $node);
        $this->assertEquals($res2, $node);
        $res3 = $trie->search(['wurm']);
        $res4 = $trie->search(['warm', 'katapult']);
        $this->assertNotEquals($res3, $node);
        $this->assertNotEquals($res4, $node);
    }

    function testSearchWildcardWithStaticPrefix2() {
        $trie = new Trie();
        $node = &$trie->insert(['tiger', 'lion', '*']);
        $res1 = $trie->search(['tiger', 'lion', 'panther']);
        $res2 = $trie->search(['tiger', 'lion', 'cat']);
        $res3 = $trie->search(['tiger', 'lion', 'kitty', 'cat']);
        $this->assertEquals($res1, $node);
        $this->assertEquals($res2, $node);
        $this->assertEquals($res3, $node);
        $this->assertNotEquals($trie->search(['tiger', 'lion']), $node);
        $this->assertNotEquals($trie->search(['snoop', 'lion']), $node);
        $this->assertNotEquals($trie->search(['tiger']), $node);
    }

    function testSearchWildcardWithParamPrefix1() {
        $trie = new Trie();
        $node = &$trie->insert([':prm', '*']);
        $res1 = $trie->search(['tiger', 'lion']);
        $res2 = $trie->search(['tiger', 'lion', 'cat']);
        $res3 = $trie->search(['kitty', 'lion', 'tiger', 'cat']);
        $this->assertEquals($res1, $node);
        $this->assertEquals($res2, $node);
        $this->assertEquals($res3, $node);
        $this->assertNotEquals($trie->search(['tiger']), $node);
        $this->assertNotEquals($trie->search(['snoop']), $node);
        $this->assertNotEquals($trie->search([]), $node);
    }

    function testSearchWildcardWithParamPrefix2() {
        $trie = new Trie();
        $node = &$trie->insert([':p1', ':p2', '*']);
        $res1 = $trie->search(['tiger', 'lion', 'panther']);
        $res2 = $trie->search(['tiger', 'lion', 'cat']);
        $res3 = $trie->search(['kitty', 'lion', 'tiger', 'cat']);
        $this->assertEquals($res1, $node);
        $this->assertEquals($res2, $node);
        $this->assertEquals($res3, $node);
        $this->assertNotEquals($trie->search(['tiger', 'lion']), $node);
        $this->assertNotEquals($trie->search(['snoop', 'lion']), $node);
        $this->assertNotEquals($trie->search(['tiger']), $node);
    }

    function testSearchWildcardWithMixedPrefix1() {
        $trie = new Trie();
        $node = &$trie->insert(['one', ':two', '*']);
        $res1 = $trie->search(['one', 'three', 'four']);
        $res2 = $trie->search(['one', 'four', 'five']);
        $res3 = $trie->search(['one', 'five', 'seven', 'dwarves']);
        $this->assertEquals($res1, $node);
        $this->assertEquals($res2, $node);
        $this->assertEquals($res3, $node);
        $res4 = $trie->search(['one', 'two']);
        $res5 = $trie->search(['one']);
        $res6 = $trie->search(['two', 'two', 'tree']);
        $this->assertNotEquals($res4, $node);
        $this->assertNotEquals($res5, $node);
        $this->assertNotEquals($res6, $node);
    }

    function testSearchWildcardWithMixedPrefix2() {
        $trie = new Trie();
        $node = &$trie->insert([':one', 'two', '*']);
        $res1 = $trie->search(['one', 'two', 'four']);
        $res2 = $trie->search(['two', 'two', 'five']);
        $res3 = $trie->search(['one', 'two', 'seven', 'dwarves']);
        $this->assertEquals($res1, $node);
        $this->assertEquals($res2, $node);
        $this->assertEquals($res3, $node);
        $res4 = $trie->search(['one', 'two']);
        $res5 = $trie->search(['one']);
        $res6 = $trie->search(['one', 'one', 'tree']);
        $this->assertNotEquals($res4, $node);
        $this->assertNotEquals($res5, $node);
        $this->assertNotEquals($res6, $node);
    }
}
