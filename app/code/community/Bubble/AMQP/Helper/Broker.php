<?php
/**
 * Depends on AMQP 1.0+
 *
 * @see http://pecl.php.net/package/amqp
 *
 * @category    Bubble
 * @package     Bubble_AMQP
 * @version     1.0.0
 * @copyright   Copyright (c) 2013 BubbleCode (http://shop.bubblecode.net)
 *
 * Usage:
 * <code>
 * $broker = Mage::helper('amqp/broker');
 * // Send high priority message but not mandatory
 * $broker->send('New Magento User!', 'amq.topic', 'magento.user.create', false, true);
 * </code>
 */
class Bubble_AMQP_Helper_Broker extends Mage_Core_Helper_Abstract
{
    /**
     * @var AMQPConnection
     */
    protected $_connection;

    /**
     * @var AMQPChannel
     */
    protected $_channel;

    /**
     * Retrieves connection params from config, then inits connection and channel.
     *
     * @see http://www.php.net/manual/en/class.amqpconnection.php
     * @see http://www.php.net/manual/en/class.amqpchannel.php
     */
    public function __construct()
    {
        if (!extension_loaded('amqp')) {
            Mage::throwException('AMQP extension does not appear to be loaded');
        }
        $config = $this->getConfig();
        $this->_connection = new AMQPConnection($config);
        $this->_connection->connect();
        if (!$this->_connection->isConnected()) {
            Mage::throwException(sprintf(
                "Unable to authenticate to 'amqp://%s:%d' (vhost: %s, user: %s, password: %s)",
                $config['host'],
                $config['port'],
                $config['vhost'],
                $config['user'],
                $config['password']
            ));
        }

        $this->_channel = new AMQPChannel($this->_connection);
    }

    /**
     * Retrieves connection params.
     *
     * @return array
     */
    public function getConfig()
    {
        return Mage::getStoreConfig('bubble_amqp/connection');
    }

    /**
     * Sends a message to the specified exchange.
     * ATM, exchange must already exists on the broker.
     *
     * @param string $msg
     * @param string $exchangeName
     * @param string $routingKey
     * @param boolean $mandatory
     * @param boolean $immediate
     * @param array $attributes
     * @return boolean
     * @see http://www.php.net/manual/en/amqpexchange.publish.php
     */
    public function send($msg, $exchangeName = '', $routingKey = '', $mandatory = false,
                         $immediate = false, array $attributes = array())
    {
        $exchange = new AMQPExchange($this->_channel);
        $exchange->setName($exchangeName);

        $flags = AMQP_NOPARAM;
        if ($mandatory) {
            $flags |= AMQP_MANDATORY;
        }
        if ($immediate) {
            $flags |= AMQP_IMMEDIATE;
        }

        return $exchange->publish((string) $msg, (string) $routingKey, $flags, $attributes);
    }

    /**
     * ATM, queue must already exists on the broker.
     *
     * @param string $name
     * @return AMQPQueue
     * @see http://www.php.net/manual/en/class.amqpqueue.php
     */
    public function getQueue($name)
    {
        $queue = new AMQPQueue($this->_channel);
        $queue->setName($name);

        return $queue;
    }
}