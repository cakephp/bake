<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\View\Cell;

use Bake\Test\App\View\Cell\ArticlesCell;
use Cake\TestSuite\TestCase;

/**
 * Bake\Test\App\View\Cell\ArticlesCell Test Case
 */
class ArticlesCellTest extends TestCase
{
    /**
     * Request mock
     *
     * @var \Cake\Http\ServerRequest|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * Response mock
     *
     * @var \Cake\Http\Response|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $response;

    /**
     * Test subject
     *
     * @var \Bake\Test\App\View\Cell\ArticlesCell
     */
    protected $Articles;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = $this->getMockBuilder('Cake\Http\ServerRequest')->getMock();
        $this->response = $this->getMockBuilder('Cake\Http\Response')->getMock();
        $this->Articles = new ArticlesCell($this->request, $this->response);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Articles);

        parent::tearDown();
    }
}
