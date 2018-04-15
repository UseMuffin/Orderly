<?php
namespace Muffin\Orderly\Test\TestCase\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class OrderlyBehaviorTest extends TestCase
{

    public $fixtures = [
        'core.Posts',
    ];

    public function setUp()
    {
        parent::setUp();

        $this->Table = TableRegistry::get('Posts');
    }

    public function tearDown()
    {
        parent::tearDown();

        TableRegistry::clear();
        unset($this->Table, $this->Behavior);
    }

    public function testInitialize()
    {
        $this->Table->addBehavior('Muffin/Orderly.Orderly');

        $expected = [
            [
                'order' => $this->Table->aliasField($this->Table->getDisplayField()),
                'callback' => null
            ]
        ];
        $this->assertEquals(
            $expected,
            $this->Table->behaviors()->Orderly->getConfig()['orders']
        );

        $this->Table->removeBehavior('Orderly');
        $this->Table->addBehavior('Muffin/Orderly.Orderly', ['order' => 'published']);

        $expected = [
            [
                'order' => 'published',
                'callback' => null
            ]
        ];
        $this->assertEquals(
            $expected,
            $this->Table->behaviors()->Orderly->getConfig()['orders']
        );

        $callback = function () {
            return true;
        };
        $this->Table->removeBehavior('Orderly');
        $this->Table->addBehavior('Muffin/Orderly.Orderly', ['callback' => $callback]);

        $expected = [
            [
                'order' => 'Posts.title',
                'callback' => $callback
            ]
        ];
        $this->assertEquals(
            $expected,
            $this->Table->behaviors()->Orderly->getConfig()['orders']
        );

        $this->Table->removeBehavior('Orderly');
        $this->Table->addBehavior('Muffin/Orderly.Orderly', [
            [],
            ['order' => 'published', 'callback' => $callback]
        ]);

        $expected = [
            [
                'order' => 'Posts.title',
                'callback' => null
            ],
            [
                'order' => 'published',
                'callback' => $callback
            ]
        ];
        $this->assertEquals(
            $expected,
            $this->Table->behaviors()->Orderly->getConfig()['orders']
        );
    }

    public function testBeforeFind()
    {
        $this->Table->addBehavior('Muffin/Orderly.Orderly');
        $behavior = $this->Table->behaviors()->Orderly;

        $event = new Event('Model.beforeFind', $this);
        $query = $this->Table->query();
        $behavior->beforeFind($event, $query, new \ArrayObject, true);
        $this->assertEquals(1, count($query->clause('order')));
    }

    public function testBeforeFindQueryWithOrder()
    {
        $this->Table->addBehavior('Muffin/Orderly.Orderly');
        $behavior = $this->Table->behaviors()->Orderly;

        $event = new Event('Model.beforeFind', $this);
        $query = $this->Table->find()
            ->order('author_id');
        $behavior->beforeFind($event, $query, new \ArrayObject, true);
        $this->assertEquals(1, count($query->clause('order')));
    }
}
