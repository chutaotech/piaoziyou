<?php

namespace ChutaoTech\Piaoziyou;

use ChutaoTech\Piaoziyou\Exceptions\HttpException;
use ChutaoTech\Piaoziyou\Support\Config;
use ChutaoTech\Piaoziyou\Traits\HasHttpRequest;

class Client
{
    use HasHttpRequest;

    const DEV_BASE_URI = 'https://dev.piaoziyou.com';

    const PROD_BASE_URI = 'https://www.piaoziyou.com';

    protected $config;

    protected $baseUri;

    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }

    /**
     * @return string
     */
    protected function getBaseUri()
    {
        return $this->config->get('is_dev') ? self::DEV_BASE_URI : self::PROD_BASE_URI;
    }

    /**
     * @return int
     */
    protected function getTimeout()
    {
        return 0;
    }

    /**
     * generate sign
     *
     * @param array $params
     * @return string
     */
    protected function generateSign($params)
    {
        $authCode = $this->config->get('auth_code');

        return strtoupper(md5(strtolower(json_encode($params)) . $authCode));
    }

    /**
     * @param array $params
     * @return array
     */
    protected function createPayload($params = [])
    {
        $data = [
            'partner_id' => $this->config->get('partner_id'),
            'time' => time(),
            'body' => $params
        ];
        $data['sign'] = $this->generateSign($data);

        return $data;
    }

    /**
     * 获取产品
     * @param int|null $productID
     * @param int $page
     * @param int $limit
     * @return array
     * @throws HttpException
     */
    public function getProducts($productID = null, $page = 1, $limit = 100)
    {
        $params = ['page' => $page, 'limit' => $limit];
        if (!empty($productID)) $params['product_id'] = $productID;

        try {
            $result = $this->postJson(
                '/rest/ota.ticket.open/getProductInfo',
                $this->createPayload($params)
            );

        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }

    /**
     * 获取产品价格日历
     * @param int $productID
     * @return array
     * @throws HttpException
     */
    public function getProductPrice($productID)
    {
        $params = ['product_id' => $productID];

        try {
            $result = $this->postJson(
                '/rest/ota.ticket.open/getPrice',
                $this->createPayload($params)
            );

        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }

}