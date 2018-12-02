<?php
/**
 * Created by PhpStorm.
 * User: YJC
 * Date: 2018/12/2 002
 * Time: 10:43
 */

namespace Geek;

class State
{
    /**
     * @var int
     */
    private $errno = 0;
    /**
     * @var string
     */
    private $errmsg = '';
    /**
     * @var array
     */
    private $data = null;

    /**
     * @return State
     */
    public static function getInstance() : State {
        return new self();
    }

    /**
     * 设置错误码
     * @param int $errNo
     * @return State
     */
    public function setErrorNo(int $errNo) : State {
        $this->errno = $errNo;
        return $this;
    }

    /**
     * 返回错误码
     * @return int
     */
    public function getErrorNo() : int {
        return $this->errno;
    }

    /**
     * 设置错误消息
     * @param string $errMsg
     * @return State
     */
    public function setErrorMsg(string $errMsg) : State {
        $this->errmsg = $errMsg;
        return $this;
    }

    /**
     * 设置错误消息
     * @return string
     */
    public function getErrorMsg() : string {
        return $this->errmsg;
    }

    /**
     * 设置data
     * @param string $errMsg
     * @return State
     */
    public function setData($data) : Ys_Model_State {
        $this->data = $data;
        return $this;
    }

    /**
     * 返回data
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }
}