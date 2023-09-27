<?php
declare(strict_types=1);

namespace Muffin\Orderly\Model\Behavior;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Query\SelectQuery;

class OrderlyBehavior extends Behavior
{
    /**
     * Initialize behavior
     *
     * @param array<string, mixed> $config Config
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->_normalizeConfig($config);
    }

    /**
     * Add the default order clause to the query as necessary.
     *
     * @param \Cake\Event\EventInterface $event Event
     * @param \Cake\ORM\Query\SelectQuery $query Query
     * @param \ArrayObject $options Options
     * @param bool $primary Boolean indicating whether it's primary query.
     * @return void
     */
    public function beforeFind(EventInterface $event, SelectQuery $query, ArrayObject $options, bool $primary): void
    {
        if ($query->clause('order')) {
            return;
        }

        $orders = $this->_config['orders'];
        foreach ($orders as $config) {
            if (
                empty($config['callback'])
                || call_user_func($config['callback'], $query, $options, $primary)
            ) {
                $query->orderBy($config['order']);
            }
        }
    }

    /**
     * Normalize configuration.
     *
     * @param array<string, mixed> $orders Orders config
     * @return void
     */
    protected function _normalizeConfig(array $orders): void
    {
        if (empty($orders)) {
            $orders = [[]];
        } elseif (isset($orders['order']) || isset($orders['callback'])) {
            $orders = [$orders];
        }

        $default = [
            'order' => array_map(
                $this->_table->aliasField(...),
                (array)$this->_table->getDisplayField()
            ),
            'callback' => null,
        ];

        foreach ($orders as $key => $value) {
            $orders[$key] = $orders[$key] + $default;
        }

        $this->_config = [
            'orders' => $orders,
        ];
    }
}
