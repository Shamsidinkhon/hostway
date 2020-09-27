<?php

use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
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
                        'format' => 'Y-m-d H:i:s'
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
        $validator->add('country_code', new Callback([
            'callback' => function ($model) {
                return isset(self::getCountryCodes()[$model->country_code]);
            },
            'allowEmpty' => true,
            'message' => 'Please provide a valid Country Code'
        ]));
        $validator->add('timezone', new Callback([
            'callback' => function ($model) {
                return isset(self::getTimZones()[$model->timezone]);
            },
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
        $cache = Phalcon\DI::getDefault()->get("cache");
        $cacheKey = 'country_codes.cache';
        $countryCodes = $cache->get($cacheKey);
        if ($countryCodes == null) {
            $countryCodes = RequestHelper::get('GET', 'https://api.hostaway.com/countries');
            if ($countryCodes) $cache->set($cacheKey, $countryCodes);
        }
        $result = json_decode($countryCodes, true);
        return isset($result['result']) ? $result['result'] : [];
    }

    /**
     * Allows to get time zones from hostway api
     *
     * @return array
     */
    public static function getTimZones()
    {
        $cache = Phalcon\DI::getDefault()->get("cache");
        $cacheKey = 'timezones.cache';
        $timezones = $cache->get($cacheKey);
        if ($timezones == null) {
            $timezones = RequestHelper::get('GET', 'https://api.hostaway.com/timezones');
            if ($timezones) $cache->set($cacheKey, $timezones);
        }
        $result = json_decode($timezones, true);
        return isset($result['result']) ? $result['result'] : [];
    }

    public static function getWhiteList(){
        return [
            'first_name',
            'last_name',
            'phone_number',
            'country_code',
            'timezone',
        ];
    }
}
