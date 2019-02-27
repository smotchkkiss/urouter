<?php

namespace Em4nl\U;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use PHPUnit\Framework\TestCase;


class RouterTest extends TestCase {

    function testHasDefaultProperties() {
        $router = new Router();
        $this->assertInstanceOf(Router::class, $router);
        $this->assertObjectHasAttribute('base_path', $router);
        $this->assertObjectHasAttribute('get_trie', $router);
        $this->assertObjectHasAttribute('post_trie', $router);
        $this->assertObjectHasAttribute('catchall_callback', $router);
        $this->assertIsString($router->base_path);
        $this->assertInstanceOf(Trie::class, $router->get_trie);
        $this->assertInstanceOf(Trie::class, $router->post_trie);
        $this->assertNull($router->catchall_callback);
        $this->assertEquals($router->base_path, '');
    }

    function testSetBasePath() {
        $router = new Router();
        $this->assertEquals($router->base_path, '');
        $router->base('wurm');
        $this->assertEquals($router->base_path, 'wurm');
        $router->base('/lego');
        $this->assertEquals($router->base_path, 'lego');
        $router->base('cheese/');
        $this->assertEquals($router->base_path, 'cheese');
        $router->base('/cheese/');
        $this->assertEquals($router->base_path, 'cheese');
        $router->base('/cheese/balls');
        $this->assertEquals($router->base_path, 'cheese/balls');
        $router->base('/and/my/axe/');
        $this->assertEquals($router->base_path, 'and/my/axe');
    }

    function testInsertGetRouteWithOptionalStatics() {
        $router = new Router();
        $router->get('/cheese?/mouse', function() {});
        $router->get('/wurm/rausch?', function() {});
        $router->get('/gold?/wasser?', function() {});
        $this->assertEquals(6, count($router->get_trie->root->static_nodes));
        $this->assertTrue(
            isset($router->get_trie->root->static_nodes['cheese'])
        );
        $this->assertTrue(
            isset($router->get_trie->root->static_nodes['mouse'])
        );
        $this->assertTrue(
            isset($router->get_trie->root->static_nodes['wurm'])
        );
        $this->assertTrue(
            isset($router->get_trie->root->static_nodes['gold'])
        );
        $this->assertTrue(
            isset($router->get_trie->root->static_nodes['wasser'])
        );
        $this->assertTrue(
            isset($router->get_trie->root->static_nodes[''])
        );
    }

    function testInsertGetRouteWithOptionalParams() {
        $router = new Router();
        $router->get('/:cheese?/mouse', function() {});
        $this->assertEquals(1, count($router->get_trie->root->static_nodes));
        $this->assertTrue(isset($router->get_trie->root->static_nodes['mouse']));
        $this->assertInstanceOf(
            Node::class,
            $router->get_trie->root->param_node
        );
        $this->assertEquals('cheese', $router->get_trie->root->param_name);
    }
}
