<?php

use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Validation;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\StringLength;

class PhoneBooks extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $first_name;

    /**
     *
     * @var string
     */
    public $last_name;

    /**
     *
     * @var string
     */
    public $phone_number;

    /**
     *
     * @var string
     */
    public $country_code;

    /**
     *
     * @var string
     */
    public $timezone;

    /**
     *
     * @var string
     */
    public $inserted_on;

    /**
     *
     * @var string
     */
    public $updated_on;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("hostway");
        $this->setSource("phone_books");
        $this->addBehavior(
            new Timestampable(
                [
                    'beforeCreate' => [
                        'field' => ['inserted_on', 'updated_on'],
                        'format' => new self()
                    ],
                    'beforeUpdate' => [
                        'field' => 'updated_on',
                        'format' => 'Y-m-d H:i:s'
                    ]
                ]
            )
        );
    }

    /**
     * Validation method for model.
     */
    public function validation()
    {
        $validator = new Validation();
        $validator->add(['first_name', 'phone_number'], new PresenceOf());
        $validator->add(['first_name', 'last_name'], new StringLength([
            'max' => 255
        ]));
        $validator->add(
            'phone_number',
            new Regex(
                [
                    'message' => 'Please provide a valid american phone number ex: +18666484023',
                    'pattern' => '/\+1[0-9]+/',
                ]
            )
        );
        $validator->add('country_code', new InclusionIn([
            'domain' => self::getCountryCodes(),
            'allowEmpty' => true,
            'message' => 'Please provide a valid Country Code'
        ]));
        $validator->add('timezone', new InclusionIn([
            'domain' => self::getTimZones(),
            'allowEmpty' => true,
            'message' => 'Please provide a valid time zone'
        ]));
        return $this->validate($validator);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhoneBooks[]|PhoneBooks|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhoneBooks|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Allows to query the all records
     *
     * @param array $params
     * @return array
     */
    public function getAll($queries)
    {
        $page = isset($queries['page']) ? (int)$queries['page'] : 1;
        $limit = isset($queries['limit']) ? (int)$queries['limit'] : 20;
        $builder = $this->modelsManager->createBuilder()->from(self::class)->orderBy('id ASC');
        $paginator = new QueryBuilder(
            [
                'builder' => $builder,
                'limit' => $limit,
                'page' => $page,
            ]
        );
        $page = $paginator->paginate();
        return [
            'status' => true,
            'data' => $page->items,
            'total' => $page->total_items
        ];
    }

    /**
     * Allows to get country codes from hostway api
     *
     * @return array
     */
    public static function getCountryCodes()
    {
        $response = RequestHelper::get('GET', 'https://api.hostaway.com/countries');
        if (isset($response['result'])) return array_keys($response['result']);
        return [];
    }

    /**
     * Allows to get time zones from hostway api
     *
     * @return array
     */
    public static function getTimZones()
    {
        $response = RequestHelper::get('GET', 'https://api.hostaway.com/timezones');
        if (isset($response['result'])) return array_keys($response['result']);
        return [];
    }
}
