<?php


class EtuDev_KanbanizePHP_APICall
{

    /**
     * @var string
     */
    public $function;

    /**
     * @var string
     */
    public $format = 'json';

    /**
     * @var array
     */
    public $data = array();

    /**
     * @var array
     */
    public $headers = array();

    /**
     * @var string|null
     */
    public $response;

    /**
     * @var int
     */
    public $response_code;

    /**
     * @var string|null
     */
    public $request_error;

    /**
     * @return $this
     */
    public function resetResponse()
    {
        $this->response      = null;
        $this->response_code = null;
        return $this;
    }

    /**
     * @param array $data
     *
     * @return EtuDev_KanbanizePHP_APICall
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $headers
     *
     * @return EtuDev_KanbanizePHP_APICall
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param null|string $request_error
     *
     * @return EtuDev_KanbanizePHP_APICall
     */
    public function setRequestError($request_error)
    {
        $this->request_error = $request_error;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getRequestError()
    {
        return $this->request_error;
    }

    /**
     * @param null|string $response
     *
     * @return EtuDev_KanbanizePHP_APICall
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param int $response_code
     *
     * @return EtuDev_KanbanizePHP_APICall
     */
    public function setResponseCode($response_code)
    {
        $this->response_code = $response_code;
        return $this;
    }

    /**
     * @return int
     */
    public function getResponseCode()
    {
        return $this->response_code;
    }

    /**
     * @param string $function
     *
     * @return EtuDev_KanbanizePHP_APICall
     */
    public function setFunction($function)
    {
        $this->function = $function;
        return $this;
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @param string $format
     *
     * @return EtuDev_KanbanizePHP_APICall
     */
    protected function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @return EtuDev_KanbanizePHP_APICall
     */
    public function setFormatJSON()
    {
        return $this->setFormat('json');
    }

    /**
     * @return EtuDev_KanbanizePHP_APICall
     */
    public function setFormatXML()
    {
        return $this->setFormat('xml');
    }

    /**
     * @return mixed|null|SimpleXMLElement|string
     */
    public function getResponseDecoded()
    {
        if ($this->format == 'json') {
            return $this->decodeResponseJSON();
        } elseif ($this->format == 'xml') {
            return $this->decodeResponseXML();
        } else {
            return $this->response;
        }
    }

    /**
     * @return mixed
     */
    protected function decodeResponseJSON()
    {
        return json_decode($this->response, true);
    }

    /**
     * @return SimpleXMLElement
     */
    protected function decodeResponseXML()
    {
        try {
            return simplexml_load_string($this->response);
        } catch (Exception $e) {
            return null;
        }
    }
}