<?php

namespace Em4nl\U;

require_once dirname(__DIR__) . '/vendor/autoload.php';

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

    function testPopulatesParams1() {
        $trie = new Trie();
        $trie->insert(['one', ':two']);
        $params1 = [];
        $trie->search(['one', '222'], $params1);
        $this->assertNotEmpty($params1);
        $this->assertEquals(1, count($params1));
    }

    function testPopulatesParams2() {
        $trie = new Trie();
        $trie->insert(['one', '*']);
        $trie->insert(['two', ':three', '*']);
        $trie->insert([':what', ':else', ':might']);
        $params1 = [];
        $trie->search(['one', 'wurm', 'nofretete'], $params1);
        $this->assertEquals('wurm/nofretete', $params1['*']);
        $params2 = [];
        $trie->search(['one', 'zobel'], $params2);
        $this->assertEquals('zobel', $params2['*']);
        $params3 = [];
        $trie->search(['two', 'zobel', 'humppaaa'], $params3);
        $this->assertEquals('zobel', $params3['three']);
        $this->assertEquals('humppaaa', $params3['*']);
        $params4 = [];
        $trie->search(
            ['two', 'lavazza', 'carazza', 'bifi', 'zerspanen'],
            $params4
        );
        $this->assertEquals('lavazza', $params4['three']);
        $this->assertEquals('carazza/bifi/zerspanen', $params4['*']);
        $params5 = [];
        $trie->search(['hunde', 'amberg', 'not'], $params5);
        $this->assertEquals('hunde', $params5['what']);
        $this->assertEquals('amberg', $params5['else']);
        $this->assertEquals('not', $params5['might']);
    }

    function testRootWildcardNodeWorks() {
        $trie = new Trie();
        $static1 = &$trie->insert(['static1']);
        $static2 = &$trie->insert(['static2']);
        $parametrised = &$trie->insert(['parametrised', ':param1']);
        $wildcard = &$trie->insert(['*']);
        $res1 = $trie->search(['static1']);
        $res2 = $trie->search(['static2']);
        $this->assertEquals($static1, $res1);
        $this->assertEquals($static2, $res2);
        $res3_params = [];
        $res3 = $trie->search(['parametrised', 'ginko'], $res3_params);
        $this->assertEquals($parametrised, $res3);
        $this->assertEquals('ginko', $res3_params['param1']);
        $res4_params = [];
        $res4 = $trie->search(['parametrised', 'nix'], $res4_params);
        $this->assertEquals($parametrised, $res4);
        $this->assertEquals('nix', $res4_params['param1']);
        $res5_params = [];
        $res5 = $trie->search([''], $res5_params);
        $this->assertEquals($wildcard, $res5);
        $this->assertEquals('', $res5_params['*']);
        $res6_params = [];
        $res6 = $trie->search(['witschel'], $res6_params);
        $this->assertEquals($wildcard, $res6);
        $this->assertEquals('witschel', $res6_params['*']);
        $res7_params = [];
        $res7 = $trie->search(['treebeard', 'fangorn'], $res7_params);
        $this->assertEquals($wildcard, $res7);
        $this->assertEquals('treebeard/fangorn', $res7_params['*']);
        $res8_params = [];
        $res8 = $trie->search(['aragorn', 'sonof', 'arathorn'], $res8_params);
        $this->assertEquals($wildcard, $res8);
        $this->assertEquals('aragorn/sonof/arathorn', $res8_params['*']);
    }

    function testParamsOrder() {
        $trie = new Trie();

        $trie->insert(['s1', ':p1']);
        $params1 = [];
        $trie->search(['s1', 'P1'], $params1);
        $params1_keys = array_keys($params1);
        $params1_values = array_values($params1);
        $this->assertEquals('p1', $params1_keys[0]);
        $this->assertEquals('P1', $params1_values[0]);

        $trie->insert(['s2', ':p1', ':p2']);
        $params2 = [];
        $trie->search(['s2', 'P1', 'P2'], $params2);
        $params2_keys = array_keys($params2);
        $params2_values = array_values($params2);
        $this->assertEquals('p1', $params2_keys[0]);
        $this->assertEquals('P1', $params2_values[0]);
        $this->assertEquals('p2', $params2_keys[1]);
        $this->assertEquals('P2', $params2_values[1]);

        $trie->insert(['s3', ':p1', ':p2', ':p3']);
        $params3 = [];
        $trie->search(['s3', 'P1', 'P2', 'P3'], $params3);
        $params3_keys = array_keys($params3);
        $params3_values = array_values($params3);
        $this->assertEquals('p1', $params3_keys[0]);
        $this->assertEquals('P1', $params3_values[0]);
        $this->assertEquals('p2', $params3_keys[1]);
        $this->assertEquals('P2', $params3_values[1]);
        $this->assertEquals('p3', $params3_keys[2]);
        $this->assertEquals('P3', $params3_values[2]);

        $trie->insert(['w1', '*']);
        $params4 = [];
        $trie->search(['w1', 'Q1'], $params4);
        $params4_keys = array_keys($params4);
        $params4_values = array_values($params4);
        $this->assertEquals('*', $params4_keys[0]);
        $this->assertEquals('Q1', $params4_values[0]);
        $params5 = [];
        $trie->search(['w1', 'Q1', 'Q2'], $params5);
        $params5_keys = array_keys($params5);
        $params5_values = array_values($params5);
        $this->assertEquals('*', $params5_keys[0]);
        $this->assertEquals('Q1/Q2', $params5_values[0]);
        $params6 = [];
        $trie->search(['w1', 'Q1', 'Q2', 'Q3'], $params6);
        $params6_keys = array_keys($params6);
        $params6_values = array_values($params6);
        $this->assertEquals('*', $params6_keys[0]);
        $this->assertEquals('Q1/Q2/Q3', $params6_values[0]);

        $trie->insert(['w2', ':p1', '*']);
        $params7 = [];
        $trie->search(['w2', 'Q1', 'Q2'], $params7);
        $params7_keys = array_keys($params7);
        $params7_values = array_values($params7);
        $this->assertEquals('p1', $params7_keys[0]);
        $this->assertEquals('Q1', $params7_values[0]);
        $this->assertEquals('*', $params7_keys[1]);
        $this->assertEquals('Q2', $params7_values[1]);
        $params8 = [];
        $trie->search(['w2', 'Q1', 'Q2', 'Q3'], $params8);
        $params8_keys = array_keys($params8);
        $params8_values = array_values($params8);
        $this->assertEquals('p1', $params8_keys[0]);
        $this->assertEquals('Q1', $params8_values[0]);
        $this->assertEquals('*', $params8_keys[1]);
        $this->assertEquals('Q2/Q3', $params8_values[1]);
        $params9 = [];
        $trie->search(['w2', 'Q1', 'Q2', 'Q3', 'Q4'], $params9);
        $params9_keys = array_keys($params9);
        $params9_values = array_values($params9);
        $this->assertEquals('p1', $params9_keys[0]);
        $this->assertEquals('Q1', $params9_values[0]);
        $this->assertEquals('*', $params9_keys[1]);
        $this->assertEquals('Q2/Q3/Q4', $params9_values[1]);

        $trie->insert(['w3', ':p1', ':p2', '*']);
        $params10 = [];
        $trie->search(['w3', 'Q1', 'Q2', 'Q3'], $params10);
        $params10_keys = array_keys($params10);
        $params10_values = array_values($params10);
        $this->assertEquals('p1', $params10_keys[0]);
        $this->assertEquals('Q1', $params10_values[0]);
        $this->assertEquals('p2', $params10_keys[1]);
        $this->assertEquals('Q2', $params10_values[1]);
        $this->assertEquals('*', $params10_keys[2]);
        $this->assertEquals('Q3', $params10_values[2]);
        $params11 = [];
        $trie->search(['w3', 'Q1', 'Q2', 'Q3', 'Q4'], $params11);
        $params11_keys = array_keys($params11);
        $params11_values = array_values($params11);
        $this->assertEquals('p1', $params11_keys[0]);
        $this->assertEquals('Q1', $params11_values[0]);
        $this->assertEquals('p2', $params11_keys[1]);
        $this->assertEquals('Q2', $params11_values[1]);
        $this->assertEquals('*', $params11_keys[2]);
        $this->assertEquals('Q3/Q4', $params11_values[2]);
        $params12 = [];
        $trie->search(['w3', 'Q1', 'Q2', 'Q3', 'Q4', 'Q5'], $params12);
        $params12_keys = array_keys($params12);
        $params12_values = array_values($params12);
        $this->assertEquals('p1', $params12_keys[0]);
        $this->assertEquals('Q1', $params12_values[0]);
        $this->assertEquals('p2', $params12_keys[1]);
        $this->assertEquals('Q2', $params12_values[1]);
        $this->assertEquals('*', $params12_keys[2]);
        $this->assertEquals('Q3/Q4/Q5', $params12_values[2]);
    }
}
