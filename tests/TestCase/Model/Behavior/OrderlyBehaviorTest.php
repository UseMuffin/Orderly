<?php
declare(strict_types=1);

namespace Muffin\Orderly\Test\TestCase\Model\Behavior;

use ArrayObject;
use Cake\Database\ValueBinder;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;

class OrderlyBehaviorTest extends TestCase
{
    protected array $fixtures = [
        'core.Posts',
    ];

    protected Table $Table;

    public function setUp(): void
    {
        parent::setUp();

        $this->Table = $this->getTableLocator()->get('Posts');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->getTableLocator()->clear();
        unset($this->Table);
    }

    public function testInitialize()
    {
        $this->Table->addBehavior('Muffin/Orderly.Orderly');

        $expected = [
            [
                'order' => ['Posts.title'],
                'callback' => null,
            ],
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
                'callback' => null,
            ],
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
                'order' => ['Posts.title'],
                'callback' => $callback,
            ],
        ];
        $this->assertEquals(
            $expected,
            $this->Table->behaviors()->Orderly->getConfig()['orders']
        );

        $this->Table->removeBehavior('Orderly');
        $this->Table->addBehavior('Muffin/Orderly.Orderly', [
            [],
            ['order' => 'published', 'callback' => $callback],
        ]);

        $expected = [
            [
                'order' => ['Posts.title'],
                'callback' => null,
            ],
            [
                'order' => 'published',
                'callback' => $callback,
            ],
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
        $behavior->beforeFind($event, $query, new ArrayObject(), true);
        $this->assertEquals(1, count($query->clause('order')));
    }

    public function testBeforeFindQueryWithOrder()
    {
        $this->Table->addBehavior('Muffin/Orderly.Orderly');
        $behavior = $this->Table->behaviors()->Orderly;

        $event = new Event('Model.beforeFind', $this);
        $query = $this->Table->find()
            ->orderBy('author_id');
        $behavior->beforeFind($event, $query, new ArrayObject(), true);
        $this->assertEquals(1, count($query->clause('order')));
    }

    public function testCallback()
    {
        $this->Table->addBehavior('Muffin/Orderly.Orderly', [
            [
                'order' => 'first',
                'callback' => function ($query, $options, $primary) {
                    if ($options['field'] === 'first' || $options['field'] === '_all_') {
                        return true;
                    }

                    return false;
                },
            ],
            [
                'order' => 'second',
                'callback' => function ($query, $options, $primary) {
                    if ($options['field'] === 'second' || $options['field'] === '_all_') {
                        return true;
                    }

                    return false;
                },
            ],
        ]);
        $behavior = $this->Table->behaviors()->Orderly;

        $event = new Event('Model.beforeFind', $this);
        $query = $this->Table->find();

        $behavior->beforeFind($event, $query, new ArrayObject(['field' => null]), true);
        $this->assertNull($query->clause('order'));

        $valueBinder = new ValueBinder();

        $behavior->beforeFind($event, $query, new ArrayObject(['field' => 'first']), true);
        $orderClause = $query->clause('order');
        $this->assertCount(1, $orderClause);
        $this->assertEquals('ORDER BY first', $orderClause->sql($valueBinder));

        $query = $this->Table->find();
        $behavior->beforeFind($event, $query, new ArrayObject(['field' => 'second']), true);
        $orderClause = $query->clause('order');
        $this->assertCount(1, $orderClause);
        $this->assertEquals('ORDER BY second', $orderClause->sql($valueBinder));

        $query = $this->Table->find();
        $behavior->beforeFind($event, $query, new ArrayObject(['field' => '_all_']), true);
        $orderClause = $query->clause('order');
        $this->assertCount(2, $orderClause);
        $this->assertEquals('ORDER BY first, second', $orderClause->sql($valueBinder));
    }
}
