<?php
namespace Muffin\Orderly\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
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
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->_normalizeConfig($config);
    }

    /**
     * Add default order clause to query as necessary.
     *
     * @param \Cake\Event\Event $event Event
     * @param \Cake\ORM\Query $query Query
     * @param \ArrayObject $options Options
     * @param bool $primary Boolean indicating whether it's primary query.
     * @return void
     */
    public function beforeFind(Event $event, Query $query, ArrayObject $options, $primary)
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
                $query->order($config['order']);
            }
        }
    }

    /**
     * Normalize configuration.
     *
     * @param mixed $orders Orders config
     * @return void
     */
    protected function _normalizeConfig($orders)
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
