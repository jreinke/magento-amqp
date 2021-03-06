### Magento extension for sending messages through AMQP protocol (RabbitMQ, ActiveMQ, ...)

#### Requirements

* AMQP PECL extension `sudo pecl install amqp`

#### Installation instructions

Install with [modgit](https://github.com/jreinke/modgit):

    $ cd /path/to/magento
    $ modgit init
    $ modgit clone amqp https://github.com/jreinke/magento-amqp.git

or download package manually [here](https://github.com/jreinke/magento-amqp/archive/master.zip) and unzip in Magento root folder.

Finally:

* Clear cache
* Logout from admin then login again to access module configuration

#### Usage
```php
<?php
// Sending data of a new customer to exchange 'amqp.topic' with routing key 'magento.customer.create'
$data = $customer->getData();
$broker = Mage::helper('amqp/broker');
$broker->send(json_encode($data), 'amq.topic', 'magento.customer.create');
```
