<?php
declare(strict_types=1);
namespace Muffin\Orderly\Model\Behavior;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Query;

class OrderlyBehavior extends Behavior
{
    /**
     * Initialize behavior
     *
     * @param array $config Config
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->_normalizeConfig($config);
    }

    /**
     * Add default order clause to query as necessary.
     *
     * @param \Cake\Event\EventInterface $event Event
     * @param \Cake\ORM\Query $query Query
     * @param \ArrayObject $options Options
     * @param bool $primary Boolean indicating whether it's primary query.
     * @return void
     */
    public function beforeFind(EventInterface $event, Query $query, ArrayObject $options, bool $primary)
    {
        $orders = $this->_config['orders'];

        $args = [$query, $options, $primary];

        foreach ($orders as $config) {
            if ((!empty($config['callback'])
                    && call_user_func_array($config['callback'], $args)
                ) || !$query->clause('order')
            ) {
                $query->order($config['order']);
                break;
            }
        }
    }

    /**
     * Normalize configuration.
     *
     * @param array $orders Orders config
     * @return void
     */
    protected function _normalizeConfig(array $orders)
    {
        if (empty($orders)) {
            $orders = [[]];
        } elseif (isset($orders['order']) || isset($orders['callback'])) {
            $orders = [$orders];
        }

        $default = [
            'order' => $this->_table->aliasField($this->_table->getDisplayField()),
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
